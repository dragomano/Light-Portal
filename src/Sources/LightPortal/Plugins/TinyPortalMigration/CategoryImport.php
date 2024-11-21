<?php

/**
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 12.11.24
 */

namespace Bugo\LightPortal\Plugins\TinyPortalMigration;

use Bugo\Compat\{Config, Db, Lang, Utils};
use Bugo\LightPortal\Areas\Imports\AbstractCustomCategoryImport;
use Bugo\LightPortal\Utils\{ItemList, Str};

use const LP_NAME;

if (! defined('LP_NAME'))
	die('No direct access...');

class CategoryImport extends AbstractCustomCategoryImport
{
	public function main(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tiny_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_categories_import'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_categories;sa=import_from_tp';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tiny_portal_migration']['category_import_desc'],
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
				'actions' => [
					'header' => [
						'value' =>  Str::html('input', [
							'type' => 'checkbox',
							'onclick' => 'invertAll(this, this.form);',
							'checked' => 'checked'
						])
					],
					'data' => [
						'function' => static fn($entry) => Str::html('input', [
							'type' => 'checkbox',
							'value' => $entry['id'],
							'name' => 'categories[]',
							'checked' => 'checked'
						]),
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
					'value' => Str::html('input', ['type' => 'hidden']) .
						Str::html('input', [
							'type' => 'submit',
							'name' => 'import_selection',
							'value' => Lang::$txt['lp_import_selection'],
							'class' => 'button'
						]) .
						Str::html('input', [
							'type' => 'submit',
							'name' => 'import_all',
							'value' => Lang::$txt['lp_import_all'],
							'class' => 'button'
						])
				]
			]
		];

		new ItemList($listOptions);
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id'): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'tp_variables')))
			return [];

		$result = Db::$db->query('', '
			SELECT id, value1 AS title
			FROM {db_prefix}tp_variables
			WHERE type = {literal:category}
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'sort'  => $sort,
				'start' => $start,
				'limit' => $limit,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id']] = [
				'id'    => $row['id'],
				'title' => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'tp_variables')))
			return 0;

		$result = Db::$db->query('', '
			SELECT COUNT(*)
			FROM {db_prefix}tp_variables
			WHERE type = {literal:category}',
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	protected function getItems(array $ids): array
	{
		$result = Db::$db->query('', '
			SELECT id, value1 AS title
			FROM {db_prefix}tp_variables
			WHERE type = {literal:category}' . (empty($ids) ? '' : '
				AND id IN ({array_int:categories})'),
			[
				'categories' => $ids,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id']] = [
				'title'       => $row['title'],
				'icon'        => '',
				'description' => '',
				'priority'    => 0,
				'status'      => 1,
			];
		}

		Db::$db->free_result($result);

		return $items;
	}
}
