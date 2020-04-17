<?php

namespace Bugo\LightPortal;

/**
 * FrontPage.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
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
	 * Placeholder image for articles
	 *
	 * @var string
	 */
	private static $placeholder_image = '<i class="far fa-image fa-7x"></i>';

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
		} elseif ($modSettings['lp_frontpage_mode'] == 3) {
			self::prepareArticles();
			$context['sub_template'] = 'show_pages_as_articles';
		} else {
			self::prepareArticles('boards');
			$context['sub_template'] = 'show_boards_as_articles';
		}

		if ($context['current_action'] !== 'portal')
			Block::show();

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
	private static function getNumColumns()
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
				$function = 'getTopicsFromSelectedBoards';
				break;
			case 'boards':
				$function = 'getSelectedBoards';
				break;
			default:
				$function = 'getActivePages';
		}

		$start    = (int) $_REQUEST['start'];
		$limit    = $modSettings['lp_num_items_per_page'] ?? 10;
		$articles = self::$function($start, $limit);

		$articles = array_map(function ($article) use ($modSettings, $context) {
			if (isset($article['time']))
				$article['time'] = Helpers::getFriendlyTime($article['time']);
			if (isset($article['created_at']))
				$article['created_at'] = Helpers::getFriendlyTime($article['created_at']);
			if (isset($article['last_updated']))
				$article['last_updated'] = Helpers::getFriendlyTime($article['last_updated']);
			if (isset($article['title']))
				$article['title'] = Helpers::getPublicTitle($article);
			if (!empty($modSettings['lp_image_placeholder']))
				$image = '<img src="' . $modSettings['lp_image_placeholder'] . '" alt="' . $article['title'] . '">';
			if (empty($article['image']) && !empty($modSettings['lp_show_images_in_articles']))
				$article['image_placeholder'] = Subs::runAddons('getFrontpagePlaceholderImage') ?: $image ?? self::$placeholder_image;

			return $article;
		}, $articles);

		$getTotalFunction = $function . 'Quantity';

		$context['page_index'] = constructPageIndex($scripturl . '?action=portal', $_REQUEST['start'], self::$getTotalFunction(), $limit);
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
		global $modSettings, $user_info, $smcFunc, $scripturl;

		$selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		if (empty($selected_boards))
			return [];

		if (($topics = cache_get_data('light_portal_fronttopics_' . $start . '_' . $limit, 3600)) == null) {
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

			$request = Helpers::dbSelect('
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
					censorText($row['subject']);
					censorText($row['body']);

					$row['subject'] = Helpers::cleanBbcode($row['subject']);

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
						'poster_id'   => $row['id_member'],
						'poster_link' => $scripturl . '?action=profile;u=' . $row['id_member'],
						'poster_name' => $row['poster_name'],
						'time'        => $row['poster_time'],
						'subject'     => self::getShortenSubject($row['subject']),
						'preview'     => $row['body'],
						'link'        => $scripturl . '?topic=' . $row['id_topic'] . ($row['new_from'] > $row['id_msg_modified'] ? '.0' : '.new;topicseen#new'),
						'board'       => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>',
						'is_sticky'   => !empty($row['is_sticky']),
						'is_new'      => $row['new_from'] <= $row['id_msg_modified'],
						'num_views'   => $row['num_views'],
						'num_replies' => $row['num_replies'],
						'css_class'   => $colorClass,
						'image'       => $image
					);
				}

				Subs::runAddons('frontTopicsOutput', array(&$topics, $row));
			}

			$smcFunc['db_free_result']($request);

			if (!empty($messages) && !empty($modSettings['lp_show_images_in_articles'])) {
				$request = Helpers::dbSelect('
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

				foreach ($attachments as $id_topic => $data)
					$topics[$id_topic]['image'] = $data[0];
			}

			cache_put_data('light_portal_fronttopics_' . $start . '_' . $limit, $topics, 3600);
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
	public static function getTopicsFromSelectedBoardsQuantity()
	{
		global $modSettings, $smcFunc;

		$selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		if (empty($selected_boards))
			return 0;

		if (($num_topics = cache_get_data('light_portal_fronttopics_total', 3600)) == null) {
			$request = Helpers::dbSelect('
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

			cache_put_data('light_portal_fronttopics_total', $num_topics, 3600);
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
		global $smcFunc, $modSettings, $scripturl, $user_info;

		if (($pages = cache_get_data('light_portal_frontpages_' . $start . '_' . $limit, 3600)) == null) {
			$titles = Helpers::useCache('all_titles', 'getAllTitles', '\Bugo\LightPortal\Subs', 3600, 'page');

			$custom_columns    = [];
			$custom_tables     = [];
			$custom_wheres     = [];
			$custom_parameters = [
				'type'   => 'page',
				'status' => Page::STATUS_ACTIVE,
				'start'  => $start,
				'limit'  => $limit
			];

			Subs::runAddons('frontPages', array(&$custom_columns, &$custom_tables, &$custom_wheres, &$custom_parameters));

			$request = Helpers::dbSelect('
				SELECT
					p.page_id, p.author_id, p.alias, p.content, p.description, p.type, p.permissions, p.status, p.num_views, p.num_comments,
					GREATEST(p.created_at, p.updated_at) AS date, mem.real_name AS author_name' . (!empty($custom_columns) ? ',
					' . implode(', ', $custom_columns) : '') . '
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)' . (!empty($custom_tables) ? '
					' . implode("\n\t\t\t\t\t", $custom_tables) : '') . '
				WHERE p.status = {int:status}' . (!empty($custom_wheres) ? '
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

				if (!isset($pages[$row['page_id']]))
					$pages[$row['page_id']] = array(
						'id'           => $row['page_id'],
						'author_id'    => $row['author_id'],
						'author_link'  => $scripturl . '?action=profile;u=' . $row['author_id'],
						'author_name'  => $row['author_name'],
						'alias'        => $row['alias'],
						'description'  => $row['description'],
						'type'         => $row['type'],
						'num_views'    => $row['num_views'],
						'num_comments' => $row['num_comments'],
						'created_at'   => $row['date'],
						'is_new'       => $user_info['last_login'] < $row['date'] && $row['author_id'] != $user_info['id'],
						'link'         => $scripturl . '?page=' . $row['alias'],
						'image'        => $image,
						'can_show'     => Helpers::canShowItem($row['permissions']),
						'can_edit'     => $user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $row['author_id'] == $user_info['id'])
					);

				$pages[$row['page_id']]['title'] = self::getShortenSubject($titles[$row['page_id']]);

				Subs::runAddons('frontPagesOutput', array(&$pages, $row));
			}

			$smcFunc['db_free_result']($request);

			cache_put_data('light_portal_frontpages_' . $start . '_' . $limit, $pages, 3600);
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
	public static function getActivePagesQuantity()
	{
		global $smcFunc;

		if (($num_pages = cache_get_data('light_portal_frontpages_total', 3600)) == null) {
			$request = Helpers::dbSelect('
				SELECT COUNT(page_id)
				FROM {db_prefix}lp_pages
				WHERE status = {int:status}',
				array(
					'status' => Page::STATUS_ACTIVE
				)
			);

			list ($num_pages) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			cache_put_data('light_portal_frontpages_total', $num_pages, 3600);
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

		if (($boards = cache_get_data('light_portal_frontboards_' . $start . '_' . $limit, 3600)) == null) {
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

			$request = Helpers::dbSelect('
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
					'name'        => self::getShortenSubject($board_name),
					'description' => $description,
					'category'    => $cat_name,
					'link'        => $row['is_redirect'] ? $row['redirect'] : $scripturl . '?board=' . $row['id_board'] . '.0',
					'is_redirect' => $row['is_redirect'],
					'is_updated'  => empty($row['is_read']),
					'num_posts'   => $row['num_posts'],
					'image'       => $image
				);

				if (!empty($row['last_updated'])) {
					$boards[$row['id_board']]['last_post'] = $scripturl . '?topic=' . $row['id_topic'] . '.msg' . ($user_info['is_guest'] ? $row['id_msg'] : $row['new_from']) . (empty($row['is_read']) ? ';boardseen' : '') . '#new';
					$boards[$row['id_board']]['last_updated'] = $row['last_updated'];
				}

				Subs::runAddons('frontBoardsOutput', array(&$boards, $row));
			}

			$smcFunc['db_free_result']($request);

			cache_put_data('light_portal_frontboards_' . $start . '_' . $limit, $boards, 3600);
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
	public static function getSelectedBoardsQuantity()
	{
		global $modSettings, $smcFunc;

		$selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		if (empty($selected_boards))
			return 0;

		if (($num_boards = cache_get_data('light_portal_frontboards_total', 3600)) == null) {
			$request = Helpers::dbSelect('
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

			cache_put_data('light_portal_frontboards_total', $num_boards, 3600);
		}

		return $num_boards;
	}

	/**
	 * Get shorten title|titles
	 *
	 * Получаем короткий заголовок или заголовки
	 *
	 * @param array|string $object
	 * @return array|string
	 */
	public static function getShortenSubject($object)
	{
		global $modSettings;

		if (is_array($object))
			return array_map('self::getShortenSubject', $object);

		return !empty($modSettings['lp_subject_size']) ? shorten_subject($object, $modSettings['lp_subject_size']) : $object;
	}
}
