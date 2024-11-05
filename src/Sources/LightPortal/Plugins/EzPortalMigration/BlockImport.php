<?php

/**
 * @package EzPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 10.10.24
 */

namespace Bugo\LightPortal\Plugins\EzPortalMigration;

use Bugo\Compat\{Config, Db, Lang, Utils};
use Bugo\LightPortal\Areas\Imports\AbstractCustomBlockImport;
use Bugo\LightPortal\Enums\{Permission, Placement};
use Bugo\LightPortal\Utils\ItemList;

use const LP_NAME;

if (! defined('LP_NAME'))
	die('No direct access...');

class BlockImport extends AbstractCustomBlockImport
{
	private array $supportedTypes = ['HTML', 'PHP'];

	public function main(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_ez_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_blocks_import'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_blocks;sa=import_from_ez';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_ez_portal_migration']['block_import_desc'],
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
						'default' => 'blocktitle DESC',
						'reverse' => 'blocktitle'
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
						'default' => 'blocktype DESC',
						'reverse' => 'blocktype'
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

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'ezp_blocks')))
			return [];

		$result = Utils::$smcFunc['db_query']('', '
			SELECT b.id_block, b.blocktype AS type, bl.customtitle AS title, bl.id_column AS col
			FROM {db_prefix}ezp_blocks AS b
				INNER JOIN {db_prefix}ezp_block_layout AS bl ON (b.id_block = bl.id_block)
			WHERE b.blocktype IN ({array_string:types})
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

		if (empty(Utils::$smcFunc['db_list_tables'](false, Config::$db_prefix . 'ezp_blocks')))
			return 0;

		$result = Utils::$smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}ezp_blocks
			WHERE blocktype IN ({array_string:types})',
			[
				'types' => $this->supportedTypes,
			]
		);

		[$count] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);

		return (int) $count;
	}

	protected function getItems(array $ids): array
	{
		$result = Utils::$smcFunc['db_query']('', '
			SELECT
				b.id_block, b.blocktype AS type, b.blocktitle,
				bl.customtitle AS title, bl.id_column AS col, bl.permissions, bl.active AS status, bl.blockdata AS content
			FROM {db_prefix}ezp_blocks AS b
				INNER JOIN {db_prefix}ezp_block_layout AS bl ON (b.id_block = bl.id_block)
			WHERE b.blocktype IN ({array_string:types})' . (empty($ids) ? '' : '
				AND b.id_block IN ({array_int:blocks})'),
			[
				'types'  => $this->supportedTypes,
				'blocks' => $ids,
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
		return strtolower($type);
	}

	private function getPlacement(int $col): string
	{
		return match ($col) {
			1 => Placement::LEFT->name(),
			2 => Placement::TOP->name(),
			3 => Placement::RIGHT->name(),
			5 => Placement::BOTTOM->name(),
			default => Placement::HEADER->name(),
		};
	}

	private function getBlockPermission(array $row): int
	{
		$permissions = explode(',', (string) $row['permissions']);

		$perm = Permission::ADMIN->value;
		if (count($permissions) == 1 && $permissions[0] == -1) {
			$perm = Permission::GUEST->value;
		} elseif (count($permissions) == 1 && $permissions[0] == 0) {
			$perm = Permission::MEMBER->value;
		} elseif (in_array(-1, $permissions)) {
			$perm = Permission::ALL->value;
		} elseif (in_array(0, $permissions)) {
			$perm = Permission::ALL->value;
		}

		return $perm;
	}
}
