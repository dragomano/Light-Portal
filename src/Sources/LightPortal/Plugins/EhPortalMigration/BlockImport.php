<?php

/**
 * @package EhPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 03.12.24
 */

namespace Bugo\LightPortal\Plugins\EhPortalMigration;

use Bugo\Bricks\Presenters\TablePresenter;
use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Imports\AbstractCustomBlockImport;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\Placement;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\ImportButtonsRow;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\TitleColumn;

use const LP_NAME;

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

		TablePresenter::show(
			PortalTableBuilder::make('ep_blocks', Lang::$txt['lp_blocks_import'])
				->withParams(
					50,
					defaultSortColumn: 'title'
				)
				->setItems($this->getAll(...))
				->setCount($this->getTotalCount(...))
				->addColumns([
					TitleColumn::make()
						->setData('title', 'word_break')
						->setSort('title DESC', 'title'),
					Column::make('type', Lang::$txt['lp_block_type'])
						->setData('type', 'centertext')
						->setSort('type DESC', 'type'),
					Column::make('placement', Lang::$txt['lp_block_placement'])
						->setData('placement', 'centertext')
						->setSort('col DESC', 'col'),
					CheckboxColumn::make(entity: 'blocks'),
				])
				->addRow(ImportButtonsRow::make())
		);
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id_block'): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'sp_blocks')))
			return [];

		$result = Db::$db->query('', '
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
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id_block']] = [
				'id'        => $row['id_block'],
				'type'      => Lang::$txt['lp_' . $this->getType($row['type'])]['title'],
				'title'     => $row['title'],
				'placement' => Utils::$context['lp_block_placements'][$this->getPlacement($row['col'])],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'sp_blocks')))
			return 0;

		$result = Db::$db->query('', '
			SELECT COUNT(*)
			FROM {db_prefix}sp_blocks
			WHERE type IN ({array_string:types})',
			[
				'types' => $this->supportedTypes,
			]
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	protected function getItems(array $ids): array
	{
		$result = Db::$db->query('', '
			SELECT
				b.id_block, b.type, b.label AS title, b.col, b.permission_set, b.groups_allowed, b.state AS status,
				p.value AS content
			FROM {db_prefix}sp_blocks AS b
				INNER JOIN {db_prefix}sp_parameters AS p ON (b.id_block = p.id_block AND p.variable = {literal:content})
			WHERE b.type IN ({array_string:types})' . (empty($ids) ? '' : '
				AND b.id_block IN ({array_int:blocks})'),
			[
				'types'  => $this->supportedTypes,
				'blocks' => $ids,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
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

		Db::$db->free_result($result);

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
				$perm = Permission::GUEST->value;
			} elseif ($groups == 0) {
				$perm = Permission::MEMBER->value;
			} elseif ($groups == 1) {
				$perm = Permission::ADMIN->value;
			} else {
				$perm = Permission::ALL->value;
			}
		}

		return $perm;
	}
}
