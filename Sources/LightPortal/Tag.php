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
							return '<a href="' . $scripturl . '?page=' . $entry['alias'] . '">' . $entry['title'] . '</a>';
						},
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.title DESC',
						'reverse' => 'p.title'
					)
				),
				'author' => array(
					'header' => array(
						'value' => $txt['author']
					),
					'data' => array(
						'function' => function ($entry) use ($scripturl)
						{
							return '<a href="' . $scripturl . '?action=profile;u=' . $entry['author_id'] . '">' . $entry['author_name'] . '</a>';
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
		global $smcFunc, $modSettings;

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.title, p.alias, p.keywords, p.permissions, p.num_views,
				GREATEST(p.created_at, p.updated_at) AS date, mem.real_name AS author_name
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)
			WHERE p.status = {int:status}' . (!empty($modSettings['lp_frontpage_mode']) ? '
				AND p.alias != {string:alias}' : '') . '
			ORDER BY ' . $sort . ', p.page_id
			LIMIT ' . $start . ', ' . $items_per_page,
			array(
				'status' => 1,
				'alias'  => '/'
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$items[] = array(
				'id'          => $row['page_id'],
				'title'       => $row['title'],
				'alias'       => $row['alias'],
				'keywords'    => explode(', ', $row['keywords']),
				'num_views'   => $row['num_views'],
				'author_id'   => $row['author_id'],
				'author_name' => $row['author_name'],
				'created_at'  => Helpers::getFriendlyTime($row['date']),
				'can_show'    => Helpers::canShowItem($row['permissions'])
			);
		}

		$smcFunc['db_free_result']($request);

		$keyword = $smcFunc['htmlspecialchars']($_GET['key'], ENT_QUOTES);
		$items = array_filter($items, function ($page) use ($keyword) {
			return $page['can_show'] && in_array($keyword, $page['keywords']);
		});

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
		global $smcFunc, $modSettings;

		$request = $smcFunc['db_query']('', '
			SELECT keywords, permissions
			FROM {db_prefix}lp_pages
			WHERE status = {int:status}' . (!empty($modSettings['lp_frontpage_mode']) ? '
				AND alias != {string:alias}' : ''),
			array(
				'status' => 1,
				'alias'  => '/'
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$items[] = array(
				'keywords' => explode(', ', $row['keywords']),
				'can_show' => Helpers::canShowItem($row['permissions'])
			);
		}

		$smcFunc['db_free_result']($request);

		$keyword = $smcFunc['htmlspecialchars']($_GET['key'], ENT_QUOTES);
		$items = array_filter($items, function ($page) use ($keyword) {
			return $page['can_show'] && in_array($keyword, $page['keywords']);
		});

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

		$context['lp_tags'] = self::getAllTags();
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
	public static function getAllTags()
	{
		global $smcFunc, $modSettings;

		$request = $smcFunc['db_query']('', '
			SELECT keywords, permissions
			FROM {db_prefix}lp_pages
			WHERE status = {int:status}' . (!empty($modSettings['lp_frontpage_mode']) ? '
				AND alias != {string:alias}' : ''),
			array(
				'status' => 1,
				'alias'  => '/'
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$keywords = explode(', ', $row['keywords']);
			$can_show = Helpers::canShowItem($row['permissions']);

			if (!empty($keywords) && $can_show) {
				foreach ($keywords as $key) {
					if (empty($key))
						continue;

					if (!isset($items[$key]))
						$i = 1;
					else
						$i++;

					$items[$key] = array(
						'keyword'   => $key,
						'frequency' => $i
					);
				}
			}
		}

		$smcFunc['db_free_result']($request);

		asort($items);

		return $items;
	}
}
