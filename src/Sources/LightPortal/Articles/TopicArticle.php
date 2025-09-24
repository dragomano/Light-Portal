<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Articles;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Parsers\BBCodeParser;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Utils\Avatar;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class TopicArticle extends AbstractArticle
{
	protected array $selectedBoards = [];

	protected int $sorting = 0;

	public function init(): void
	{
		$this->selectedBoards = Setting::get('lp_frontpage_boards', 'array', []);

		$this->sorting = Setting::get('lp_frontpage_article_sorting', 'int', 0);

		$this->params = [
			'current_member'    => User::$me->id,
			'is_approved'       => 1,
			'id_poll'           => 0,
			'id_redirect_topic' => 0,
			'attachment_type'   => 0,
			'selected_boards'   => $this->selectedBoards,
		];

		$this->orders = [
			't.id_last_msg DESC',
			'mf.poster_time DESC',
			'mf.poster_time',
			'date DESC',
		];

		$this->events()->dispatch(
			PortalHook::frontTopics,
			[
				'columns' => &$this->columns,
				'tables'  => &$this->tables,
				'params'  => &$this->params,
				'wheres'  => &$this->wheres,
				'orders'  => &$this->orders,
			]
		);
	}

	public function getData(int $start, int $limit): iterable
	{
		if (empty($this->selectedBoards) && Setting::isFrontpageMode('all_topics')) {
			return;
		}

		$this->params += [
			'start' => $start,
			'limit' => $limit,
		];

		$result = Db::$db->query('
			SELECT
				t.id_topic, t.id_board, t.num_views, t.num_replies, t.is_sticky, t.id_first_msg, t.id_member_started,
				mf.subject, mf.body AS body, mf.smileys_enabled, COALESCE(mem.real_name, mf.poster_name) AS poster_name,
				mf.poster_time, mf.id_member, ml.id_msg, ml.id_member AS last_poster_id, ml.poster_name AS last_poster_name,
				ml.body AS last_body, ml.poster_time AS last_msg_time, GREATEST(mf.poster_time, mf.modified_time) AS date,
				b.name, ' . (empty(Config::$modSettings['lp_show_images_in_articles']) ? '' : '(
					SELECT id_attach
					FROM {db_prefix}attachments
					WHERE id_msg = t.id_first_msg
						AND width <> 0
						AND height <> 0
						AND approved = {int:is_approved}
						AND attachment_type = {int:attachment_type}
					ORDER BY id_attach
					LIMIT 1
				) AS id_attach, ') . (
					User::$me->is_guest
						? '0'
						: 'COALESCE(lt.id_msg, lmr.id_msg, -1) + 1'
				) . ' AS new_from, ml.id_msg_modified' . (empty($this->columns) ? '' : ',
				' . implode(', ', $this->columns)) . '
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS ml ON (t.id_last_msg = ml.id_msg)
				INNER JOIN {db_prefix}messages AS mf ON (t.id_first_msg = mf.id_msg)
				INNER JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)
				LEFT JOIN {db_prefix}members AS mem ON (mf.id_member = mem.id_member)' . (
					User::$me->is_guest ? '' : '
				LEFT JOIN {db_prefix}log_topics AS lt ON (
					t.id_topic = lt.id_topic AND lt.id_member = {int:current_member}
				)
				LEFT JOIN {db_prefix}log_mark_read AS lmr ON (
					t.id_board = lmr.id_board AND lmr.id_member = {int:current_member}
				)') . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE t.id_poll = {int:id_poll}
				AND t.approved = {int:is_approved}
				AND t.id_redirect_topic = {int:id_redirect_topic}' . (empty($this->selectedBoards) ? '' : '
				AND t.id_board IN ({array_int:selected_boards})') . '
				AND {query_wanna_see_board}' . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)) . '
			ORDER BY ' . (empty(Config::$modSettings['lp_frontpage_order_by_replies']) ? '' : 't.num_replies DESC, ')
				. $this->orders[$this->sorting] . '
			LIMIT {int:start}, {int:limit}',
			$this->params,
		);

		while ($row = Db::$db->fetch_assoc($result)) {
			$topic = [
				'id'        => (int) $row['id_topic'],
				'section'   => $this->getSectionData($row),
				'author'    => $this->getAuthorData($row),
				'date'      => $this->getDate($row),
				'title'     => $this->getTitle($row),
				'link'      => $this->getLink($row),
				'is_new'    => $this->isNew($row),
				'views'     => $this->getViewsData($row),
				'replies'   => $this->getRepliesData($row),
				'css_class' => $row['is_sticky'] ? ' sticky' : '',
				'image'     => $this->getImage($row),
				'can_edit'  => $this->canEdit($row),
				'edit_link' => $this->getEditLink($row),
			];

			$this->prepareTeaser($topic, $row);

			$articles = [$row['id_topic'] => $topic];

			$this->events()->dispatch(PortalHook::frontTopicsRow, ['articles' => &$articles, 'row' => $row]);

			$topic = $articles[$row['id_topic']];

			yield $row['id_topic'] => Avatar::getWithItems([$topic])[0] ?? [];
		}

		Db::$db->free_result($result);
	}

	public function getTotalCount(): int
	{
		if (empty($this->selectedBoards) && Setting::isFrontpageMode('all_topics'))
			return 0;

		$result = Db::$db->query(/** @lang text */ '
			SELECT COUNT(t.id_topic)
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)' . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE t.approved = {int:is_approved}
				AND t.id_poll = {int:id_poll}
				AND t.id_redirect_topic = {int:id_redirect_topic}' . (empty($this->selectedBoards) ? '' : '
				AND t.id_board IN ({array_int:selected_boards})') . '
				AND {query_wanna_see_board}' . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)),
			$this->params,
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	private function getSectionData(array $row): array
	{
		return [
			'name' => $row['name'],
			'link' => Config::$scripturl . '?board=' . $row['id_board'] . '.0',
		];
	}

	private function getAuthorData(array $row): array
	{
		return [
			'id'   => $authorId = (int) ($this->sorting === 0 ? $row['last_poster_id'] : $row['id_member']),
			'link' => Config::$scripturl . '?action=profile;u=' . $authorId,
			'name' => $this->sorting === 0 ? $row['last_poster_name'] : $row['poster_name'],
		];
	}

	private function getDate(array $row): int
	{
		if ($this->sorting === 0 && $row['last_msg_time']) {
			return (int) $row['last_msg_time'];
		}

		if ($this->sorting === 3) {
			return (int) $row['date'];
		}

		return (int) $row['poster_time'];
	}

	private function getTitle(array $row): string
	{
		Str::cleanBbcode($row['subject']);

		Lang::censorText($row['subject']);

		return $row['subject'];
	}

	private function getLink(array $row): string
	{
		return Config::$scripturl . '?topic=' . $row['id_topic'] . '.0';
	}

	private function isNew(array $row): bool
	{
		if (empty($row['new_from']))
			return false;

		return $row['new_from'] <= $row['id_msg_modified'] && (int) $row['last_poster_id'] !== User::$me->id;
	}

	private function getViewsData(array $row): array
	{
		return [
			'num'   => (int) $row['num_views'],
			'title' => Lang::$txt['lp_views'],
			'after' => '',
		];
	}

	private function getRepliesData(array $row): array
	{
		return [
			'num'   => (int) $row['num_replies'],
			'title' => Lang::$txt['lp_replies'],
			'after' => '',
		];
	}

	private function getImage(array $row): string
	{
		$image = empty(Config::$modSettings['lp_show_images_in_articles'])
			? '' : Str::getImageFromText(BBCodeParser::load()->parse($row['body'], false));

		if (! empty($row['id_attach']) && empty($image)) {
			$image = $this->getLink($row) . ';attach=' . $row['id_attach'] . ';image';
		}

		return $image;
	}

	private function canEdit(array $row): bool
	{
		return User::$me->is_admin || (User::$me->id && (int) $row['id_member'] === User::$me->id);
	}

	private function getEditLink(array $row): string
	{
		return Config::$scripturl . '?action=post;msg=' . $row['id_first_msg'] . ';topic=' . $row['id_topic'] . '.0';
	}

	private function prepareTeaser(array &$topic, array $row): void
	{
		if (empty(Config::$modSettings['lp_show_teaser']))
			return;

		$body = (string) ($this->sorting === 0 ? $row['last_body'] : $row['body']);

		Lang::censorText($body);

		$body = preg_replace('~\[spoiler.*].*?\[/spoiler]~Usi', '', $body);
		$body = preg_replace('~\[quote.*].*?\[/quote]~Usi', '', $body);
		$body = preg_replace('~\[table.*].*?\[/table]~Usi', '', $body);
		$body = preg_replace('~\[code.*].*?\[/code]~Usi', '', $body);
		$body = BBCodeParser::load()->parse($body, (bool) $row['smileys_enabled'], (int) $row['id_first_msg']);

		$topic['teaser'] = Str::getTeaser($body);
	}
}
