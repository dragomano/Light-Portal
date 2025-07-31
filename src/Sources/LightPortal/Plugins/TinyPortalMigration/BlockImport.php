<?php declare(strict_types=1);

/**
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.04.25
 */

namespace Bugo\LightPortal\Plugins\TinyPortalMigration;

use Bugo\Bricks\Presenters\TablePresenter;
use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Imports\AbstractCustomBlockImport;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\ContentType;
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
	private array $supportedTypes = [5, 10, 11];

	public function main(): void
	{
		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tiny_portal_migration']['label_name'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_blocks_import'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_blocks;sa=import_from_tp';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tiny_portal_migration']['block_import_desc'],
		];

		$this->run();

		app(TablePresenter::class)->show(
			PortalTableBuilder::make('tp_blocks', Lang::$txt['lp_blocks_import'])
				->withParams(50, defaultSortColumn: 'title')
				->setItems($this->getAll(...))
				->setCount($this->getTotalCount(...))
				->addColumns([
					TitleColumn::make()
						->setData('title', 'word_break'),
					Column::make('type', Lang::$txt['lp_block_type'])
						->setData('type', 'centertext')
						->setSort('type DESC', 'type'),
					Column::make('placement', Lang::$txt['lp_block_placement'])
						->setData('placement', 'centertext')
						->setSort('bar DESC', 'bar'),
					CheckboxColumn::make(entity: 'blocks'),
				])
				->addRow(ImportButtonsRow::make())
		);
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id'): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'tp_blocks')))
			return [];

		$result = Db::$db->query('
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
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id']] = [
				'id'        => $row['id'],
				'type'      => Lang::$txt['lp_' . $this->getType($row['type'])]['title'],
				'title'     => $row['title'],
				'placement' => Utils::$context['lp_block_placements'][$this->getPlacement($row['bar'])],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(): int
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'tp_blocks')))
			return 0;

		$result = Db::$db->query('
			SELECT COUNT(*)
			FROM {db_prefix}tp_blocks
			WHERE type IN ({array_int:types})',
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
		$result = Db::$db->query('
			SELECT id, type, title, body, access, bar
			FROM {db_prefix}tp_blocks
			WHERE type IN ({array_int:types})' . (empty($ids) ? '' : '
				AND id IN ({array_int:blocks})'),
			[
				'types'  => $this->supportedTypes,
				'blocks' => $ids,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['id']] = [
				'type'          => $this->getType($row['type']),
				'title'         => $row['title'],
				'content'       => $row['body'],
				'placement'     => $this->getPlacement($row['bar']),
				'permissions'   => $this->getBlockPermission($row),
				'status'        => 0,
				'title_class'   => TitleClass::first(),
				'content_class' => ContentClass::first(),
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	private function getType(string $type): string
	{
		return match ((int) $type) {
			5  => ContentType::BBC->name(),
			10 => ContentType::PHP->name(),
			default => ContentType::HTML->name(),
		};
	}

	private function getPlacement(string $bar): string
	{
		return match ((int) $bar) {
			1 => Placement::LEFT->name(),
			2 => Placement::RIGHT->name(),
			5 => Placement::FOOTER->name(),
			6 => Placement::HEADER->name(),
			7 => Placement::BOTTOM->name(),
			default => Placement::TOP->name(),
		};
	}

	private function getBlockPermission(array $row): int
	{
		$permissions = explode(',', (string) $row['access']);

		return match (true) {
			count($permissions) == 1 && $permissions[0] == -1 => Permission::GUEST->value,
			count($permissions) == 1 && $permissions[0] == 0 => Permission::MEMBER->value,
			in_array(-1, $permissions), in_array(0, $permissions) => Permission::ALL->value,
			default => Permission::ADMIN->value,
		};
	}
}
