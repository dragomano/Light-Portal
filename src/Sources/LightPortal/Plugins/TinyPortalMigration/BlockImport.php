<?php declare(strict_types=1);

/**
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 23.09.25
 */

namespace Bugo\LightPortal\Plugins\TinyPortalMigration;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\Imports\Custom\AbstractCustomBlockImport;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\Placement;
use Bugo\LightPortal\Enums\TitleClass;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\TitleColumn;

if (! defined('LP_NAME'))
	die('No direct access...');

class BlockImport extends AbstractCustomBlockImport
{
	protected string $langKey    = 'lp_tiny_portal_migration';

	protected string $formAction = 'import_from_tp';

	protected string $uiTableId  = 'tp_blocks';

	private array $supportedTypes = [5, 10, 11];

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
				->setSort('bar DESC', 'bar'),
			CheckboxColumn::make(entity: 'blocks'),
		];
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
				'permissions'   => $this->getPermission($row),
				'status'        => 0,
				'title_class'   => TitleClass::first(),
				'content_class' => ContentClass::first(),
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	protected function getType(string $type): string
	{
		return match ((int) $type) {
			5  => ContentType::BBC->name(),
			10 => ContentType::PHP->name(),
			default => ContentType::HTML->name(),
		};
	}

	protected function getPlacement(string $col): string
	{
		return match ((int) $col) {
			1 => Placement::LEFT->name(),
			2 => Placement::RIGHT->name(),
			5 => Placement::FOOTER->name(),
			6 => Placement::HEADER->name(),
			7 => Placement::BOTTOM->name(),
			default => Placement::TOP->name(),
		};
	}

	protected function extractPermissions(array $row): int|array
	{
		return array_map('intval', explode(',', (string) $row['access']));
	}
}
