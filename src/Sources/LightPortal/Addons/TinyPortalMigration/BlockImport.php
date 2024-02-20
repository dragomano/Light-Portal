<?php

/**
 * BlockImport.php
 *
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 20.02.24
 */

namespace Bugo\LightPortal\Addons\TinyPortalMigration;

use Bugo\Compat\{Config, Database as Db, Lang, Utils};
use Bugo\LightPortal\Areas\Imports\AbstractCustomBlockImport;
use Bugo\LightPortal\Utils\ItemList;

if (! defined('LP_NAME'))
	die('No direct access...');

class BlockImport extends AbstractCustomBlockImport
{
	private array $supportedTypes = [5, 10, 11];

	public function main(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tiny_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_blocks_import'];
		Utils::$context['canonical_url']   = Config::$scripturl . '?action=admin;area=lp_blocks;sa=import_from_tp';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tiny_portal_migration']['block_import_desc']
		];

		$this->run();

		$listOptions = [
			'id' => 'tp_blocks',
			'items_per_page' => 50,
			'title' => Lang::$txt['lp_blocks_import'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Utils::$context['canonical_url'],
			'default_sort_col' => 'title',
			'get_items' => [
				'function' => [$this, 'getAll']
			],
			'get_count' => [
				'function' => [$this, 'getTotalCount']
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
				'type' => [
					'header' => [
						'value' => Lang::$txt['lp_block_type']
					],
					'data' => [
						'db'    => 'type',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'type DESC',
						'reverse' => 'type'
					]
				],
				'placement' => [
					'header' => [
						'value' => Lang::$txt['lp_block_placement']
					],
					'data' => [
						'db'    => 'placement',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'bar DESC',
						'reverse' => 'bar'
					]
				],
				'actions' => [
					'header' => [
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" checked>'
					],
					'data' => [
						'function' => static fn($entry) => '<input type="checkbox" value="' . $entry['id'] . '" name="blocks[]" checked>',
						'class' => 'centertext'
					]
				]
			],
			'form' => [
				'href' => Utils::$context['canonical_url']
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

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id'): array
	{
		Db::extend();

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'tp_blocks')))
			return [];

		$result = Utils::$smcFunc['db_query']('', '
			SELECT id, type, title, bar
			FROM {db_prefix}tp_blocks
			WHERE type IN ({array_int:types})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'types' => $this->supportedTypes,
				'sort'  => $sort,
				'start' => $start,
				'limit' => $limit,
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$items[$row['id']] = [
				'id'        => $row['id'],
				'type'      => Lang::$txt['lp_' . $this->getType($row['type'])]['title'],
				'title'     => $row['title'],
				'placement' => Utils::$context['lp_block_placements'][$this->getPlacement($row['bar'])],
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}

	public function getTotalCount(): int
	{
		Db::extend();

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'tp_blocks')))
			return 0;

		$result = Utils::$smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}tp_blocks
			WHERE type IN ({array_int:types})',
			[
				'types' => $this->supportedTypes,
			]
		);

		[$count] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return (int) $count;
	}

	protected function getItems(array $blocks): array
	{
		$result = Utils::$smcFunc['db_query']('', '
			SELECT id, type, title, body, access, bar
			FROM {db_prefix}tp_blocks
			WHERE type IN ({array_int:types})' . (empty($blocks) ? '' : '
				AND id IN ({array_int:blocks})'),
			[
				'types'  => $this->supportedTypes,
				'blocks' => $blocks,
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
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

			$items[$row['id']] = [
				'type'          => $this->getType($row['type']),
				'title'         => $row['title'],
				'content'       => $row['body'],
				'placement'     => $this->getPlacement($row['bar']),
				'permissions'   => $perm,
				'status'        => 0,
				'title_class'   => array_key_first(Utils::$context['lp_all_title_classes']),
				'content_class' => array_key_first(Utils::$context['lp_all_content_classes']),
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}

	private function getType(int $type): string
	{
		return match ($type) {
			5  => 'bbc',
			10 => 'php',
			default => 'html',
		};
	}

	private function getPlacement(int $bar): string
	{
		return match ($bar) {
			1 => 'left',
			2 => 'right',
			5 => 'footer',
			6 => 'header',
			7 => 'bottom',
			default => 'top',
		};
	}
}
