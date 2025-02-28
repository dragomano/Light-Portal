<?php declare(strict_types=1);

/**
 * @package EzPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 11.02.25
 */

namespace Bugo\LightPortal\Plugins\EzPortalMigration;

use Bugo\Bricks\Presenters\TablePresenter;
use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Imports\AbstractCustomBlockImport;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\Placement;
use Bugo\LightPortal\Enums\TitleClass;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\ImportButtonsRow;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\TitleColumn;

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

		app(TablePresenter::class)->show(
			PortalTableBuilder::make('ez_blocks', Lang::$txt['lp_blocks_import'])
				->withParams(
					50,
					defaultSortColumn: 'title'
				)
				->setItems($this->getAll(...))
				->setCount($this->getTotalCount(...))
				->addColumns([
					TitleColumn::make()
						->setData('title', 'word_break')
						->setSort('blocktitle DESC', 'blocktitle'),
					Column::make('type', Lang::$txt['lp_block_type'])
						->setData('type', 'centertext')
						->setSort('blocktitle DESC', 'blocktitle'),
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
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'ezp_blocks')))
			return [];

		$result = Db::$db->query('', '
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
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id_block']] = [
				'id'        => $row['id_block'],
				'type'      => Lang::$txt['lp_' . $this->getType($row['type'])]['title'],
				'title'     => $row['title'],
				'placement' => Utils::$context['lp_block_placements'][$this->getPlacement((int) $row['col'])],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'ezp_blocks')))
			return 0;

		$result = Db::$db->query('', '
			SELECT COUNT(*)
			FROM {db_prefix}ezp_blocks
			WHERE blocktype IN ({array_string:types})',
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
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id_block']] = [
				'type'          => $this->getType($row['type']),
				'title'         => $row['title'],
				'content'       => $row['content'],
				'placement'     => $this->getPlacement($row['col']),
				'permissions'   => $this->getBlockPermission($row),
				'status'        => (int) $row['status'],
				'title_class'   => TitleClass::first(),
				'content_class' => ContentClass::first(),
			];
		}

		Db::$db->free_result($result);

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
