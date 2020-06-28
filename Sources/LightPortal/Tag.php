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
		global $smcFunc, $context, $txt, $scripturl, $modSettings, $sourcedir;

		if (empty($_GET['key']))
			self::showAll();

		$context['lp_keyword']     = $smcFunc['htmlspecialchars'](trim($_GET['key']), ENT_QUOTES);
		$context['page_title']     = sprintf($txt['lp_all_tags_by_key'], $context['lp_keyword']);
		$context['canonical_url']  = $scripturl . '?action=portal;sa=tags;key=' . urlencode($context['lp_keyword']);
		$context['robot_no_index'] = true;

		$context['linktree'][] = array(
			'name' => $txt['lp_all_page_tags'],
			'url'  => $scripturl . '?action=portal;sa=tags'
		);

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		$listOptions = array(
			'id' => 'tags',
			'items_per_page' => $modSettings['defaultMaxListItems'] ?: 50,
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
						'db'    => 'date',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.created_at DESC, p.updated_at DESC',
						'reverse' => 'p.created_at, p.updated_at'
					)
				),
				'title' => array(
					'header' => array(
						'value' => $txt['lp_title']
					),
					'data' => array(
						'function' => function ($entry) use ($scripturl)
						{
							return '<a href="' . $scripturl . (Helpers::isFrontpage($entry['alias']) ? '' : ('?page=' . $entry['alias'])) . '">' . Helpers::getPublicTitle($entry) . '</a>';
						},
						'class' => 'centertext'
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
		$context['default_list'] = 'tags';

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
		global $smcFunc, $txt, $context;

		$titles = Helpers::getFromCache('all_titles', 'getAllTitles', '\Bugo\LightPortal\Subs', LP_CACHE_TIME, 'page');

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.alias, p.num_views, GREATEST(p.created_at, p.updated_at) AS date,
				t.value, mem.id_member AS author_id, COALESCE(mem.real_name, {string:guest}) AS author_name
			FROM {db_prefix}lp_tags AS t
				LEFT JOIN {db_prefix}lp_pages AS p ON (p.page_id = t.page_id)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)
			WHERE t.value = {string:key}
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array(
				'guest'        => $txt['guest_title'],
				'key'          => $context['lp_keyword'],
				'status'       => Page::STATUS_ACTIVE,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions(),
				'sort'         => $sort,
				'start'        => $start,
				'limit'        => $items_per_page
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$items[$row['page_id']] = array(
				'id'          => $row['page_id'],
				'alias'       => $row['alias'],
				'num_views'   => $row['num_views'],
				'author_id'   => $row['author_id'],
				'author_name' => $row['author_name'],
				'date'        => Helpers::getFriendlyTime($row['date']),
				'title'       => $titles[$row['page_id']] ?? []
			);
		}

		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

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
		global $smcFunc, $context;

		$request = $smcFunc['db_query']('', '
			SELECT t.page_id, t.value
			FROM {db_prefix}lp_tags AS t
				LEFT JOIN {db_prefix}lp_pages AS p ON (p.page_id = t.page_id)
			WHERE t.value = {string:key}
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})',
			array(
				'key'          => $context['lp_keyword'],
				'status'       => Page::STATUS_ACTIVE,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions()
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$items[$row['page_id']] = $row['value'];

		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

		return sizeof($items);
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
		global $context, $txt, $scripturl, $modSettings, $sourcedir;

		$context['page_title']     = $txt['lp_all_page_tags'];
		$context['canonical_url']  = $scripturl . '?action=portal;sa=tags';
		$context['robot_no_index'] = true;

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		$listOptions = array(
			'id' => 'tags',
			'items_per_page' => $modSettings['defaultMaxListItems'] ?: 50,
			'title' => $context['page_title'],
			'no_items_label' => $txt['lp_no_tags'],
			'base_href' => $context['canonical_url'],
			'default_sort_col' => 'value',
			'get_items' => array(
				'function' => __CLASS__ . '::getAll'
			),
			'get_count' => array(
				'function' => __CLASS__ . '::getTotalQuantity'
			),
			'columns' => array(
				'value' => array(
					'header' => array(
						'value' => $txt['lp_keyword_column']
					),
					'data' => array(
						'function' => function ($entry)
						{
							return '<a href="' . $entry['link'] . '">' . $entry['value'] . '</a>';
						},
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 't.value DESC',
						'reverse' => 't.value'
					)
				),
				'frequency' => array(
					'header' => array(
						'value' => $txt['lp_frequency_column']
					),
					'data' => array(
						'db'    => 'frequency',
						'class' => 'centertext'
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
		$context['default_list'] = 'tags';

		obExit();
	}

	/**
	 * Get the list of all tags
	 *
	 * Получаем список всех тегов
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 * @return array
	 */
	public static function getAll(int $start, int $items_per_page, string $sort)
	{
		global $smcFunc, $scripturl, $context;

		$request = $smcFunc['db_query']('', '
			SELECT t.value
			FROM {db_prefix}lp_tags AS t
				LEFT JOIN {db_prefix}lp_pages AS p ON (p.page_id = t.page_id)
			WHERE t.value IS NOT NULL
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort}' . ($items_per_page ? '
			LIMIT {int:start}, {int:limit}' : ''),
			array(
				'status'       => Page::STATUS_ACTIVE,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions(),
				'sort'         => $sort,
				'start'        => $start,
				'limit'        => $items_per_page
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (!isset($items[$row['value']]))
				$i = 1;
			else
				$i++;

			$items[$row['value']] = array(
				'value'     => $row['value'],
				'link'      => $scripturl . '?action=portal;sa=tags;key=' . urlencode(trim($row['value'])),
				'frequency' => $i
			);
		}

		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

		uasort($items, function ($a, $b) {
			return $a['frequency'] < $b['frequency'];
		});

		return $items;
	}

	/**
	 * Get the total number of pages with tags
	 *
	 * Подсчитываем общее количество страниц тегами
	 *
	 * @return int
	 */
	public static function getTotalQuantity()
	{
		global $smcFunc, $context;

		$request = $smcFunc['db_query']('', '
			SELECT t.page_id, t.value
			FROM {db_prefix}lp_tags AS t
				LEFT JOIN {db_prefix}lp_pages AS p ON (p.page_id = t.page_id)
			WHERE t.value IS NOT NULL
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})',
			array(
				'status'       => Page::STATUS_ACTIVE,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions()
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$items[$row['value']] = $row['page_id'];

		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

		return sizeof($items);
	}
}
