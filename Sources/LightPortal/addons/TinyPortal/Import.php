<?php

namespace Bugo\LightPortal\Addons\TinyPortal;

use Bugo\LightPortal\Impex\AbstractImport;
use Bugo\LightPortal\Helpers;

/**
 * Import.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Import extends AbstractImport
{
	/**
	 * TinyPortal pages import
	 *
	 * Импорт страниц TinyPortal
	 *
	 * @return void
	 */
	public static function main()
	{
		global $context, $txt, $scripturl, $sourcedir;

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_tiny_portal_addon_label_name'];
		$context['page_area_title'] = $txt['lp_pages_import'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_pages;sa=import_from_tp';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_tiny_portal_addon_description']
		);

		self::run();

		$listOptions = array(
			'id' => 'pages',
			'items_per_page' => 50,
			'title' => $txt['lp_pages_import'],
			'no_items_label' => $txt['lp_no_items'],
			'base_href' => $context['canonical_url'],
			'default_sort_col' => 'id',
			'get_items' => array(
				'function' => __CLASS__ . '::getAll'
			),
			'get_count' => array(
				'function' => __CLASS__ . '::getTotalQuantity'
			),
			'columns' => array(
				'id' => array(
					'header' => array(
						'value' => '#',
						'style' => 'width: 5%'
					),
					'data' => array(
						'db'    => 'id',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'id',
						'reverse' => 'id DESC'
					)
				),
				'alias' => array(
					'header' => array(
						'value' => $txt['lp_page_alias']
					),
					'data' => array(
						'db'    => 'alias',
						'class' => 'centertext word_break'
					),
					'sort' => array(
						'default' => 'shortname DESC',
						'reverse' => 'shortname'
					)
				),
				'title' => array(
					'header' => array(
						'value' => $txt['lp_title']
					),
					'data' => array(
						'db'    => 'title',
						'class' => 'word_break'
					),
					'sort' => array(
						'default' => 'subject DESC',
						'reverse' => 'subject'
					)
				),
				'actions' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" checked>'
					),
					'data' => array(
						'function' => function ($entry)
						{
							return '<input type="checkbox" value="' . $entry['id'] . '" name="pages[]" checked>';
						},
						'class' => 'centertext'
					)
				)
			),
			'form' => array(
				'href' => $context['canonical_url']
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '
						<input type="submit" name="import_selection" value="' . $txt['lp_tiny_portal_addon_button_run'] . '" class="button">
						<input type="submit" name="import_all" value="' . $txt['lp_tiny_portal_addon_button_all'] . '" class="button">',
					'class' => 'floatright'
				)
			)
		);

		require_once($sourcedir . '/Subs-List.php');
		createList($listOptions);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'pages';
	}

	/**
	 * Get the list of pages
	 *
	 * Получаем список страниц
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 * @return array
	 */
	public static function getAll(int $start = 0, int $items_per_page = 0, string $sort = 'id')
	{
		global $smcFunc, $db_prefix;

		db_extend();

		if (empty($smcFunc['db_list_tables'](false, $db_prefix . 'tp_articles')))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT id, date, subject, author_id, off, views, shortname, type
			FROM {db_prefix}tp_articles
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array(
				'sort'  => $sort,
				'start' => $start,
				'limit' => $items_per_page
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$items[$row['id']] = array(
				'id'         => $row['id'],
				'alias'      => $row['shortname'],
				'type'       => $row['type'],
				'status'     => (int) empty($row['off']),
				'num_views'  => $row['views'],
				'author_id'  => $row['author_id'],
				'created_at' => Helpers::getFriendlyTime($row['date']),
				'title'      => $row['subject']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	/**
	 * Get the total number of pages
	 *
	 * Подсчитываем общее количество страниц
	 *
	 * @return int
	 */
	public static function getTotalQuantity()
	{
		global $smcFunc, $db_prefix;

		db_extend();

		if (empty($smcFunc['db_list_tables'](false, $db_prefix . 'tp_articles')))
			return 0;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}tp_articles',
			array()
		);

		[$num_pages] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return (int) $num_pages;
	}

	/**
	 * Start importing data
	 *
	 * Запускаем импорт
	 *
	 * @return void
	 */
	protected static function run()
	{
		global $db_temp_cache, $db_cache, $language, $smcFunc;

		if (Helpers::post()->isEmpty('pages') && Helpers::post()->has('import_all') === false)
			return;

		// Might take some time.
		@set_time_limit(600);

		// Don't allow the cache to get too full
		$db_temp_cache = $db_cache;
		$db_cache = [];

		$pages = !empty(Helpers::post('pages')) && Helpers::post()->has('export_all') === false ? Helpers::post('pages') : null;

		$items = self::getItems($pages);

		$comments = self::getComments($pages);

		$titles = $params = [];
		foreach ($items as $page_id => $item) {
			$items[$page_id]['num_comments'] = sizeof($comments[$page_id]);

			$titles[] = [
				'item_id' => $page_id,
				'type'    => 'page',
				'lang'    => $language,
				'title'   => $item['subject']
			];

			unset($items[$page_id]['subject']);

			if (in_array('author', $items[$page_id]['options']) || in_array('date', $items[$page_id]['options']))
				$params[] = [
					'item_id' => $page_id,
					'type'    => 'page',
					'name'    => 'show_author_and_date',
					'value'   => 1
				];

			if (in_array('commentallow', $items[$page_id]['options']))
				$params[] = [
					'item_id' => $page_id,
					'type'    => 'page',
					'name'    => 'allow_comments',
					'value'   => 1
				];

			unset($items[$page_id]['options']);
		}

		if (!empty($items)) {
			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$result = $smcFunc['db_insert']('replace',
					'{db_prefix}lp_pages',
					array(
						'page_id'      => 'int',
						'author_id'    => 'int',
						'alias'        => 'string-255',
						'description'  => 'string-255',
						'content'      => 'string-' . MAX_MSG_LENGTH,
						'type'         => 'string-4',
						'permissions'  => 'int',
						'status'       => 'int',
						'num_views'    => 'int',
						'num_comments' => 'int',
						'created_at'   => 'int',
						'updated_at'   => 'int'
					),
					$items[$i],
					array('page_id'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (!empty($titles) && !empty($result)) {
			$titles = array_chunk($titles, 100);
			$count  = sizeof($titles);

			for ($i = 0; $i < $count; $i++) {
				$result = $smcFunc['db_insert']('replace',
					'{db_prefix}lp_titles',
					array(
						'item_id' => 'int',
						'type'    => 'string',
						'lang'    => 'string',
						'title'   => 'string'
					),
					$titles[$i],
					array('item_id', 'type', 'lang'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (!empty($params) && !empty($result)) {
			$params = array_chunk($params, 100);
			$count  = sizeof($params);

			for ($i = 0; $i < $count; $i++) {
				$result = $smcFunc['db_insert']('replace',
					'{db_prefix}lp_params',
					array(
						'item_id' => 'int',
						'type'    => 'string',
						'name'    => 'string',
						'value'   => 'string'
					),
					$params[$i],
					array('item_id', 'type', 'name'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (!empty($comments) && !empty($result)) {
			$temp = [];

			foreach ($comments as $item_id => $comment) {
				foreach ($comment as $com) {
					$temp[] = $com;
				}
			}

			$comments = array_chunk($temp, 100);
			$count    = sizeof($comments);

			for ($i = 0; $i < $count; $i++) {
				$result = $smcFunc['db_insert']('replace',
					'{db_prefix}lp_comments',
					array(
						'id'         => 'int',
						'parent_id'  => 'int',
						'page_id'    => 'int',
						'author_id'  => 'int',
						'message'    => 'string-' . MAX_MSG_LENGTH,
						'created_at' => 'int'
					),
					$comments[$i],
					array('id', 'page_id'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		if (empty($result))
			fatal_lang_error('lp_import_failed', false);

		// Restore the cache
		$db_cache = $db_temp_cache;

		Helpers::cache()->flush();
	}

	/**
	 * @param array|null $pages
	 * @return array
	 */
	private static function getItems($pages)
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT a.id, a.date, a.body, a.intro, a.subject, a.author_id, a.off, a.options, a.comments, a.views, a.shortname, a.type, a.pub_start, a.pub_end, v.value3
			FROM {db_prefix}tp_articles AS a
				LEFT JOIN {db_prefix}tp_variables AS v ON (a.category = v.id AND v.type = {string:type})' . (!empty($pages) ? '
			WHERE a.id IN ({array_int:pages})' : ''),
			array(
				'type'  => 'category',
				'pages' => $pages
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$permissions = explode(',', $row['value3']);

			$perm = 0;
			if (count($permissions) == 1 && $permissions[0] == -1) {
				$perm = 1;
			} elseif (count($permissions) == 1 && $permissions[0] == 0) {
				$perm = 2;
			} elseif (in_array(-1, $permissions)) {
				$perm = 3;
			} elseif (in_array(0, $permissions)) {
				$perm = 3;
			}

			$items[$row['id']] = array(
				'page_id'      => $row['id'],
				'author_id'    => $row['author_id'],
				'alias'        => $row['shortname'] ?: ('page_' . $row['id']),
				'description'  => $row['intro'],
				'content'      => $row['body'],
				'type'         => $row['type'],
				'permissions'  => $perm,
				'status'       => (int) empty($row['off']),
				'num_views'    => $row['views'],
				'num_comments' => 0,
				'created_at'   => $row['date'],
				'updated_at'   => 0,
				'subject'      => $row['subject'],
				'options'      => explode(',', $row['options'])
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	/**
	 * @param array|null $pages
	 * @return array
	 */
	private static function getComments($pages)
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT *
			FROM {db_prefix}tp_comments
			WHERE item_type = {string:type}' . (!empty($pages) ? '
				AND item_id IN ({array_int:pages})' : ''),
			array(
				'type'  => 'article_comment',
				'pages' => $pages
			)
		);

		$comments = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$comments[$row['item_id']][] = array(
				'id'         => $row['id'],
				'parent_id'  => 0,
				'page_id'    => $row['item_id'],
				'author_id'  => $row['member_id'],
				'message'    => $row['comment'],
				'created_at' => $row['datetime']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $comments;
	}
}