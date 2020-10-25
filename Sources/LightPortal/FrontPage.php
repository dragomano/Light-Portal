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
 * @version 1.2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class FrontPage
{
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

		$context['lp_need_lower_case'] = Helpers::isLowerCaseForDates();

		switch ($modSettings['lp_frontpage_mode']) {
			case 1:
				return Page::show();
			case 2:
				self::prepareArticles('topics');
				$context['sub_template'] = 'show_topics_as_articles';
				break;
			case 3:
				self::prepareArticles();
				$context['sub_template'] = 'show_pages_as_articles';
				break;
			default:
				self::prepareArticles('boards');
				$context['sub_template'] = 'show_boards_as_articles';
		}

		Subs::runAddons('frontpageCustomTemplate');

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

		$num_columns = 12;

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

		$start = Helpers::request('start');
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
				$article['date']     = Helpers::getFriendlyTime($article['date']);
			}

			if (isset($article['title']))
				$article['title'] = Helpers::getPublicTitle($article);

			if (empty($article['image']) && !empty($modSettings['lp_image_placeholder']))
				$article['image'] = $modSettings['lp_image_placeholder'];

			return $article;
		}, $articles);

		$context['page_index'] = constructPageIndex($scripturl . '?action=portal', Helpers::request()->get('start'), $total_items, $limit);
		$context['start']      = Helpers::request()->get('start');

		$context['lp_frontpage_articles'] = $articles;

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
		global $modSettings, $user_info, $smcFunc, $scripturl, $txt, $context;

		$selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		if (empty($selected_boards))
			return [];

		if (($topics = Helpers::cache()->get('articles_u' . $user_info['id'] . '_' . $start . '_' . $limit, LP_CACHE_TIME)) === null) {
			$custom_columns    = [];
			$custom_tables     = [];
			$custom_wheres     = [];

			$custom_parameters = [
				'current_member'    => $user_info['id'],
				'is_approved'       => 1,
				'id_poll'           => 0,
				'id_redirect_topic' => 0,
				'attachment_type'   => 0,
				'selected_boards'   => $selected_boards,
				'start'             => $start,
				'limit'             => $limit
			];

			$custom_sorting = [
				't.id_last_msg DESC',
				'mf.poster_time DESC',
				'mf.poster_time',
			];

			Subs::runAddons('frontTopics', array(&$custom_columns, &$custom_tables, &$custom_wheres, &$custom_parameters, &$custom_sorting));

			$request = $smcFunc['db_query']('', '
				SELECT
					t.id_topic, t.id_board, t.num_views, t.num_replies, t.is_sticky, t.id_first_msg, t.id_member_started, mf.subject, mf.body, mf.smileys_enabled, COALESCE(mem.real_name, mf.poster_name) AS poster_name, mf.poster_time, mf.id_member, ml.id_msg, ml.id_member AS last_poster_id, ml.poster_name AS last_poster_name, ml.body AS last_body, ml.poster_time AS last_msg_time, b.name, ' . (!empty($modSettings['lp_show_images_in_articles']) ? '(
						SELECT id_attach
						FROM {db_prefix}attachments
						WHERE id_msg = t.id_first_msg
							AND width <> 0
							AND height <> 0
							AND approved = {int:is_approved}
							AND attachment_type = {int:attachment_type}
						ORDER BY id_attach
						LIMIT 1
					) AS id_attach, ' : '') . ($user_info['is_guest'] ? '0' : 'COALESCE(lt.id_msg, lmr.id_msg, -1) + 1') . ' AS new_from, ml.id_msg_modified' . (!empty($custom_columns) ? ',
					' . implode(', ', $custom_columns) : '') . '
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}messages AS ml ON (t.id_last_msg = ml.id_msg)
					INNER JOIN {db_prefix}messages AS mf ON (t.id_first_msg = mf.id_msg)
					INNER JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)
					LEFT JOIN {db_prefix}members AS mem ON (mf.id_member = mem.id_member)' . ($user_info['is_guest'] ? '' : '
					LEFT JOIN {db_prefix}log_topics AS lt ON (t.id_topic = lt.id_topic AND lt.id_member = {int:current_member})
					LEFT JOIN {db_prefix}log_mark_read AS lmr ON (t.id_board = lmr.id_board AND lmr.id_member = {int:current_member})') . (!empty($custom_tables) ? '
					' . implode("\n\t\t\t\t\t", $custom_tables) : '') . '
				WHERE t.approved = {int:is_approved}
					AND t.id_poll = {int:id_poll}
					AND t.id_redirect_topic = {int:id_redirect_topic}
					AND t.id_board IN ({array_int:selected_boards})
					AND {query_wanna_see_board}' . (!empty($custom_wheres) ? '
					' . implode("\n\t\t\t\t\t", $custom_wheres) : '') . '
				ORDER BY ' . (!empty($modSettings['lp_frontpage_order_by_num_replies']) ? 'IF (t.num_replies > 0, 0, 1), t.num_replies DESC, ' : '') . $custom_sorting[$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
				LIMIT {int:start}, {int:limit}',
				$custom_parameters
			);

			$topics = [];
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				if (!isset($topics[$row['id_topic']])) {
					Helpers::cleanBbcode($row['subject']);
					censorText($row['subject']);
					censorText($row['body']);
					censorText($row['last_body']);

					$image = null;
					if (!empty($row['id_attach']))
						$image = $scripturl . '?action=dlattach;topic=' . $row['id_topic'] . '.0;attach=' . $row['id_attach'] . ';image';

					if (!empty($modSettings['lp_show_images_in_articles']) && empty($image)) {
						$body = parse_bbc($row['body'], false);
						$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $body, $value);
						$image = $first_post_image ? array_pop($value) : null;
					}

					$row['body'] = preg_replace('~\[spoiler.*].*?\[\/spoiler]~Usi', $txt['spoiler'] ?? '', $row['body']);
					$row['body'] = preg_replace('~\[code.*].*?\[\/code]~Usi', $txt['code'], $row['body']);
					$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['id_first_msg']), array('<br>' => ' ')), '<blockquote><cite>');

					$row['last_body'] = preg_replace('~\[spoiler.*].*?\[\/spoiler]~Usi', $txt['spoiler'] ?? '', $row['last_body']);
					$row['last_body'] = preg_replace('~\[code.*].*?\[\/code]~Usi', $txt['code'], $row['last_body']);
					$row['last_body'] = strip_tags(strtr(parse_bbc($row['last_body'], $row['smileys_enabled'], $row['id_msg']), array('<br>' => ' ')), '<blockquote><cite>');

					$colorClass = '';
					if ($row['is_sticky'])
						$colorClass .= ' sticky';

					$topics[$row['id_topic']] = array(
						'id'          => $row['id_topic'],
						'id_msg'      => $row['id_first_msg'],
						'author_id'   => $author_id = empty($row['num_replies']) ? $row['id_member'] : $row['last_poster_id'],
						'author_link' => $scripturl . '?action=profile;u=' . $author_id,
						'author_name' => empty($row['num_replies']) ? $row['poster_name'] : $row['last_poster_name'],
						'date'        => empty($modSettings['lp_frontpage_article_sorting']) && !empty($row['last_msg_time']) ? $row['last_msg_time'] : $row['poster_time'],
						'subject'     => $row['subject'],
						'teaser'      => Helpers::getTeaser(empty($row['num_replies']) ? $row['body'] : $row['last_body']),
						'link'        => $scripturl . '?topic=' . $row['id_topic'] . ($row['new_from'] > $row['id_msg_modified'] ? '.0' : '.new;topicseen#new'),
						'board_link'  => $scripturl . '?board=' . $row['id_board'] . '.0',
						'board_name'  => $row['name'],
						'is_sticky'   => !empty($row['is_sticky']),
						'is_new'      => $row['new_from'] <= $row['id_msg_modified'] && $row['last_poster_id'] != $user_info['id'],
						'num_views'   => $row['num_views'],
						'num_replies' => $row['num_replies'],
						'css_class'   => $colorClass,
						'image'       => $image,
						'can_edit'    => $user_info['is_admin'] || ($row['id_member'] == $user_info['id'] && !empty($user_info['id']))
					);

					$topics[$row['id_topic']]['msg_link'] = $topics[$row['id_topic']]['link'];

					if (!empty($topics[$row['id_topic']]['num_replies']))
						$topics[$row['id_topic']]['msg_link'] = $scripturl . '?msg=' . $row['id_msg'];
				}

				Subs::runAddons('frontTopicsOutput', array(&$topics, $row));
			}

			$smcFunc['db_free_result']($request);
			$context['lp_num_queries']++;

			Helpers::cache()->put('articles_u' . $user_info['id'] . '_' . $start . '_' . $limit, $topics, LP_CACHE_TIME);
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

		if (($num_topics = Helpers::cache()->get('articles_u' . $user_info['id'] . '_total', LP_CACHE_TIME)) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(t.id_topic)
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)
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

			[$num_topics] = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
			$context['lp_num_queries']++;

			Helpers::cache()->put('articles_u' . $user_info['id'] . '_total', (int) $num_topics, LP_CACHE_TIME);
		}

		return (int) $num_topics;
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

		if (($pages = Helpers::cache()->get('articles_u' . $user_info['id'] . '_' . $start . '_' . $limit, LP_CACHE_TIME)) === null) {
			$titles = Helpers::cache('all_titles', 'getAllTitles', '\Bugo\LightPortal\Subs', LP_CACHE_TIME, 'page');

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

			$custom_sorting = [
				'comment_date DESC',
				'p.created_at DESC',
				'p.created_at',
			];

			Subs::runAddons('frontPages', array(&$custom_columns, &$custom_tables, &$custom_wheres, &$custom_parameters, &$custom_sorting));

			$request = $smcFunc['db_query']('', '
				SELECT
					p.page_id, p.author_id, p.alias, p.content, p.description, p.type, p.status, p.num_views, p.num_comments, p.created_at, GREATEST(p.created_at, p.updated_at) AS date,
					mem.real_name AS author_name, (
						SELECT created_at
						FROM {db_prefix}lp_comments
						WHERE page_id = p.page_id
						ORDER BY created_at DESC
						LIMIT 1
					) AS comment_date, (
						SELECT author_id
						FROM {db_prefix}lp_comments
						WHERE created_at = comment_date
						LIMIT 1
					) AS comment_author_id, (
						SELECT real_name
						FROM {db_prefix}members
						WHERE id_member = comment_author_id
						LIMIT 1
					) AS comment_author_name' . (!empty($custom_columns) ? ',
					' . implode(', ', $custom_columns) : '') . '
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)' . (!empty($custom_tables) ? '
					' . implode("\n\t\t\t\t\t", $custom_tables) : '') . '
				WHERE p.status = {int:status}
					AND p.created_at <= {int:current_time}
					AND p.permissions IN ({array_int:permissions})' . (!empty($custom_wheres) ? '
					' . implode("\n\t\t\t\t\t", $custom_wheres) : '') . '
				ORDER BY ' . (!empty($modSettings['lp_frontpage_order_by_num_replies']) ? 'IF (num_comments > 0, 0, 1), num_comments DESC, ' : '') . $custom_sorting[$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
				LIMIT {int:start}, {int:limit}',
				$custom_parameters
			);

			$pages = [];
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				Helpers::parseContent($row['content'], $row['type']);

				$image = null;
				if (!empty($modSettings['lp_show_images_in_articles'])) {
					$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $row['content'], $value);
					$image = $first_post_image ? array_pop($value) : null;
				}

				if (!isset($pages[$row['page_id']])) {
					$pages[$row['page_id']] = array(
						'id'            => $row['page_id'],
						'author_id'     => $author_id = empty($row['num_comments']) ? $row['author_id'] : $row['comment_author_id'],
						'author_link'   => $scripturl . '?action=profile;u=' . $author_id,
						'author_name'   => empty($row['num_comments']) ? $row['author_name'] : $row['comment_author_name'],
						'teaser'        => Helpers::getTeaser($row['description'] ?: strip_tags($row['content'])),
						'type'          => $row['type'],
						'num_views'     => $row['num_views'],
						'num_comments'  => $row['num_comments'],
						'date'          => empty($modSettings['lp_frontpage_article_sorting']) && !empty($row['comment_date']) ? $row['comment_date'] : $row['created_at'],
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

			Helpers::cache()->put('articles_u' . $user_info['id'] . '_' . $start . '_' . $limit, $pages, LP_CACHE_TIME);
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

		if (($num_pages = Helpers::cache()->get('articles_u' . $user_info['id'] . '_total', LP_CACHE_TIME)) === null) {
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

			[$num_pages] = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
			$context['lp_num_queries']++;

			Helpers::cache()->put('articles_u' . $user_info['id'] . '_total', (int) $num_pages, LP_CACHE_TIME);
		}

		return (int) $num_pages;
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

		if (($boards = Helpers::cache()->get('articles_u' . $user_info['id'] . '_' . $start . '_' . $limit, LP_CACHE_TIME)) === null) {
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

			$custom_sorting = [
				'b.id_last_msg DESC',
				'm.poster_time DESC',
				'm.poster_time',
			];

			Subs::runAddons('frontBoards', array(&$custom_columns, &$custom_tables, &$custom_wheres, &$custom_parameters, &$custom_sorting));

			$request = $smcFunc['db_query']('', '
				SELECT
					b.id_board, b.name, b.description, b.redirect, CASE WHEN b.redirect != {string:blank_string} THEN 1 ELSE 0 END AS is_redirect, b.num_posts,
					GREATEST(m.poster_time, m.modified_time) AS last_updated, m.id_msg, m.id_topic, c.name AS cat_name,' . ($user_info['is_guest'] ? ' 1 AS is_read, 0 AS new_from' : '
					(CASE WHEN COALESCE(lb.id_msg, 0) >= b.id_last_msg THEN 1 ELSE 0 END) AS is_read, COALESCE(lb.id_msg, -1) + 1 AS new_from') . (!empty($modSettings['lp_show_images_in_articles']) ? ', COALESCE(a.id_attach, 0) AS attach_id' : '') . (!empty($custom_columns) ? ',
					' . implode(', ', $custom_columns) : '') . '
				FROM {db_prefix}boards AS b
					INNER JOIN {db_prefix}categories AS c ON (b.id_cat = c.id_cat)
					LEFT JOIN {db_prefix}messages AS m ON (b.id_last_msg = m.id_msg)' . ($user_info['is_guest'] ? '' : '
					LEFT JOIN {db_prefix}log_boards AS lb ON (b.id_board = lb.id_board AND lb.id_member = {int:current_member})') . (!empty($modSettings['lp_show_images_in_articles']) ? '
					LEFT JOIN {db_prefix}attachments AS a ON (b.id_last_msg = a.id_msg AND a.id_thumb <> 0 AND a.width > 0 AND a.height > 0)' : '') . (!empty($custom_tables) ? '
					' . implode("\n\t\t\t\t\t", $custom_tables) : '') . '
				WHERE b.id_board IN ({array_int:selected_boards})
					AND {query_see_board}' . (!empty($custom_wheres) ? '
					' . implode("\n\t\t\t\t\t", $custom_wheres) : '') . '
				ORDER BY ' . (!empty($modSettings['lp_frontpage_order_by_num_replies']) ? 'IF (b.num_posts > 0, 0, 1), b.num_posts DESC, ' : '') . $custom_sorting[$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
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

				$boards[$row['id_board']]['msg_link'] = $boards[$row['id_board']]['link'];

				if (empty($boards[$row['id_board']]['is_redirect']))
					$boards[$row['id_board']]['msg_link'] = $scripturl . '?msg=' . $row['id_msg'];

				Subs::runAddons('frontBoardsOutput', array(&$boards, $row));
			}

			$smcFunc['db_free_result']($request);
			$context['lp_num_queries']++;

			Helpers::cache()->put('articles_u' . $user_info['id'] . '_' . $start . '_' . $limit, $boards, LP_CACHE_TIME);
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

		if (($num_boards = Helpers::cache()->get('articles_u' . $context['user']['id'] . '_total', LP_CACHE_TIME)) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(b.id_board)
				FROM {db_prefix}boards AS b
					INNER JOIN {db_prefix}categories AS c ON (b.id_cat = c.id_cat)
				WHERE b.id_board IN ({array_int:selected_boards})
					AND {query_see_board}',
				array(
					'selected_boards' => $selected_boards
				)
			);

			[$num_boards] = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
			$context['lp_num_queries']++;

			Helpers::cache()->put('articles_u' . $context['user']['id'] . '_total', (int) $num_boards, LP_CACHE_TIME);
		}

		return (int) $num_boards;
	}
}
