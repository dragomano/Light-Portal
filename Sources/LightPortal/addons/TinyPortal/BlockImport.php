<?php

/**
 * Import.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\TinyPortal;

use Bugo\LightPortal\Impex\AbstractImport;
use Bugo\LightPortal\Helpers;

class BlockImport extends AbstractImport
{
	/**
	 * @var array
	 */
	private $supported_types = [5, 10, 11];

	/**
	 * TinyPortal blocks import
	 *
	 * Импорт блоков TinyPortal
	 *
	 * @return void
	 */
	public function main()
	{
		global $context, $txt, $scripturl;

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_tiny_portal']['label_name'];
		$context['page_area_title'] = $txt['lp_blocks_import'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_blocks;sa=import_from_tp';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_tiny_portal']['block_import_desc']
		);

		$this->run();

		$listOptions = array(
			'id' => 'lp_blocks',
			'items_per_page' => 50,
			'title' => $txt['lp_blocks_import'],
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
						'default' => 'id',
						'reverse' => 'id DESC'
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
				'type' => array(
					'header' => array(
						'value' => $txt['lp_block_type']
					),
					'data' => array(
						'db'    => 'type',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'type DESC',
						'reverse' => 'type'
					)
				),
				'placement' => array(
					'header' => array(
						'value' => $txt['lp_block_placement']
					),
					'data' => array(
						'db'    => 'placement',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'bar DESC',
						'reverse' => 'bar'
					)
				),
				'actions' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" checked>'
					),
					'data' => array(
						'function' => function ($entry)
						{
							return '<input type="checkbox" value="' . $entry['id'] . '" name="blocks[]" checked>';
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
						<input type="submit" name="import_selection" value="' . $txt['lp_tiny_portal']['button_run'] . '" class="button">
						<input type="submit" name="import_all" value="' . $txt['lp_tiny_portal']['button_all'] . '" class="button">'
				)
			)
		);

		Helpers::require('Subs-List');
		createList($listOptions);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'lp_blocks';
	}

	/**
	 * Get the list of blocks
	 *
	 * Получаем список блоков
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 * @return array
	 */
	public function getAll(int $start = 0, int $items_per_page = 0, string $sort = 'id')
	{
		global $smcFunc, $db_prefix, $txt, $context;

		db_extend();

		if (empty($smcFunc['db_list_tables'](false, $db_prefix . 'tp_blocks')))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT id, type, title, bar
			FROM {db_prefix}tp_blocks
			WHERE type IN ({array_int:types})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array(
				'types' => $this->supported_types,
				'sort'  => $sort,
				'start' => $start,
				'limit' => $items_per_page
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$items[$row['id']] = array(
				'id'        => $row['id'],
				'type'      => $txt['lp_block_types'][$this->getType($row['type'])],
				'title'     => $row['title'],
				'placement' => $context['lp_block_placements'][$this->getPlacement($row['bar'])]
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	/**
	 * Get the total number of blocks
	 *
	 * Подсчитываем общее количество блоков
	 *
	 * @return int
	 */
	public function getTotalCount()
	{
		global $smcFunc, $db_prefix;

		db_extend();

		if (empty($smcFunc['db_list_tables'](false, $db_prefix . 'tp_blocks')))
			return 0;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}tp_blocks
			WHERE type IN ({array_int:types})',
			array(
				'types' => $this->supported_types
			)
		);

		[$num_blocks] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return (int) $num_blocks;
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
		global $db_temp_cache, $db_cache, $language, $smcFunc;

		if (Helpers::post()->isEmpty('blocks') && Helpers::post()->has('import_all') === false)
			return;

		// Might take some time.
		@set_time_limit(600);

		// Don't allow the cache to get too full
		$db_temp_cache = $db_cache;
		$db_cache = [];

		$blocks = !empty(Helpers::post('blocks')) && Helpers::post()->has('import_all') === false ? Helpers::post('blocks') : null;

		$items = $this->getItems($blocks);

		$titles = [];
		foreach ($items as $block_id => $item) {
			$titles[] = [
				'type'  => 'block',
				'lang'  => $language,
				'title' => $item['title']
			];

			unset($items[$block_id]['title']);
		}

		$result = [];

		if (!empty($items)) {
			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$temp = $smcFunc['db_insert']('',
					'{db_prefix}lp_blocks',
					array(
						'type'          => 'string',
						'content'       => 'string-65534',
						'placement'     => 'string-10',
						'permissions'   => 'int',
						'status'        => 'int',
						'title_class'   => 'string',
						'content_class' => 'string'
					),
					$items[$i],
					array('block_id'),
					2
				);

				$smcFunc['lp_num_queries']++;

				$result = array_merge($result, $temp);
			}
		}

		if (!empty($titles) && !empty($result)) {
			foreach ($result as $key => $value) {
				$titles[$key]['item_id'] = $value;
			}

			$titles = array_chunk($titles, 100);
			$count  = sizeof($titles);

			for ($i = 0; $i < $count; $i++) {
				$result = $smcFunc['db_insert']('',
					'{db_prefix}lp_titles',
					array(
						'type'    => 'string',
						'lang'    => 'string',
						'title'   => 'string',
						'item_id' => 'int'
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
	 * @param array|null $blocks
	 * @return array
	 */
	private function getItems($blocks)
	{
		global $smcFunc, $context;

		$request = $smcFunc['db_query']('', '
			SELECT id, type, title, body, access, bar
			FROM {db_prefix}tp_blocks
			WHERE type IN ({array_int:types})' . (!empty($blocks) ? '
				AND id IN ({array_int:blocks})' : ''),
			array(
				'types'  => $this->supported_types,
				'blocks' => $blocks
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$permissions = explode(',', $row['access']);

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
				'type'          => $this->getType($row['type']),
				'title'         => $row['title'],
				'content'       => $row['body'],
				'placement'     => $this->getPlacement($row['bar']),
				'permissions'   => $perm,
				'status'        => 0,
				'title_class'   => array_key_first($context['lp_all_title_classes']),
				'content_class' => array_key_first($context['lp_all_content_classes'])
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	/**
	 * @param int $type
	 * @return string
	 */
	private function getType($type)
	{
		switch ($type) {
			case 5:
				return 'bbc';
			case 10:
				return 'php';
			case 11:
			default:
				return 'html';
		}
	}

	/**
	 * @param int $bar
	 * @return string
	 */
	private function getPlacement($bar)
	{
		switch ($bar) {
			case 1:
				return 'left';
			case 2:
				return 'right';
			case 5:
				return 'footer';
			case 6:
				return 'header';
			case 7:
				return 'bottom';
			case 3:
			case 4:
			default:
				return 'top';
		}
	}
}
