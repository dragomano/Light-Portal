<?php

namespace Bugo\LightPortal;

/**
 * FrontPage.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class FrontPage
{
	/**
	 * Number of columns (layout)
	 *
	 * @var int
	 */
	private static $num_columns = 12;

	/**
	 * Show articles on the portal frontpage
	 *
	 * Выводим статьи на главной странице портала
	 *
	 * @return void
	 */
	public static function show()
	{
		global $modSettings, $context, $scripturl, $txt;

		isAllowedTo('light_portal_view');

		if ($modSettings['lp_frontpage_mode'] == 1) {
			return Page::show();
		} elseif ($modSettings['lp_frontpage_mode'] == 2) {
			self::prepareArticles('topics');
			$context['sub_template'] = 'show_topics_as_articles';

			// Custom topic style
			//$context['lp_all_categories'] = self::getListSelectedBoards();
			//$context['sub_template']      = 'show_topics_as_custom_style';
		} elseif ($modSettings['lp_frontpage_mode'] == 3) {
			self::prepareArticles();
			$context['sub_template'] = 'show_pages_as_articles';
		} else {
			self::prepareArticles('boards');
			$context['sub_template'] = 'show_boards_as_articles';
		}

		$context['lp_frontpage_layout'] = self::getNumColumns();
		$context['canonical_url']       = $scripturl;

		loadTemplate('LightPortal/ViewFrontPage');

		$context['page_title'] = $modSettings['lp_frontpage_title'] ?: ($context['forum_name'] . ' - ' . $txt['lp_portal']);
		$context['linktree'][] = array(
			'name' => $txt['lp_portal']
		);
	}

	/**
	 * Get the number columns for the frontpage layout
	 *
	 * Получаем количество колонок для макета главной страницы
	 *
	 * @return int
	 */
	public static function getNumColumns()
	{
		global $modSettings;

		$num_columns = self::$num_columns;

		if (!empty($modSettings['lp_frontpage_layout'])) {
			switch ($modSettings['lp_frontpage_layout']) {
				case '1':
					$num_columns /= 2;
					break;
				case '2':
					$num_columns /= 3;
					break;
				case '3':
					$num_columns /= 4;
					break;
				default:
					$num_columns /= 6;
			}
		}

		return $num_columns;
	}

	/**
	 * Form an array of articles
	 *
	 * Формируем массив статей
	 *
	 * @param string $source (pages|topics|boards)
	 * @return void
	 */
	public static function prepareArticles(string $source = 'pages')
	{
		global $modSettings, $context, $scripturl;

		switch ($source) {
			case 'topics':
				$function = 'TopicsFromSelectedBoards';
				break;
			case 'boards':
				$function = 'SelectedBoards';
				break;
			default:
				$function = 'ActivePages';
		}

		$start = (int) $_REQUEST['start'];
		$limit = $modSettings['lp_num_items_per_page'] ?? 12;

		$getTotalFunction = 'getTotal' . $function;
		$total_items      = self::$getTotalFunction();

		if ($start >= $total_items) {
			send_http_status(404);
			$start = (floor(($total_items - 1) / $limit) + 1) * $limit - $limit;
		}

		$getFunction = 'get' . $function;
		$articles    = self::$getFunction($start, $limit);

		$articles = array_map(function ($article) use ($modSettings) {
			if (!empty($article['date'])) {
				$article['datetime'] = date('Y-m-d', $article['date']);
				$article['date'] = Helpers::getFriendlyTime($article['date']);
			}

			if (isset($article['title']))
				$article['title'] = Helpers::getPublicTitle($article);

			if (empty($article['image']) && !empty($modSettings['lp_image_placeholder']))
				$article['image'] = $modSettings['lp_image_placeholder'];

			return $article;
		}, $articles);

		$context['page_index'] = constructPageIndex($scripturl . '?action=portal', $_REQUEST['start'], $total_items, $limit);
		$context['start']      = &$_REQUEST['start'];

		$context['lp_frontpage_articles'] = $articles;

		loadJavaScriptFile('light_portal/jquery.matchHeight-min.js', array('minimize' => true));
		addInlineJavaScript('
		jQuery(document).ready(function ($) {
			$(".lp_frontpage_articles .roundframe").matchHeight();
		});', true);

		Subs::runAddons('frontpageAssets');
	}

	/**
	 * Get topics from selected boards
	 *
	 * Получаем темы из выбранных разделов
	 *
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public static function getTopicsFromSelectedBoards(int $start, int $limit)
	{
		global $modSettings, $user_info, $smcFunc, $scripturl, $context;

		$selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		if (empty($selected_boards))
			return [];

		if (($topics = cache_get_data('light_portal_fronttopics_u' . $user_info['id'] . '_' . $start . '_' . $limit, LP_CACHE_TIME)) === null) {
			$custom_columns    = [];
			$custom_tables     = [];
			$custom_wheres     = [];
			$custom_parameters = [
				'current_member'    => $user_info['id'],
				'is_approved'       => 1,
				'id_poll'           => 0,
				'id_redirect_topic' => 0,
				'selected_boards'   => $selected_boards,
				'start'             => $start,
				'limit'             => $limit
			];

			Subs::runAddons('frontTopics', array(&$custom_columns, &$custom_tables, &$custom_wheres, &$custom_parameters));

			$request = $smcFunc['db_query']('', '
				SELECT
					t.id_topic, t.id_board, t.num_views, t.num_replies, t.is_sticky, t.id_first_msg, t.id_member_started, mf.subject, mf.body, mf.smileys_enabled, COALESCE(mem.real_name, mf.poster_name) AS poster_name, mf.poster_time, mf.id_member, ml.id_msg, b.name, ' . ($user_info['is_guest'] ? '0' : 'COALESCE(lt.id_msg, lmr.id_msg, -1) + 1') . ' AS new_from, ml.id_msg_modified' . (!empty($custom_columns) ? ',
					' . implode(', ', $custom_columns) : '') . '
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
					INNER JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
					LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = mf.id_member)
					LEFT JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)' . ($user_info['is_guest'] ? '' : '
					LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
					LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = t.id_board AND lmr.id_member = {int:current_member})') . (!empty($custom_tables) ? '
					' . implode("\n\t\t\t\t\t", $custom_tables) : '') . '
				WHERE t.approved = {int:is_approved}
					AND t.id_poll = {int:id_poll}
					AND t.id_redirect_topic = {int:id_redirect_topic}
					AND t.id_board IN ({array_int:selected_boards})
					AND {query_wanna_see_board}' . (!empty($custom_wheres) ? '
					' . implode("\n\t\t\t\t\t", $custom_wheres) : '') . '
				ORDER BY t.id_last_msg DESC
				LIMIT {int:start}, {int:limit}',
				$custom_parameters
			);

			$topics = $messages = [];
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				if (!isset($topics[$row['id_topic']])) {
					Helpers::cleanBbcode($row['subject']);
					censorText($row['subject']);
					censorText($row['body']);

					$image = null;
					if (!empty($modSettings['lp_show_images_in_articles'])) {
						$body = parse_bbc($row['body'], false);
						$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $body, $value);
						$image = $first_post_image ? array_pop($value) : null;
					}

					$row['body'] = preg_replace('~\[spoiler].*?\[/spoiler]~Usi', '', $row['body']);
					$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['id_first_msg']), array('<br>' => ' ')));

					$colorClass = '';
					if ($row['is_sticky'])
						$colorClass .= ' alternative2';

					$messages[] = $row['id_first_msg'];
					$topics[$row['id_topic']] = array(
						'id'          => $row['id_topic'],
						'id_msg'      => $row['id_first_msg'],
						'author_id'   => $row['id_member'],
						'author_link' => $scripturl . '?action=profile;u=' . $row['id_member'],
						'author_name' => $row['poster_name'],
						'date'        => $row['poster_time'],
						'subject'     => $row['subject'],
						'teaser'      => Helpers::getTeaser($row['body']),
						'link'        => $scripturl . '?topic=' . $row['id_topic'] . ($row['new_from'] > $row['id_msg_modified'] ? '.0' : '.new;topicseen#new'),
						'board_link'  => $scripturl . '?board=' . $row['id_board'] . '.0',
						'board_name'  => $row['name'],
						'is_sticky'   => !empty($row['is_sticky']),
						'is_new'      => $row['new_from'] <= $row['id_msg_modified'],
						'num_views'   => $row['num_views'],
						'num_replies' => $row['num_replies'],
						'css_class'   => $colorClass,
						'image'       => $image,
						'can_edit'    => $user_info['is_admin'] || ($row['id_member'] == $user_info['id'] && !empty($user_info['id']))
					);
				}

				Subs::runAddons('frontTopicsOutput', array(&$topics, $row));
			}

			$smcFunc['db_free_result']($request);
			$context['lp_num_queries']++;

			if (!empty($messages) && !empty($modSettings['lp_show_images_in_articles'])) {
				$request = $smcFunc['db_query']('', '
					SELECT a.id_attach, a.id_msg, t.id_topic
					FROM {db_prefix}attachments AS a
						LEFT JOIN {db_prefix}topics AS t ON (t.id_first_msg = a.id_msg)
					WHERE a.id_msg IN ({array_int:message_list})
						AND a.width <> 0
						AND a.height <> 0
						AND a.approved = {int:is_approved}
						AND a.attachment_type = {int:attachment_type}
					ORDER BY a.id_attach',
					array(
						'message_list'    => $messages,
						'attachment_type' => 0,
						'is_approved'     => 1
					)
				);

				$attachments = [];
				while ($row = $smcFunc['db_fetch_assoc']($request))
					$attachments[$row['id_topic']][] = $scripturl . '?action=dlattach;topic=' . $row['id_topic'] . '.0;attach=' . $row['id_attach'] . ';image';

				$smcFunc['db_free_result']($request);
				$context['lp_num_queries']++;

				foreach ($attachments as $id_topic => $data)
					$topics[$id_topic]['image'] = $data[0];
			}

			cache_put_data('light_portal_fronttopics_u' . $user_info['id'] . '_' . $start . '_' . $limit, $topics, LP_CACHE_TIME);
		}

		return $topics;
	}

	/**
	 * Get count of active topics from selected boards
	 *
	 * Получаем количество активных тем из выбранных разделов
	 *
	 * @return int
	 */
	public static function getTotalTopicsFromSelectedBoards()
	{
		global $modSettings, $user_info, $smcFunc, $context;

		$selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		if (empty($selected_boards))
			return 0;

		if (($num_topics = cache_get_data('light_portal_fronttopics_u' . $user_info['id'] . '_total', LP_CACHE_TIME)) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(t.id_topic)
				FROM {db_prefix}topics AS t
					LEFT JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
				WHERE t.approved = {int:is_approved}
					AND t.id_poll = {int:id_poll}
					AND t.id_redirect_topic = {int:id_redirect_topic}
					AND t.id_board IN ({array_int:selected_boards})
					AND {query_wanna_see_board}',
				array(
					'is_approved'       => 1,
					'id_poll'           => 0,
					'id_redirect_topic' => 0,
					'selected_boards'   => $selected_boards
				)
			);

			list ($num_topics) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
			$context['lp_num_queries']++;

			cache_put_data('light_portal_fronttopics_u' . $user_info['id'] . '_total', $num_topics, LP_CACHE_TIME);
		}

		return $num_topics;
	}

	/**
	 * Get active pages
	 *
	 * Получаем активные страницы
	 *
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public static function getActivePages(int $start, int $limit)
	{
		global $user_info, $smcFunc, $modSettings, $scripturl, $context;

		if (($pages = cache_get_data('light_portal_frontpages_u' . $user_info['id'] . '_' . $start . '_' . $limit, LP_CACHE_TIME)) === null) {
			$titles = Helpers::getFromCache('all_titles', 'getAllTitles', '\Bugo\LightPortal\Subs', LP_CACHE_TIME, 'page');

			$custom_columns    = [];
			$custom_tables     = [];
			$custom_wheres     = [];
			$custom_parameters = [
				'type'         => 'page',
				'status'       => Page::STATUS_ACTIVE,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions(),
				'start'        => $start,
				'limit'        => $limit
			];

			Subs::runAddons('frontPages', array(&$custom_columns, &$custom_tables, &$custom_wheres, &$custom_parameters));

			$request = $smcFunc['db_query']('', '
				SELECT
					p.page_id, p.author_id, p.alias, p.content, p.description, p.type, p.status, p.num_views, p.num_comments,
					GREATEST(p.created_at, p.updated_at) AS date, mem.real_name AS author_name' . (!empty($custom_columns) ? ',
					' . implode(', ', $custom_columns) : '') . '
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)' . (!empty($custom_tables) ? '
					' . implode("\n\t\t\t\t\t", $custom_tables) : '') . '
				WHERE p.status = {int:status}
					AND p.created_at <= {int:current_time}
					AND p.permissions IN ({array_int:permissions})' . (!empty($custom_wheres) ? '
					' . implode("\n\t\t\t\t\t", $custom_wheres) : '') . '
				ORDER BY date DESC
				LIMIT {int:start}, {int:limit}',
				$custom_parameters
			);

			$pages = [];
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				Subs::parseContent($row['content'], $row['type']);

				$image = null;
				if (!empty($modSettings['lp_show_images_in_articles'])) {
					$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $row['content'], $value);
					$image = $first_post_image ? array_pop($value) : null;
				}

				if (!isset($pages[$row['page_id']])) {
					$pages[$row['page_id']] = array(
						'id'            => $row['page_id'],
						'author_id'     => $row['author_id'],
						'author_link'   => $scripturl . '?action=profile;u=' . $row['author_id'],
						'author_name'   => $row['author_name'],
						'teaser'        => Helpers::getTeaser($row['description'] ?: strip_tags($row['content'])),
						'type'          => $row['type'],
						'num_views'     => $row['num_views'],
						'num_comments'  => $row['num_comments'],
						'date'          => $row['date'],
						'is_new'        => $user_info['last_login'] < $row['date'] && $row['author_id'] != $user_info['id'],
						'link'          => $scripturl . '?page=' . $row['alias'],
						'image'         => $image,
						'can_edit'      => $user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $row['author_id'] == $user_info['id'])
					);
				}

				$pages[$row['page_id']]['title'] = $titles[$row['page_id']];

				Subs::runAddons('frontPagesOutput', array(&$pages, $row));
			}

			$smcFunc['db_free_result']($request);
			$context['lp_num_queries']++;

			cache_put_data('light_portal_frontpages_u' . $user_info['id'] . '_' . $start . '_' . $limit, $pages, LP_CACHE_TIME);
		}

		return $pages;
	}

	/**
	 * Get count of active pages
	 *
	 * Получаем количество активных страниц
	 *
	 * @return int
	 */
	public static function getTotalActivePages()
	{
		global $user_info, $smcFunc, $context;

		if (($num_pages = cache_get_data('light_portal_frontpages_u' . $user_info['id'] . '_total', LP_CACHE_TIME)) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(page_id)
				FROM {db_prefix}lp_pages
				WHERE status = {int:status}
					AND created_at <= {int:current_time}
					AND permissions IN ({array_int:permissions})',
				array(
					'status'       => Page::STATUS_ACTIVE,
					'current_time' => time(),
					'permissions'  => Helpers::getPermissions()
				)
			);

			list ($num_pages) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
			$context['lp_num_queries']++;

			cache_put_data('light_portal_frontpages_u' . $user_info['id'] . '_total', $num_pages, LP_CACHE_TIME);
		}

		return $num_pages;
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
	public static function getSelectedBoards(int $start, int $limit)
	{
		global $modSettings, $user_info, $smcFunc, $context, $scripturl;

		$selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		if (empty($selected_boards))
			return [];

		if (($boards = cache_get_data('light_portal_frontboards_u' . $user_info['id'] . '_' . $start . '_' . $limit, LP_CACHE_TIME)) === null) {
			$custom_columns    = [];
			$custom_tables     = [];
			$custom_wheres     = [];
			$custom_parameters = [
				'blank_string'    => '',
				'current_member'  => $user_info['id'],
				'selected_boards' => $selected_boards,
				'start'           => $start,
				'limit'           => $limit
			];

			Subs::runAddons('frontBoards', array(&$custom_columns, &$custom_tables, &$custom_wheres, &$custom_parameters));

			$request = $smcFunc['db_query']('', '
				SELECT
					b.id_board, b.name, b.description, b.redirect, CASE WHEN b.redirect != {string:blank_string} THEN 1 ELSE 0 END AS is_redirect, b.num_posts,
					GREATEST(m.poster_time, m.modified_time) AS last_updated, m.id_msg, m.id_topic, c.name AS cat_name,' . ($user_info['is_guest'] ? ' 1 AS is_read, 0 AS new_from' : '
					(CASE WHEN COALESCE(lb.id_msg, 0) >= b.id_last_msg THEN 1 ELSE 0 END) AS is_read, COALESCE(lb.id_msg, -1) + 1 AS new_from') . (!empty($modSettings['lp_show_images_in_articles']) ? ', COALESCE(a.id_attach, 0) AS attach_id' : '') . (!empty($custom_columns) ? ',
					' . implode(', ', $custom_columns) : '') . '
				FROM {db_prefix}boards AS b
					LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
					LEFT JOIN {db_prefix}messages AS m ON (m.id_msg = b.id_last_msg)' . ($user_info['is_guest'] ? '' : '
					LEFT JOIN {db_prefix}log_boards AS lb ON (lb.id_board = b.id_board AND lb.id_member = {int:current_member})') . (!empty($modSettings['lp_show_images_in_articles']) ? '
					LEFT JOIN {db_prefix}attachments AS a ON (a.id_msg = b.id_last_msg AND a.id_thumb <> 0 AND a.width > 0 AND a.height > 0)' : '') . (!empty($custom_tables) ? '
					' . implode("\n\t\t\t\t\t", $custom_tables) : '') . '
				WHERE b.id_board IN ({array_int:selected_boards})
					AND {query_see_board}' . (!empty($custom_wheres) ? '
					' . implode("\n\t\t\t\t\t", $custom_wheres) : '') . '
				ORDER BY b.id_last_msg DESC
				LIMIT {int:start}, {int:limit}',
				$custom_parameters
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

				$description = strip_tags($description);

				$boards[$row['id_board']] = array(
					'id'          => $row['id_board'],
					'name'        => $board_name,
					'teaser'      => Helpers::getTeaser($description),
					'category'    => $cat_name,
					'link'        => $row['is_redirect'] ? $row['redirect'] : $scripturl . '?board=' . $row['id_board'] . '.0',
					'is_redirect' => $row['is_redirect'],
					'is_updated'  => empty($row['is_read']),
					'num_posts'   => $row['num_posts'],
					'image'       => $image,
					'can_edit'    => $user_info['is_admin'] || allowedTo('manage_boards')
				);

				if (!empty($row['last_updated'])) {
					$boards[$row['id_board']]['last_post'] = $scripturl . '?topic=' . $row['id_topic'] . '.msg' . ($user_info['is_guest'] ? $row['id_msg'] : $row['new_from']) . (empty($row['is_read']) ? ';boardseen' : '') . '#new';
					$boards[$row['id_board']]['date'] = $row['last_updated'];
				}

				Subs::runAddons('frontBoardsOutput', array(&$boards, $row));
			}

			$smcFunc['db_free_result']($request);
			$context['lp_num_queries']++;

			cache_put_data('light_portal_frontboards_u' . $user_info['id'] . '_' . $start . '_' . $limit, $boards, LP_CACHE_TIME);
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
	public static function getTotalSelectedBoards()
	{
		global $modSettings, $context, $smcFunc;

		$selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		if (empty($selected_boards))
			return 0;

		if (($num_boards = cache_get_data('light_portal_frontboards_u' . $context['user']['id'] . '_total', LP_CACHE_TIME)) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(b.id_board)
				FROM {db_prefix}boards AS b
					LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
				WHERE b.id_board IN ({array_int:selected_boards})
					AND {query_see_board}',
				array(
					'selected_boards' => $selected_boards
				)
			);

			list ($num_boards) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
			$context['lp_num_queries']++;

			cache_put_data('light_portal_frontboards_u' . $context['user']['id'] . '_total', $num_boards, LP_CACHE_TIME);
		}

		return $num_boards;
	}

	/**
	 * Get the list of categories with boards, considering the selected boards in the portal settings
	 *
	 * Получаем список всех категорий с разделами, учитывая отмеченные разделы в настройках портала
	 *
	 * @return array
	 */
	public static function getListSelectedBoards()
	{
		global $sourcedir, $modSettings;

		require_once($sourcedir . '/Subs-MessageIndex.php');

		$boardListOptions = array(
			'ignore_boards'   => true,
			'use_permissions' => true,
			'not_redirection' => true,
			'included_boards' => !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : []
		);

		return getBoardList($boardListOptions);
	}
}
