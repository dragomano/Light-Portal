<?php declare(strict_types=1);

/**
 * @package EhPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 23.09.25
 */

namespace Bugo\LightPortal\Plugins\EhPortalMigration;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\Imports\Custom\AbstractCustomBlockImport;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\Placement;
use Bugo\LightPortal\Enums\TitleClass;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\TitleColumn;

if (! defined('LP_NAME'))
	die('No direct access...');

class BlockImport extends AbstractCustomBlockImport
{
	protected string $langKey = 'lp_eh_portal_migration';

	protected string $formAction = 'import_from_eh';

	protected string $uiTableId = 'eh_blocks';

	private array $supportedTypes = ['sp_bbc', 'sp_html', 'sp_php'];

	protected function defineUiColumns(): array
	{
		return [
			TitleColumn::make()
				->setData('title', 'word_break'),
			Column::make('type', Lang::$txt['lp_block_type'])
				->setData('type', 'centertext')
				->setSort('type DESC', 'type'),
			Column::make('placement', Lang::$txt['lp_block_placement'])
				->setData('placement', 'centertext')
				->setSort('col DESC', 'col'),
			CheckboxColumn::make(entity: 'blocks'),
		];
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id_block'): array
	{
		if (empty(Db::$db->list_tables(false, Config::$db_prefix . 'sp_blocks')))
			return [];

		$result = Db::$db->query('
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

		$result = Db::$db->query('
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
		$result = Db::$db->query('
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
				'permissions'   => $this->getPermission($row),
				'status'        => (int) $row['status'],
				'title_class'   => TitleClass::first(),
				'content_class' => ContentClass::first(),
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	protected function getType(string $type): string
	{
		return str_replace('sp_', '', $type);
	}

	protected function getPlacement(string $col): string
	{
		return match ((int) $col) {
			1 => Placement::LEFT->name(),
			3 => Placement::BOTTOM->name(),
			4 => Placement::RIGHT->name(),
			5 => Placement::HEADER->name(),
			6 => Placement::FOOTER->name(),
			default => Placement::TOP->name(),
		};
	}

	protected function extractPermissions(array $row): int|array
	{
		if (! empty($row['permission_set'])) {
			return (int) $row['permission_set'];
		}

		return match ((int) $row['groups_allowed']) {
			-1      => Permission::GUEST->value,
			0       => Permission::MEMBER->value,
			1       => Permission::ADMIN->value,
			default => Permission::ALL->value,
		};
	}
}
