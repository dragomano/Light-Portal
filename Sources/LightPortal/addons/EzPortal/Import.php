<?php

/**
 * Import.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\EzPortal;

use Bugo\LightPortal\Impex\AbstractImport;
use Bugo\LightPortal\Helpers;

class Import extends AbstractImport
{
	/**
	 * EzPortal pages import
	 *
	 * Импорт страниц EzPortal
	 *
	 * @return void
	 */
	public function main()
	{
		global $context, $txt, $scripturl;

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_ez_portal']['label_name'];
		$context['page_area_title'] = $txt['lp_pages_import'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_pages;sa=import_from_ez';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_ez_portal']['desc']
		);

		$this->run();

		$listOptions = array(
			'id' => 'lp_pages',
			'items_per_page' => 50,
			'title' => $txt['lp_pages_import'],
			'no_items_label' => $txt['lp_no_items'],
			'base_href' => $context['canonical_url'],
			'default_sort_col' => 'id',
			'get_items' => array(
				'function' => array($this, 'getAll')
			),
			'get_count' => array(
				'function' => array($this, 'getTotalCount')
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
						'default' => 'id_page',
						'reverse' => 'id_page DESC'
					)
				),
				'alias' => array(
					'header' => array(
						'value' => $txt['lp_page_alias']
					),
					'data' => array(
						'db'    => 'alias',
						'class' => 'centertext word_break'
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
						'default' => 'title DESC',
						'reverse' => 'title'
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
						<input type="hidden">
						<input type="submit" name="import_selection" value="' . $txt['lp_ez_portal']['button_run'] . '" class="button">
						<input type="submit" name="import_all" value="' . $txt['lp_ez_portal']['button_all'] . '" class="button">'
				)
			)
		);

		Helpers::require('Subs-List');
		createList($listOptions);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'lp_pages';
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
	public function getAll(int $start = 0, int $items_per_page = 0, string $sort = 'id_page'): array
	{
		global $smcFunc, $db_prefix, $user_info;

		db_extend();

		if (empty($smcFunc['db_list_tables'](false, $db_prefix . 'ezp_page')))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT id_page, date, title, views
			FROM {db_prefix}ezp_page
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
			$items[$row['id_page']] = array(
				'id'         => $row['id_page'],
				'alias'      => $smcFunc['strtolower'](explode(' ', $row['title'])[0]) . $row['id_page'],
				'type'       => 'html',
				'status'     => 1,
				'num_views'  => $row['views'],
				'author_id'  => $user_info['id'],
				'created_at' => Helpers::getFriendlyTime($row['date']),
				'title'      => $row['title']
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
	public function getTotalCount(): int
	{
		global $smcFunc, $db_prefix;

		db_extend();

		if (empty($smcFunc['db_list_tables'](false, $db_prefix . 'ezp_page')))
			return 0;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}ezp_page',
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
	protected function run()
	{
		global $db_temp_cache, $db_cache, $language, $modSettings, $smcFunc;

		if (Helpers::post()->isEmpty('pages') && Helpers::post()->has('import_all') === false)
			return;

		// Might take some time.
		@set_time_limit(600);

		// Don't allow the cache to get too full
		$db_temp_cache = $db_cache;
		$db_cache = [];

		$pages = !empty(Helpers::post('pages')) && Helpers::post()->has('import_all') === false ? Helpers::post('pages') : null;

		$items = $this->getItems($pages);

		$titles = [];
		foreach ($items as $page_id => $item) {
			$titles[] = [
				'item_id' => $page_id,
				'type'    => 'page',
				'lang'    => $language,
				'title'   => $item['subject']
			];

			if ($language != 'english' && !empty($modSettings['userLanguage'])) {
				$titles[] = [
					'item_id' => $page_id,
					'type'    => 'page',
					'lang'    => 'english',
					'title'   => $item['subject']
				];
			}

			unset($items[$page_id]['subject']);
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
						'content'      => 'string',
						'type'         => 'string',
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
	private function getItems(?array $pages): array
	{
		global $smcFunc, $user_info;

		$request = $smcFunc['db_query']('', '
			SELECT id_page, date, title, content, views, permissions
			FROM {db_prefix}ezp_page' . (!empty($pages) ? '
			WHERE id_page IN ({array_int:pages})' : ''),
			array(
				'pages' => $pages
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$permissions = explode(',', $row['permissions']);

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

			$items[$row['id_page']] = array(
				'page_id'      => $row['id_page'],
				'author_id'    => $user_info['id'],
				'alias'        => $smcFunc['strtolower'](explode(' ', $row['title'])[0]) . $row['id_page'],
				'content'      => $row['content'],
				'type'         => 'html',
				'permissions'  => $perm,
				'status'       => 1,
				'num_views'    => $row['views'],
				'num_comments' => 0,
				'created_at'   => $row['date'],
				'updated_at'   => 0,
				'subject'      => $row['title']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}
}
