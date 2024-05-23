<?php

/**
 * BlockImport.php
 *
 * @package EhPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 23.05.24
 */

namespace Bugo\LightPortal\Addons\EhPortalMigration;

use Bugo\Compat\{Config, Db, Lang, Utils};
use Bugo\LightPortal\Areas\Imports\AbstractCustomBlockImport;
use Bugo\LightPortal\Enums\Placement;
use Bugo\LightPortal\Utils\ItemList;

if (! defined('LP_NAME'))
	die('No direct access...');

class BlockImport extends AbstractCustomBlockImport
{
	private array $supportedTypes = ['sp_bbc', 'sp_html', 'sp_php'];

	public function main(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_eh_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_blocks_import'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_blocks;sa=import_from_ep';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_eh_portal_migration']['block_import_desc'],
		];

		$this->run();

		$listOptions = [
			'id' => 'tp_blocks',
			'items_per_page' => 50,
			'title' => Lang::$txt['lp_blocks_import'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Utils::$context['form_action'],
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
						'default' => 'col DESC',
						'reverse' => 'col'
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

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id_block'): array
	{
		Db::extend();

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'sp_blocks')))
			return [];

		$result = Utils::$smcFunc['db_query']('', '
			SELECT id_block, type, label AS title, col
			FROM {db_prefix}sp_blocks
			WHERE type IN ({array_string:types})
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
			$items[$row['id_block']] = [
				'id'        => $row['id_block'],
				'type'      => Lang::$txt['lp_' . $this->getType($row['type'])]['title'],
				'title'     => $row['title'],
				'placement' => Utils::$context['lp_block_placements'][$this->getPlacement($row['col'])],
			];
		}

		Utils::$smcFunc['db_free_result']($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		Db::extend();

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'sp_blocks')))
			return 0;

		$result = Utils::$smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}sp_blocks
			WHERE type IN ({array_string:types})',
			[
				'types' => $this->supportedTypes,
			]
		);

		[$count] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);

		return (int) $count;
	}

	protected function getItems(array $blocks): array
	{
		$result = Utils::$smcFunc['db_query']('', '
			SELECT
				b.id_block, b.type, b.label AS title, b.col, b.permission_set, b.groups_allowed, b.state AS status,
				p.value AS content
			FROM {db_prefix}sp_blocks AS b
				INNER JOIN {db_prefix}sp_parameters AS p ON (b.id_block = p.id_block AND p.variable = {literal:content})
			WHERE b.type IN ({array_string:types})' . (empty($blocks) ? '' : '
				AND b.id_block IN ({array_int:blocks})'),
			[
				'types'  => $this->supportedTypes,
				'blocks' => $blocks,
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$items[$row['id_block']] = [
				'type'          => $this->getType($row['type']),
				'title'         => $row['title'],
				'content'       => $row['content'],
				'placement'     => $this->getPlacement($row['col']),
				'permissions'   => $this->getBlockPermission($row),
				'status'        => (int) $row['status'],
				'title_class'   => array_key_first(Utils::$context['lp_all_title_classes']),
				'content_class' => array_key_first(Utils::$context['lp_all_content_classes']),
			];
		}

		Utils::$smcFunc['db_free_result']($result);

		return $items;
	}

	private function getType(string $type): string
	{
		return str_replace('sp_', '', $type);
	}

	private function getPlacement(int $col): string
	{
		return match ($col) {
			1 => Placement::LEFT->name(),
			3 => Placement::BOTTOM->name(),
			4 => Placement::RIGHT->name(),
			5 => Placement::HEADER->name(),
			6 => Placement::FOOTER->name(),
			default => Placement::TOP->name(),
		};
	}

	private function getBlockPermission(array $row): int
	{
		$perm = $row['permission_set'];

		if (empty($row['permission_set'])) {
			$groups = $row['groups_allowed'];

			if ($groups == -1) {
				$perm = 1;
			} elseif ($groups == 0) {
				$perm = 2;
			} elseif ($groups == 1) {
				$perm = 0;
			} else {
				$perm = 3;
			}
		}

		return $perm;
	}
}
