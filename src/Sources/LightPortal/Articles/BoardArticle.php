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
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class BoardArticle extends AbstractArticle
{
	private array $selectedBoards = [];

	protected int $sorting = 0;

	public function init(): void
	{
		$this->selectedBoards = Setting::get('lp_frontpage_boards', 'array', []);

		$this->sorting = Setting::get('lp_frontpage_article_sorting', 'int', 0);

		$this->params = [
			'blank_string'    => '',
			'current_member'  => User::$me->id,
			'selected_boards' => $this->selectedBoards,
		];

		$this->orders = [
			'b.id_last_msg DESC',
			'm.poster_time DESC',
			'm.poster_time',
			'last_updated DESC',
		];

		$this->events()->dispatch(
			PortalHook::frontBoards,
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
		if (empty($this->selectedBoards))
			return;

		$this->params += [
			'start' => $start,
			'limit' => $limit,
		];

		$result = Db::$db->query('
			SELECT
				b.id_board, b.name, b.description, b.redirect,
				CASE WHEN b.redirect != {string:blank_string} THEN 1 ELSE 0 END AS is_redirect, b.num_posts,
				m.poster_time, GREATEST(m.poster_time, m.modified_time) AS last_updated, m.id_msg, m.id_topic,
				c.name AS cat_name,' . (
					User::$me->is_guest
						? ' 1 AS is_read, 0 AS new_from'
						: ' (CASE WHEN COALESCE(lb.id_msg, 0) >= b.id_last_msg THEN 1 ELSE 0 END) AS is_read,
						COALESCE(lb.id_msg, -1) + 1 AS new_from'
					) . (
						empty(Config::$modSettings['lp_show_images_in_articles'])
							? ''
							: ', COALESCE(a.id_attach, 0) AS attach_id'
					) . (empty($this->columns) ? '' : ',
				' . implode(', ', $this->columns)) . '
			FROM {db_prefix}boards AS b
				INNER JOIN {db_prefix}categories AS c ON (b.id_cat = c.id_cat)
				LEFT JOIN {db_prefix}messages AS m ON (b.id_last_msg = m.id_msg)' . (User::$me->is_guest ? '' : '
				LEFT JOIN {db_prefix}log_boards AS lb ON (
					b.id_board = lb.id_board AND lb.id_member = {int:current_member}
				)') . (empty(Config::$modSettings['lp_show_images_in_articles']) ? '' : '
				LEFT JOIN {db_prefix}attachments AS a ON (
					b.id_last_msg = a.id_msg AND a.id_thumb <> 0 AND a.width > 0 AND a.height > 0
				)') . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE b.id_board IN ({array_int:selected_boards})
				AND {query_see_board}' . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)) . '
			ORDER BY ' . (empty(Config::$modSettings['lp_frontpage_order_by_replies']) ? '' : 'b.num_posts DESC, ')
				. $this->orders[$this->sorting] . '
			LIMIT {int:start}, {int:limit}',
			$this->params,
		);

		while ($row = Db::$db->fetch_assoc($result)) {
			$row['description'] = BBCodeParser::load()->parse(
				$row['description'], false, '', Utils::$context['description_allowed_tags']
			);

			$board = [
				'id'          => (int) $row['id_board'],
				'date'        => $this->getDate($row),
				'title'       => $this->getTitle($row),
				'link'        => $this->getLink($row),
				'is_new'      => empty($row['is_read']),
				'replies'     => $this->getRepliesData($row),
				'image'       => $this->getImage($row),
				'can_edit'    => $this->canEdit(),
				'edit_link'   => $this->getEditLink($row),
				'category'    => $this->getCategory($row),
				'is_redirect' => $row['is_redirect'],
			];

			$this->prepareTeaser($board, $row);

			$articles = [$row['id_board'] => $board];

			$this->events()->dispatch(PortalHook::frontBoardsRow, ['articles' => &$articles, 'row' => $row]);

			$board = $articles[$row['id_board']];

			yield $row['id_board'] => $board;
		}

		Db::$db->free_result($result);
	}

	public function getTotalCount(): int
	{
		if (empty($this->selectedBoards))
			return 0;

		$result = Db::$db->query(/** @lang text */ '
			SELECT COUNT(b.id_board)
			FROM {db_prefix}boards AS b
				INNER JOIN {db_prefix}categories AS c ON (b.id_cat = c.id_cat)' . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE b.id_board IN ({array_int:selected_boards})
				AND {query_see_board}' . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)),
			$this->params,
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	private function getDate(array $row): int
	{
		return $this->sorting === 3 && $row['last_updated'] ? (int) $row['last_updated'] : (int) $row['poster_time'];
	}

	private function getTitle(array $row): string
	{
		return BBCodeParser::load()->parse($row['name'], false, '', Utils::$context['description_allowed_tags']);
	}

	private function getLink(array $row): string
	{
		return $row['is_redirect']
			? $row['redirect'] . '" rel="nofollow noopener'
			: (Config::$scripturl . '?board=' . $row['id_board'] . '.0');
	}

	private function getRepliesData(array $row): array
	{
		return [
			'num'   => (int) $row['num_posts'],
			'title' => Lang::$txt['lp_replies'],
			'after' => '',
		];
	}

	private function getImage(array $row): string
	{
		if (empty(Config::$modSettings['lp_show_images_in_articles']))
			return '';

		$image = Str::getImageFromText($row['description']);

		if ($row['attach_id'] && empty($image)) {
			$image = Config::$scripturl . '?action=dlattach;topic=' . $row['id_topic'] . ';attach='
				. $row['attach_id'] . ';image';
		}

		if ($row['is_redirect'] && empty($image)) {
			$image = 'https://mini.s-shot.ru/300x200/JPEG/300/Z100/?' . urlencode(trim((string) $row['redirect']));
		}

		return $image;
	}

	private function canEdit(): bool
	{
		return User::$me->is_admin || User::$me->allowedTo('manage_boards');
	}

	private function getEditLink(array $row): string
	{
		return Config::$scripturl . '?action=admin;area=manageboards;sa=board;boardid=' . $row['id_board'];
	}

	private function getCategory(array $row): string
	{
		return BBCodeParser::load()->parse($row['cat_name'], false, '', Utils::$context['description_allowed_tags']);
	}

	private function prepareTeaser(array &$board, array $row): void
	{
		if (empty(Config::$modSettings['lp_show_teaser']))
			return;

		$board['teaser'] = Str::getTeaser($row['description']);
	}
}
