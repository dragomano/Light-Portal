<?php

namespace Bugo\LightPortal\Front;

use Bugo\LightPortal\{Helpers, Subs};

/**
 * BoardArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.7
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class BoardArticle extends AbstractArticle
{
	/**
	 * @var array
	 */
	private $selected_boards = [];

	/**
	 * Initialize class properties
	 *
	 * Инициализируем свойства класса
	 *
	 * @return void
	 */
	public function init()
	{
		global $modSettings, $user_info;

		$this->selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		$this->params = [
			'blank_string'    => '',
			'current_member'  => $user_info['id'],
			'selected_boards' => $this->selected_boards
		];

		$this->orders = [
			'b.id_last_msg DESC',
			'm.poster_time DESC',
			'm.poster_time',
			'last_updated DESC'
		];

		Subs::runAddons('frontBoards', array(&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders));
	}

	/**
	 * Get selected boards
	 *
	 * Получаем выбранные разделы
	 *
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function getData(int $start, int $limit): array
	{
		global $user_info, $smcFunc, $modSettings, $context, $scripturl, $txt;

		if (empty($this->selected_boards))
			return [];

		if (($boards = Helpers::cache()->get('articles_u' . $user_info['id'] . '_' . $start . '_' . $limit)) === null) {
			$this->params += array(
				'start' => $start,
				'limit' => $limit
			);

			$request = $smcFunc['db_query']('', '
				SELECT
					b.id_board, b.name, b.description, b.redirect, CASE WHEN b.redirect != {string:blank_string} THEN 1 ELSE 0 END AS is_redirect, b.num_posts,
					m.poster_time, GREATEST(m.poster_time, m.modified_time) AS last_updated, m.id_msg, m.id_topic, c.name AS cat_name,' . ($user_info['is_guest'] ? ' 1 AS is_read, 0 AS new_from' : '(CASE WHEN COALESCE(lb.id_msg, 0) >= b.id_last_msg THEN 1 ELSE 0 END) AS is_read, COALESCE(lb.id_msg, -1) + 1 AS new_from') . (!empty($modSettings['lp_show_images_in_articles']) ? ', COALESCE(a.id_attach, 0) AS attach_id' : '') . (!empty($this->columns) ? ',
					' . implode(', ', $this->columns) : '') . '
				FROM {db_prefix}boards AS b
					INNER JOIN {db_prefix}categories AS c ON (b.id_cat = c.id_cat)
					LEFT JOIN {db_prefix}messages AS m ON (b.id_last_msg = m.id_msg)' . ($user_info['is_guest'] ? '' : '
					LEFT JOIN {db_prefix}log_boards AS lb ON (b.id_board = lb.id_board AND lb.id_member = {int:current_member})') . (!empty($modSettings['lp_show_images_in_articles']) ? '
					LEFT JOIN {db_prefix}attachments AS a ON (b.id_last_msg = a.id_msg AND a.id_thumb <> 0 AND a.width > 0 AND a.height > 0)' : '') . (!empty($this->tables) ? '
					' . implode("\n\t\t\t\t\t", $this->tables) : '') . '
				WHERE b.id_board IN ({array_int:selected_boards})
					AND {query_see_board}' . (!empty($this->wheres) ? '
					' . implode("\n\t\t\t\t\t", $this->wheres) : '') . '
				ORDER BY ' . (!empty($modSettings['lp_frontpage_order_by_num_replies']) ? 'b.num_posts DESC, ' : '') . $this->orders[$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
				LIMIT {int:start}, {int:limit}',
				$this->params
			);

			$boards = [];
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				$board_name  = parse_bbc($row['name'], false, '', $context['description_allowed_tags']);
				$description = parse_bbc($row['description'], false, '', $context['description_allowed_tags']);
				$cat_name    = parse_bbc($row['cat_name'], false, '', $context['description_allowed_tags']);

				$image = null;
				if (!empty($modSettings['lp_show_images_in_articles'])) {
					$board_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $description, $value);
					$image = $board_image ? array_pop($value) : (!empty($row['attach_id']) ? $scripturl . '?action=dlattach;topic=' . $row['id_topic'] . ';attach=' . $row['attach_id'] . ';image' : null);
				}

				if ($row['is_redirect'] && empty($image))
					$image = 'https://mini.s-shot.ru/300x200/JPEG/300/Z100/?' . urlencode(trim($row['redirect']));

				$boards[$row['id_board']] = array(
					'id'          => $row['id_board'],
					'date'        => $row['poster_time'],
					'title'       => $board_name,
					'link'        => $row['is_redirect'] ? ($row['redirect'] . '" rel="nofollow noopener') : ($scripturl . '?board=' . $row['id_board'] . '.0'),
					'is_new'      => empty($row['is_read']),
					'replies'     => array('num' => $row['num_posts'], 'title' => $txt['lp_replies']),
					'image'       => $image,
					'can_edit'    => $user_info['is_admin'] || allowedTo('manage_boards'),
					'edit_link'   => $scripturl . '?action=admin;area=manageboards;sa=board;boardid=' . $row['id_board'],
					'category'    => $cat_name,
					'is_redirect' => $row['is_redirect']
				);

				if (!empty($modSettings['lp_show_teaser']))
					$boards[$row['id_board']]['teaser'] = Helpers::getTeaser($description);

				if (!empty($modSettings['lp_frontpage_article_sorting']) && $modSettings['lp_frontpage_article_sorting'] == 3 && !empty($row['last_updated'])) {
					$boards[$row['id_board']]['last_post'] = $scripturl . '?topic=' . $row['id_topic'] . '.msg' . ($user_info['is_guest'] ? $row['id_msg'] : $row['new_from']) . (empty($row['is_read']) ? ';boardseen' : '') . '#new';

					$boards[$row['id_board']]['date'] = $row['last_updated'];
				}

				$boards[$row['id_board']]['msg_link'] = $boards[$row['id_board']]['link'];

				if (empty($boards[$row['id_board']]['is_redirect']))
					$boards[$row['id_board']]['msg_link'] = $scripturl . '?msg=' . $row['id_msg'];

				Subs::runAddons('frontBoardsOutput', array(&$boards, $row));
			}

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put('articles_u' . $user_info['id'] . '_' . $start . '_' . $limit, $boards);
		}

		return $boards;
	}

	/**
	 * Get count of selected boards
	 *
	 * Получаем количество выбранных разделов
	 *
	 * @return int
	 */
	public function getTotalCount(): int
	{
		global $user_info, $smcFunc;

		if (empty($this->selected_boards))
			return 0;

		if (($num_boards = Helpers::cache()->get('articles_u' . $user_info['id'] . '_total')) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(b.id_board)
				FROM {db_prefix}boards AS b
					INNER JOIN {db_prefix}categories AS c ON (b.id_cat = c.id_cat)' . (!empty($this->tables) ? '
					' . implode("\n\t\t\t\t\t", $this->tables) : '') . '
				WHERE b.id_board IN ({array_int:selected_boards})
					AND {query_see_board}' . (!empty($this->wheres) ? '
					' . implode("\n\t\t\t\t\t", $this->wheres) : ''),
				$this->params
			);

			[$num_boards] = $smcFunc['db_fetch_row']($request);

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put('articles_u' . $user_info['id'] . '_total', $num_boards);
		}

		return (int) $num_boards;
	}
}
