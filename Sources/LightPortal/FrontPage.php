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
	 * Default image for articles
	 *
	 * @var string
	 */
	private static $default_image = 'far fa-image fa-7x';

	/**
	 * Show articles on the portal frontpage
	 *
	 * Выводим статьи на главной странице портала
	 *
	 * @return void
	 */
	public static function show()
	{
		global $modSettings, $context, $txt;

		isAllowedTo('light_portal_view');

		Block::show();

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

		$context['lp_frontpage_layout'] = self::getNumColumns();

		loadTemplate('LightPortal/ViewFrontPage');

		$context['page_title'] = !empty($modSettings['lp_frontpage_title']) ? $modSettings['lp_frontpage_title'] : ($context['forum_name'] . ' - ' . $txt['lp_portal']);
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

		$num_columns = 12;

		if (!empty($modSettings['lp_frontpage_layout'])) {
			switch ($modSettings['lp_frontpage_layout']) {
				case '1':
					$num_columns = 12 / 2;
					break;
				case '2':
					$num_columns = 12 / 3;
					break;
				case '3':
					$num_columns = 12 / 4;
					break;
				default:
					$num_columns = 12 / 6;
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
		global $user_info, $modSettings, $context, $scripturl;

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

		$articles = Helpers::useCache('frontpage_' . $source . '_u' . $user_info['id'], $function, __CLASS__);

		$articles = array_map(function ($article) {
			if (isset($article['time']))
				$article['time'] = Helpers::getFriendlyTime($article['time']);
			if (isset($article['created_at']))
				$article['created_at'] = Helpers::getFriendlyTime($article['created_at']);
			if (isset($article['last_updated']))
				$article['last_updated'] = Helpers::getFriendlyTime($article['last_updated']);
			if (isset($article['title']))
				$article['title'] = Helpers::getLocalizedTitle($article);
			if (empty($article['image']))
				$article['image_placeholder'] = self::$default_image;

			return $article;
		}, $articles);

		$total_items           = count($articles);
		$limit                 = $modSettings['lp_num_items_per_page'] ?? 10;
		$context['page_index'] = constructPageIndex($scripturl . '?action=portal', $_REQUEST['start'], $total_items, $limit);
		$context['start']      = &$_REQUEST['start'];
		$start                 = (int) $_REQUEST['start'];

		$context['lp_frontpage_articles'] = array_slice($articles, $start, $limit);

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
	 * @return array
	 */
	public static function getTopicsFromSelectedBoards()
	{
		global $modSettings, $user_info, $smcFunc, $scripturl;

		$selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		if (empty($selected_boards))
			return [];

		$custom_columns    = [];
		$custom_tables     = [];
		$custom_wheres     = [];
		$custom_parameters = [
			'current_member'    => $user_info['id'],
			'is_approved'       => 1,
			'id_poll'           => 0,
			'id_redirect_topic' => 0,
			'selected_boards'   => $selected_boards
		];

		Subs::runAddons('frontpageTopics', array(&$custom_columns, &$custom_tables, &$custom_wheres, &$custom_parameters));

		$request = $smcFunc['db_query']('', '
			SELECT
				t.id_topic, t.id_board, t.num_views, t.num_replies, t.is_sticky, t.id_first_msg, t.id_member_started, mf.subject, mf.body, mf.smileys_enabled, COALESCE(mem.real_name, mf.poster_name) AS poster_name, mf.poster_time, mf.id_member, ml.id_msg, b.name, ' . ($user_info['is_guest'] ? '0' : 'COALESCE(lt.id_msg, lmr.id_msg, -1) + 1') . ' AS new_from, ml.id_msg_modified' . (!empty($modSettings['lp_show_images_in_articles']) ? ', COALESCE(a.id_attach, 0) AS attach_id' : '') . (!empty($custom_columns) ? ',
				' . implode(', ', $custom_columns) : '') . '
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
				INNER JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = mf.id_member)
				LEFT JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)' . ($user_info['is_guest'] ? '' : '
				LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
				LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = t.id_board AND lmr.id_member = {int:current_member})') . (!empty($modSettings['lp_show_images_in_articles']) ? '
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_msg = t.id_first_msg AND a.id_thumb <> 0 AND a.width > 0 AND a.height > 0)' : '') . (!empty($custom_tables) ? '
				' . implode("\n\t\t\t\t\t", $custom_tables) : '') . '
			WHERE t.approved = {int:is_approved}
				AND t.id_poll = {int:id_poll}
				AND t.id_redirect_topic = {int:id_redirect_topic}
				AND t.id_board IN ({array_int:selected_boards})
				AND {query_wanna_see_board}' . (!empty($custom_wheres) ? '
				' . implode("\n\t\t\t\t\t", $custom_wheres) : '') . '
			ORDER BY t.id_last_msg DESC',
			$custom_parameters
		);

		$subject_size = !empty($modSettings['lp_subject_size']) ? (int) $modSettings['lp_subject_size'] : 0;
		$teaser_size  = !empty($modSettings['lp_teaser_size']) ? (int) $modSettings['lp_teaser_size'] : 0;

		$topics = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['subject']);
			censorText($row['body']);

			$row['subject'] = Helpers::cleanBbcode($row['subject']);

			$image = null;
			if (!empty($modSettings['lp_show_images_in_articles'])) {
				$body = parse_bbc($row['body'], false);
				$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $body, $value);
				$image = $first_post_image ? array_pop($value) : (!empty($row['attach_id']) ? $scripturl . '?action=dlattach;topic=' . $row['id_topic'] . ';attach=' . $row['attach_id'] . ';image' : null);
			}

			$row['body'] = preg_replace('~\[spoiler\].*?\[\/spoiler\]~Usi', '', $row['body']);
			$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['id_first_msg']), array('<br>' => ' ')));
			if (!empty($teaser_size) && $smcFunc['strlen']($row['body']) > $teaser_size)
				$row['body'] = shorten_subject($row['body'], $teaser_size - 3);

			$colorClass = '';
			if ($row['is_sticky'])
				$colorClass .= ' alternative2';

			$topics[$row['id_topic']] = array(
				'id'          => $row['id_topic'],
				'poster_id'   => $row['id_member'],
				'poster_link' => $scripturl . '?action=profile;u=' . $row['id_member'],
				'poster_name' => $row['poster_name'],
				'time'        => $row['poster_time'],
				'subject'     => !empty($subject_size) ? shorten_subject($row['subject'], $subject_size) : $row['subject'],
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

			Subs::runAddons('frontpageTopicsOutput', array(&$topics, $row));
		}

		$smcFunc['db_free_result']($request);

		return $topics;
	}

	/**
	 * Get active pages
	 *
	 * Получаем активные страницы
	 *
	 * @return array
	 */
	public static function getActivePages()
	{
		global $smcFunc, $modSettings, $scripturl, $user_info;

		$custom_columns    = [];
		$custom_tables     = [];
		$custom_wheres     = [];
		$custom_parameters = [
			'type'   => 'page',
			'status' => Page::STATUS_ACTIVE
		];

		Subs::runAddons('frontpagePages', array(&$custom_columns, &$custom_tables, &$custom_wheres, &$custom_parameters));

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.alias, p.content, p.description, p.type, p.permissions, p.status, p.num_views,
				GREATEST(created_at, updated_at) AS date, pt.lang, pt.title, mem.real_name AS author_name' . (!empty($custom_columns) ? ',
				' . implode(', ', $custom_columns) : '') . '
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS pt ON (pt.item_id = p.page_id AND pt.type = {string:type})
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)' . (!empty($custom_tables) ? '
				' . implode("\n\t\t\t\t\t", $custom_tables) : '') . '
			WHERE p.status = {int:status}' . (!empty($custom_wheres) ? '
				' . implode("\n\t\t\t\t\t", $custom_wheres) : '') . '
			ORDER BY date DESC',
			$custom_parameters
		);

		$subject_size = !empty($modSettings['lp_subject_size']) ? (int) $modSettings['lp_subject_size'] : 0;
		$teaser_size  = !empty($modSettings['lp_teaser_size']) ? (int) $modSettings['lp_teaser_size'] : 0;

		$pages = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			Subs::parseContent($row['content'], $row['type']);

			$image = null;
			if (!empty($modSettings['lp_show_images_in_articles'])) {
				$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $row['content'], $value);
				$image = $first_post_image ? array_pop($value) : null;
			}

			if (!empty($teaser_size) && !empty($row['description']))
				$row['description'] = shorten_subject($row['description'], $teaser_size - 3);

			$pages[$row['page_id']] = array(
				'id'          => $row['page_id'],
				'author_id'   => $row['author_id'],
				'author_link' => $scripturl . '?action=profile;u=' . $row['author_id'],
				'author_name' => $row['author_name'],
				'alias'       => $row['alias'],
				'description' => $row['description'],
				'type'        => $row['type'],
				'num_views'   => $row['num_views'],
				'created_at'  => $row['date'],
				'is_new'      => $user_info['last_login'] < $row['date'] && $row['author_id'] != $user_info['id'],
				'link'        => $scripturl . '?page=' . $row['alias'],
				'image'       => $image,
				'can_show'    => Helpers::canShowItem($row['permissions']),
				'can_edit'    => $user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $row['author_id'] == $user_info['id'])
			);

			if (!empty($row['lang']))
				$pages[$row['page_id']]['title'][$row['lang']] = !empty($subject_size) ? shorten_subject($row['title'], $subject_size) : $row['title'];

			Subs::runAddons('frontpagePagesOutput', array(&$pages, $row));
		}

		$smcFunc['db_free_result']($request);

		return $pages;
	}

	/**
	 * Get selected boards
	 *
	 * Получаем выбранные разделы
	 *
	 * @return array
	 */
	public static function getSelectedBoards()
	{
		global $modSettings, $user_info, $smcFunc, $context, $scripturl;

		$selected_boards = !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : [];

		if (empty($selected_boards))
			return [];

		$custom_columns    = [];
		$custom_tables     = [];
		$custom_wheres     = [];
		$custom_parameters = [
			'blank_string'    => '',
			'current_member'  => $user_info['id'],
			'selected_boards' => $selected_boards
		];

		Subs::runAddons('frontpageBoards', array(&$custom_columns, &$custom_tables, &$custom_wheres, &$custom_parameters));

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
				LEFT JOIN {db_prefix}topics AS t ON (t.id_topic = m.id_topic)
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_msg = t.id_first_msg AND a.id_thumb <> 0 AND a.width > 0 AND a.height > 0)' : '') . (!empty($custom_tables) ? '
				' . implode("\n\t\t\t\t\t", $custom_tables) : '') . '
			WHERE b.id_board IN ({array_int:selected_boards})
				AND {query_see_board}' . (!empty($custom_wheres) ? '
				' . implode("\n\t\t\t\t\t", $custom_wheres) : '') . '
			ORDER BY b.id_last_msg DESC',
			$custom_parameters
		);

		$subject_size = !empty($modSettings['lp_subject_size']) ? (int) $modSettings['lp_subject_size'] : 0;
		$teaser_size  = !empty($modSettings['lp_teaser_size']) ? (int) $modSettings['lp_teaser_size'] : 0;

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
				'name'        => !empty($subject_size) ? shorten_subject($board_name, $subject_size) : $board_name,
				'description' => !empty($teaser_size) ? shorten_subject($description, $teaser_size) : $description,
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

			Subs::runAddons('frontpageBoardsOutput', array(&$boards, $row));
		}

		$smcFunc['db_free_result']($request);

		return $boards;
	}
}
