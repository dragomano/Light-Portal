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

namespace LightPortal\Articles;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Parsers\BBCodeParser;
use Bugo\Compat\User;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use LightPortal\Enums\PortalHook;
use LightPortal\Utils\Avatar;
use LightPortal\Utils\ForumPermissions;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class TopicArticle extends AbstractArticle
{
	protected array $selectedBoards = [];

	public function init(): void
	{
		$this->selectedBoards = Setting::get('lp_frontpage_boards', 'array', []);

		$this->params = [
			'current_member'    => User::$me->id,
			'is_approved'       => 1,
			'id_poll'           => 0,
			'id_redirect_topic' => 0,
			'attachment_type'   => 0,
			'selected_boards'   => $this->selectedBoards,
		];

		$this->orders = [
			'created;desc'      => 'mf.poster_time DESC',
			'created'           => 'mf.poster_time',
			'updated;desc'      => 'GREATEST(mf.poster_time, mf.modified_time) DESC',
			'updated'           => 'GREATEST(mf.poster_time, mf.modified_time)',
			'last_comment;desc' => 't.id_last_msg DESC',
			'last_comment'      => 't.id_last_msg',
			'title;desc'        => 'mf.subject DESC',
			'title'             => 'mf.subject',
			'author_name;desc'  => 'poster_name DESC',
			'author_name'       => 'poster_name',
			'num_views;desc'    => 't.num_views DESC',
			'num_views'         => 't.num_views',
			'num_replies;desc'  => 't.num_replies DESC',
			'num_replies'       => 't.num_replies',
		];

		$this->events()->dispatch(
			PortalHook::frontTopics,
			[
				'columns' => &$this->columns,
				'joins'   => &$this->joins,
				'params'  => &$this->params,
				'wheres'  => &$this->wheres,
				'orders'  => &$this->orders,
			]
		);
	}

	public function getSortingOptions(): array
	{
		return [
			'created;desc'      => Lang::$txt['lp_sort_by_created_desc'],
			'created'           => Lang::$txt['lp_sort_by_created'],
			'updated;desc'      => Lang::$txt['lp_sort_by_updated_desc'],
			'updated'           => Lang::$txt['lp_sort_by_updated'],
			'last_comment;desc' => Lang::$txt['lp_sort_by_last_reply_desc'],
			'last_comment'      => Lang::$txt['lp_sort_by_last_reply'],
			'title;desc'        => Lang::$txt['lp_sort_by_title_desc'],
			'title'             => Lang::$txt['lp_sort_by_title'],
			'author_name;desc'  => Lang::$txt['lp_sort_by_author_desc'],
			'author_name'       => Lang::$txt['lp_sort_by_author'],
			'num_views;desc'    => Lang::$txt['lp_sort_by_num_views_desc'],
			'num_views'         => Lang::$txt['lp_sort_by_num_views'],
			'num_replies;desc'  => Lang::$txt['lp_sort_by_num_replies_desc'],
			'num_replies'       => Lang::$txt['lp_sort_by_num_replies'],
		];
	}

	public function getData(int $start, int $limit, string $sortType = null): iterable
	{
		$this->setSorting($sortType);

		if (empty($this->selectedBoards))
			return;

		$this->prepareParams($start, $limit);

		$select = $this->sql->select()
			->from(['t' => 'topics'])
			->join(
				['ml' => 'messages'],
				't.id_last_msg = ml.id_msg',
				[
					'id_msg', 'last_poster_id' => 'id_member', 'last_poster_name' => 'poster_name',
					'last_body' => 'body', 'last_msg_time' => 'poster_time', 'id_msg_modified'
				]
			)
			->join(
				['mf' => 'messages'],
				't.id_first_msg = mf.id_msg',
				[
					'subject', 'body', 'smileys_enabled', 'poster_time', 'id_member',
					'date' => new Expression('GREATEST(mf.poster_time, mf.modified_time)')
				]
			)
			->join(
				['b' => 'boards'],
				't.id_board = b.id_board',
				['name']
			)
			->join(
				['mem' => 'members'],
				'mf.id_member = mem.id_member',
				['poster_name' => new Expression('COALESCE(mem.real_name, mf.poster_name)')],
				Select::JOIN_LEFT
			);

		$columns = [
			'id_topic', 'id_board', 'num_views', 'num_replies', 'is_sticky', 'id_first_msg', 'id_member_started',
		];

		if (! User::$me->is_guest) {
			$select->join(
				['lt' => 'log_topics'],
				new Expression(
					't.id_topic = lt.id_topic AND lt.id_member = ?',
					[$this->params['current_member']]
				),
				[],
				Select::JOIN_LEFT
			)
				->join(
					['lmr' => 'log_mark_read'],
					new Expression(
						't.id_board = lmr.id_board AND lmr.id_member = ?',
						[$this->params['current_member']]
					),
					[],
					Select::JOIN_LEFT
				);
			$columns['new_from'] = new Expression('COALESCE(lt.id_msg, lmr.id_msg, -1) + 1');
		} else {
			$columns['new_from'] = new Expression('0');
		}

		if (! empty(Config::$modSettings['lp_show_images_in_articles'])) {
			$select->join(
				['a' => 'attachments'],
				new Expression(
					'a.id_msg = t.id_first_msg AND a.width <> 0 AND a.height <> 0 AND a.approved = ? AND a.attachment_type = ?',
					[$this->params['is_approved'], $this->params['attachment_type']]
				),
				[],
				Select::JOIN_LEFT
			);
			$columns['id_attach'] = new Expression('MIN(a.id_attach)');
			$select->group('t.id_topic');
		}

		$select->columns($columns);

		$this->applyColumns($select);
		$this->applyJoins($select);
		$this->applyWheres($select);

		$select
			->order($this->params['sort'])
			->limit($this->params['limit'])
			->offset($this->params['start']);

		$result = $this->sql->execute($select);

		foreach ($result as $row) {
			$topic = [
				'id'           => $row['id_topic'],
				'section'      => $this->getSectionData($row),
				'author'       => $this->getAuthorData($row),
				'date'         => $this->getDate($row),
				'last_comment' => $row['last_msg_time'],
				'title'        => $this->getTitle($row),
				'link'         => $this->getLink($row),
				'is_new'       => $this->isNew($row),
				'views'        => $this->getViewsData($row),
				'replies'      => $this->getRepliesData($row),
				'css_class'    => $row['is_sticky'] ? ' sticky' : '',
				'image'        => $this->getImage($row),
				'can_edit'     => $this->canEdit($row),
				'edit_link'    => $this->getEditLink($row),
			];

			$this->prepareTeaser($topic, $row);

			$articles = [$row['id_topic'] => $topic];

			$this->events()->dispatch(PortalHook::frontTopicsRow, ['articles' => &$articles, 'row' => $row]);

			$topic = $articles[$row['id_topic']];

			yield $row['id_topic'] => Avatar::getWithItems([$topic])[0] ?? [];
		}
	}

	public function getTotalCount(): int
	{
		if (empty($this->selectedBoards))
			return 0;

		$select = $this->sql->select()
			->from(['t' => 'topics'])
			->columns(['count' => new Expression('COUNT(t.id_topic)')])
			->join(
				['b' => 'boards'],
				't.id_board = b.id_board',
				[]
			);

		$this->applyJoins($select);
		$this->applyWheres($select);

		$result = $this->sql->execute($select)->current();

		return ($result['count'] ?? 0);
	}

	protected function applyBaseConditions(Select $select): void
	{
		$select->where([
			't.id_poll'           => $this->params['id_poll'],
			't.approved'          => $this->params['is_approved'],
			't.id_redirect_topic' => $this->params['id_redirect_topic'],
		]);

		if (! empty($this->selectedBoards)) {
			$select->where(['t.id_board' => $this->selectedBoards]);
		}

		if (ForumPermissions::shouldApplyBoardPermissionCheck()) {
			$select->where(ForumPermissions::canSeeBoard());
		}
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
		$authorId   = (str_contains($this->sorting, 'last_comment') ? $row['last_poster_id'] : $row['id_member']);
		$authorName = str_contains($this->sorting, 'last_comment') ? $row['last_poster_name'] : $row['poster_name'];

		return [
			'id'   => $authorId,
			'link' => Config::$scripturl . '?action=profile;u=' . $authorId,
			'name' => $authorName,
		];
	}

	private function getDate(array $row): int
	{
		if (str_contains($this->sorting, 'last_comment') && $row['last_msg_time']) {
			return $row['last_msg_time'];
		}

		if (str_contains($this->sorting, 'updated')) {
			return $row['date'];
		}

		return $row['poster_time'];
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
		if (User::$me->is_guest || empty($row['new_from']))
			return false;

		return $row['new_from'] <= $row['id_msg_modified'] && $row['last_poster_id'] !== User::$me->id;
	}

	private function getViewsData(array $row): array
	{
		return [
			'num'   => $row['num_views'],
			'title' => Lang::$txt['lp_views'],
			'after' => '',
		];
	}

	private function getRepliesData(array $row): array
	{
		return [
			'num'   => $row['num_replies'],
			'title' => Lang::$txt['lp_replies'],
			'after' => '',
		];
	}

	private function getImage(array $row): string
	{
		if (empty(Config::$modSettings['lp_show_images_in_articles'])) {
			return '';
		}

		$image = Str::getImageFromText(BBCodeParser::load()->parse($row['body'], false));

		if (! empty($row['id_attach']) && empty($image)) {
			return $this->getLink($row) . ';attach=' . $row['id_attach'] . ';image';
		}

		return $image;
	}

	private function canEdit(array $row): bool
	{
		return User::$me->is_admin || (User::$me->id && $row['id_member'] === User::$me->id);
	}

	private function getEditLink(array $row): string
	{
		return Config::$scripturl . '?action=post;msg=' . $row['id_first_msg'] . ';topic=' . $row['id_topic'] . '.0';
	}

	private function prepareTeaser(array &$topic, array $row): void
	{
		if (empty(Config::$modSettings['lp_show_teaser']))
			return;

		$body = (string) (str_contains($this->sorting, 'last_comment') ? $row['last_body'] : $row['body']);

		Lang::censorText($body);

		$body = preg_replace('~\[spoiler.*].*?\[/spoiler]~Usi', '', $body);
		$body = preg_replace('~\[quote.*].*?\[/quote]~Usi', '', $body);
		$body = preg_replace('~\[table.*].*?\[/table]~Usi', '', $body);
		$body = preg_replace('~\[code.*].*?\[/code]~Usi', '', $body);
		$body = BBCodeParser::load()->parse($body, (bool) $row['smileys_enabled'], (int) $row['id_first_msg']);

		$topic['teaser'] = Str::getTeaser($body);
	}
}
