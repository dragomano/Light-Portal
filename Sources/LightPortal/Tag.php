<?php

namespace Bugo\LightPortal;

/**
 * Tag.php
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

class Tag
{
	/**
	 * Display all portal pages by specified tag
	 *
	 * Отображение всех страниц портала с указанным тегом
	 *
	 * @return void
	 */
	public static function show()
	{
		global $smcFunc, $context, $txt, $scripturl, $sourcedir;

		loadTemplate('LightPortal/ViewTag');

		if (empty($_GET['key']))
			self::showAll();

		$keyword = $smcFunc['htmlspecialchars']($_GET['key'], ENT_QUOTES);

		$context['page_title']     = sprintf($txt['lp_all_tags_by_key'], $keyword);
		$context['canonical_url']  = $scripturl . '?action=portal;sa=tags;key=' . urlencode($keyword);
		$context['robot_no_index'] = true;

		$context['linktree'][] = array(
			'name' => $txt['lp_all_page_tags'],
			'url'  => $scripturl . '?action=portal;sa=tags'
		);

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		$listOptions = array(
			'id' => 'pages',
			'items_per_page' => 50,
			'title' => $context['page_title'],
			'no_items_label' => $txt['lp_no_selected_tag'],
			'base_href' => $context['canonical_url'],
			'default_sort_col' => 'date',
			'get_items' => array(
				'function' => __CLASS__ . '::getAllPagesWithSelectedTag'
			),
			'get_count' => array(
				'function' => __CLASS__ . '::getTotalQuantityPagesWithSelectedTag'
			),
			'columns' => array(
				'date' => array(
					'header' => array(
						'value' => $txt['date']
					),
					'data' => array(
						'db'    => 'created_at',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.created_at DESC',
						'reverse' => 'p.created_at'
					)
				),
				'title' => array(
					'header' => array(
						'value' => $txt['lp_title']
					),
					'data' => array(
						'function' => function ($entry) use ($scripturl)
						{
							return '<a href="' . (Helpers::isFrontpage($entry['id']) ? $scripturl : '?page=' . $entry['alias']) . '">' . Helpers::getPublicTitle($entry) . '</a>';
						},
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'pt.title DESC',
						'reverse' => 'pt.title'
					)
				),
				'author' => array(
					'header' => array(
						'value' => $txt['author']
					),
					'data' => array(
						'function' => function ($entry) use ($scripturl)
						{
							return empty($entry['author_id']) ? $entry['author_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $entry['author_id'] . '">' . $entry['author_name'] . '</a>';
						},
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'author_name DESC',
						'reverse' => 'author_name'
					)
				),
				'num_views' => array(
					'header' => array(
						'value' => $txt['views']
					),
					'data' => array(
						'db'    => 'num_views',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.num_views DESC',
						'reverse' => 'p.num_views'
					)
				)
			),
			'form' => array(
				'href' => $context['canonical_url']
			)
		);

		require_once($sourcedir . '/Subs-List.php');
		createList($listOptions);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'pages';

		obExit();
	}

	/**
	 * Get the list of pages with selected tag
	 *
	 * Получаем список страниц с указанным тегом
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 * @return array
	 */
	public static function getAllPagesWithSelectedTag(int $start, int $items_per_page, string $sort)
	{
		global $smcFunc, $txt;

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.alias, p.permissions, p.num_views,
				GREATEST(p.created_at, p.updated_at) AS date, t.value, pt.lang, pt.title, mem.id_member AS author_id, COALESCE(mem.real_name, {string:guest}) AS author_name
			FROM {db_prefix}lp_tags AS t
				LEFT JOIN {db_prefix}lp_pages AS p ON (p.page_id = t.page_id)
				LEFT JOIN {db_prefix}lp_titles AS pt ON (pt.item_id = t.page_id AND pt.type = {string:type})
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)
			WHERE t.value = {string:key}
				AND p.status = {int:status}
			ORDER BY ' . $sort . ', p.page_id
			LIMIT ' . $start . ', ' . $items_per_page,
			array(
				'guest'  => $txt['guest_title'],
				'type'   => 'page',
				'key'    => $smcFunc['htmlspecialchars']($_GET['key'], ENT_QUOTES),
				'status' => Page::STATUS_ACTIVE
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (Helpers::canShowItem($row['permissions'])) {
				$items[$row['page_id']] = array(
					'id'          => $row['page_id'],
					'alias'       => $row['alias'],
					'num_views'   => $row['num_views'],
					'author_id'   => $row['author_id'],
					'author_name' => $row['author_name'],
					'created_at'  => Helpers::getFriendlyTime($row['date'])
				);

				if (!empty($row['lang']))
					$items[$row['page_id']]['title'][$row['lang']] = $row['title'];
			}
		}

		$smcFunc['db_free_result']($request);

		return $items;
	}

	/**
	 * Get the total number of pages with selected tag
	 *
	 * Подсчитываем общее количество страниц с указанным тегом
	 *
	 * @return int
	 */
	public static function getTotalQuantityPagesWithSelectedTag()
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT t.page_id, t.value, p.permissions
			FROM {db_prefix}lp_tags AS t
				LEFT JOIN {db_prefix}lp_pages AS p ON (p.page_id = t.page_id)
			WHERE t.value = {string:key}
				AND p.status = {int:status}',
			array(
				'key'    => $smcFunc['htmlspecialchars']($_GET['key'], ENT_QUOTES),
				'status' => Page::STATUS_ACTIVE
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (Helpers::canShowItem($row['permissions']))
				$items[$row['page_id']] = $row['value'];
		}

		$smcFunc['db_free_result']($request);

		return count($items);
	}

	/**
	 * Display all tags
	 *
	 * Отображение всех тегов сразу
	 *
	 * @return void
	 */
	public static function showAll()
	{
		global $context, $txt, $scripturl;

		$context['page_title']     = $txt['lp_all_page_tags'];
		$context['canonical_url']  = $scripturl . '?action=portal;sa=tags';
		$context['robot_no_index'] = true;

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		$context['lp_tags'] = self::getAll();
		$context['sub_template'] = 'show_tags';

		obExit();
	}

	/**
	 * Get the list of all tags
	 *
	 * Получаем список всех тегов
	 *
	 * @return array
	 */
	public static function getAll()
	{
		global $smcFunc, $scripturl;

		$request = $smcFunc['db_query']('', '
			SELECT t.value, p.permissions
			FROM {db_prefix}lp_tags AS t
				LEFT JOIN {db_prefix}lp_pages AS p ON (p.page_id = t.page_id)
			WHERE t.value IS NOT NULL
				AND p.status = {int:status}
			ORDER BY t.value',
			array(
				'status' => Page::STATUS_ACTIVE
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (Helpers::canShowItem($row['permissions'])) {
				if (!isset($items[$row['value']]))
					$i = 1;
				else
					$i++;

				$items[$row['value']] = array(
					'keyword'   => $row['value'],
					'link'      => $scripturl . '?action=portal;sa=tags;key=' . urlencode($row['value']),
					'frequency' => $i
				);
			}
		}

		$smcFunc['db_free_result']($request);

		return $items;
	}
}
