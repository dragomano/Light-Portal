<?php declare(strict_types=1);

/**
 * BoardArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Front;

use Bugo\LightPortal\Utils\{Config, Lang, User, Utils};

if (! defined('SMF'))
	die('No direct access...');

class BoardArticle extends AbstractArticle
{
	private array $selected_boards = [];

	public function init(): void
	{
		$this->selected_boards = empty(Config::$modSettings['lp_frontpage_boards']) ? [] : explode(',', Config::$modSettings['lp_frontpage_boards']);

		$this->params = [
			'blank_string'    => '',
			'current_member'  => User::$info['id'],
			'selected_boards' => $this->selected_boards
		];

		$this->orders = [
			'b.id_last_msg DESC',
			'm.poster_time DESC',
			'm.poster_time',
			'last_updated DESC'
		];

		$this->hook('frontBoards', [&$this->columns, &$this->tables, &$this->params, &$this->wheres, &$this->orders]);
	}

	public function getData(int $start, int $limit): array
	{
		if (empty($this->selected_boards))
			return [];

		$this->params += [
			'start' => $start,
			'limit' => $limit
		];

		$result = Utils::$smcFunc['db_query']('', '
			SELECT
				b.id_board, b.name, b.description, b.redirect, CASE WHEN b.redirect != {string:blank_string} THEN 1 ELSE 0 END AS is_redirect, b.num_posts,
				m.poster_time, GREATEST(m.poster_time, m.modified_time) AS last_updated, m.id_msg, m.id_topic, c.name AS cat_name,' . (User::$info['is_guest'] ? ' 1 AS is_read, 0 AS new_from' : ' (CASE WHEN COALESCE(lb.id_msg, 0) >= b.id_last_msg THEN 1 ELSE 0 END) AS is_read, COALESCE(lb.id_msg, -1) + 1 AS new_from') . (empty(Config::$modSettings['lp_show_images_in_articles']) ? '' : ', COALESCE(a.id_attach, 0) AS attach_id') . (empty($this->columns) ? '' : ',
				' . implode(', ', $this->columns)) . '
			FROM {db_prefix}boards AS b
				INNER JOIN {db_prefix}categories AS c ON (b.id_cat = c.id_cat)
				LEFT JOIN {db_prefix}messages AS m ON (b.id_last_msg = m.id_msg)' . (User::$info['is_guest'] ? '' : '
				LEFT JOIN {db_prefix}log_boards AS lb ON (b.id_board = lb.id_board AND lb.id_member = {int:current_member})') . (Config::$modSettings['lp_show_images_in_articles'] ? '
				LEFT JOIN {db_prefix}attachments AS a ON (b.id_last_msg = a.id_msg AND a.id_thumb <> 0 AND a.width > 0 AND a.height > 0)' : '') . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE b.id_board IN ({array_int:selected_boards})
				AND {query_see_board}' . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)) . '
			ORDER BY ' . (empty(Config::$modSettings['lp_frontpage_order_by_replies']) ? '' : 'b.num_posts DESC, ') . $this->orders[Config::$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
			LIMIT {int:start}, {int:limit}',
			$this->params
		);

		$boards = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$board_name  = $this->parseBbc($row['name'], false, '', Utils::$context['description_allowed_tags']);
			$description = $this->parseBbc($row['description'], false, '', Utils::$context['description_allowed_tags']);
			$cat_name    = $this->parseBbc($row['cat_name'], false, '', Utils::$context['description_allowed_tags']);

			if (! empty(Config::$modSettings['lp_show_images_in_articles'])) {
				$image = $this->getImageFromText($description);

				if ($row['attach_id'] && empty($image)) {
					$image = Config::$scripturl . '?action=dlattach;topic=' . $row['id_topic'] . ';attach=' . $row['attach_id'] . ';image';
				}

				if ($row['is_redirect'] && empty($image)) {
					$image = 'https://mini.s-shot.ru/300x200/JPEG/300/Z100/?' . urlencode(trim($row['redirect']));
				}
			}

			$boards[$row['id_board']] = [
				'id'          => $row['id_board'],
				'date'        => $row['poster_time'],
				'title'       => $board_name,
				'link'        => $row['is_redirect'] ? ($row['redirect'] . '" rel="nofollow noopener') : (Config::$scripturl . '?board=' . $row['id_board'] . '.0'),
				'is_new'      => empty($row['is_read']),
				'replies'     => ['num' => $row['num_posts'], 'title' => Lang::$txt['lp_replies'], 'after' => ''],
				'image'       => $image ?? '',
				'can_edit'    => User::$info['is_admin'] || $this->allowedTo('manage_boards'),
				'edit_link'   => Config::$scripturl . '?action=admin;area=manageboards;sa=board;boardid=' . $row['id_board'],
				'category'    => $cat_name,
				'is_redirect' => $row['is_redirect']
			];

			if (! empty(Config::$modSettings['lp_show_teaser']))
				$boards[$row['id_board']]['teaser'] = $this->getTeaser($description);

			if (! empty(Config::$modSettings['lp_frontpage_article_sorting']) && Config::$modSettings['lp_frontpage_article_sorting'] == 3 && $row['last_updated']) {
				$boards[$row['id_board']]['last_post'] = Config::$scripturl . '?topic=' . $row['id_topic'] . '.msg' . (User::$info['is_guest'] ? $row['id_msg'] : $row['new_from']) . (empty($row['is_read']) ? ';boardseen' : '') . '#new';

				$boards[$row['id_board']]['date'] = $row['last_updated'];
			}

			$boards[$row['id_board']]['msg_link'] = $boards[$row['id_board']]['link'];

			if (empty($boards[$row['id_board']]['is_redirect']))
				$boards[$row['id_board']]['msg_link'] = Config::$scripturl . '?msg=' . $row['id_msg'];

			$this->hook('frontBoardsOutput', [&$boards, $row]);
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $boards;
	}

	public function getTotalCount(): int
	{
		if (empty($this->selected_boards))
			return 0;

		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT COUNT(b.id_board)
			FROM {db_prefix}boards AS b
				INNER JOIN {db_prefix}categories AS c ON (b.id_cat = c.id_cat)' . (empty($this->tables) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->tables)) . '
			WHERE b.id_board IN ({array_int:selected_boards})
				AND {query_see_board}' . (empty($this->wheres) ? '' : '
				' . implode("\n\t\t\t\t\t", $this->wheres)),
			$this->params
		);

		[$num_boards] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return (int) $num_boards;
	}
}
