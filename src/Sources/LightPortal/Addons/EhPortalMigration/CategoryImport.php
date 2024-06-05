<?php

/**
 * CategoryImport.php
 *
 * @package EhPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 30.05.24
 */

namespace Bugo\LightPortal\Addons\EhPortalMigration;

use Bugo\Compat\{Config, Db, Lang, Utils};
use Bugo\LightPortal\Areas\Imports\AbstractCustomCategoryImport;
use Bugo\LightPortal\Utils\ItemList;

use const LP_NAME;

if (! defined('LP_NAME'))
	die('No direct access...');

class CategoryImport extends AbstractCustomCategoryImport
{
	public function main(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_eh_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_categories_import'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_categories;sa=import_from_ep';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_eh_portal_migration']['category_import_desc'],
		];

		$this->run();

		$listOptions = [
			'id' => 'tp_categories',
			'items_per_page' => 50,
			'title' => Lang::$txt['lp_categories_import'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Utils::$context['form_action'],
			'default_sort_col' => 'title',
			'get_items' => [
				'function' => $this->getAll(...)
			],
			'get_count' => [
				'function' => $this->getTotalCount(...)
			],
			'columns' => [
				'title' => [
					'header' => [
						'value' => Lang::$txt['lp_title']
					],
					'data' => [
						'db'    => 'title',
						'class' => 'word_break'
					],
					'sort' => [
						'default' => 'title DESC',
						'reverse' => 'title'
					]
				],
				'status' => [
					'header' => [
						'value' => Lang::$txt['status']
					],
					'data' => [
						'db'    => 'status',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'status DESC',
						'reverse' => 'status'
					]
				],
				'actions' => [
					'header' => [
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" checked>'
					],
					'data' => [
						'function' => static fn($entry) => '<input type="checkbox" value="' . $entry['id'] . '" name="categories[]" checked>',
						'class' => 'centertext'
					]
				]
			],
			'form' => [
				'href' => Utils::$context['form_action']
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => '
						<input type="hidden">
						<input type="submit" name="import_selection" value="' . Lang::$txt['lp_import_selection'] . '" class="button">
						<input type="submit" name="import_all" value="' . Lang::$txt['lp_import_all'] . '" class="button">'
				]
			]
		];

		new ItemList($listOptions);
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id_category'): array
	{
		Db::extend();

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'sp_categories')))
			return [];

		$result = Utils::$smcFunc['db_query']('', '
			SELECT id_category, name AS title, publish AS status
			FROM {db_prefix}sp_categories
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'sort'  => $sort,
				'start' => $start,
				'limit' => $limit,
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$items[$row['id_category']] = [
				'id'     => $row['id_category'],
				'title'  => $row['title'],
				'status' => $row['status'],
			];
		}

		Utils::$smcFunc['db_free_result']($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		Db::extend();

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'sp_categories')))
			return 0;

		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT COUNT(*)
			FROM {db_prefix}sp_categories',
			[]
		);

		[$count] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);

		return (int) $count;
	}

	protected function getItems(array $ids): array
	{
		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT id_category, name AS title, publish AS status
			FROM {db_prefix}sp_categories' . (empty($ids) ? '' : '
			WHERE id_category IN ({array_int:categories})'),
			[
				'categories' => $ids,
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$items[$row['id_category']] = [
				'title'       => $row['title'],
				'icon'        => '',
				'description' => '',
				'priority'    => 0,
				'status'      => (int) $row['status'],
			];
		}

		Utils::$smcFunc['db_free_result']($result);

		return $items;
	}
}
