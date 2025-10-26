<?php declare(strict_types=1);

/**
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 26.10.25
 */

namespace LightPortal\Plugins\TinyPortalMigration;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\DataHandlers\Imports\Custom\AbstractCustomBlockImport;
use LightPortal\Enums\ContentClass;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\Placement;
use LightPortal\Enums\TitleClass;
use LightPortal\UI\Tables\CheckboxColumn;
use LightPortal\UI\Tables\TitleColumn;
use Laminas\Db\Sql\Expression;

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
		if (! $this->sql->tableExists('tp_blocks'))
			return [];

		$select = $this->sql->select()
			->from('tp_blocks')
			->columns(['id', 'type', 'title', 'bar'])
			->where(['type' => $this->supportedTypes])
			->order($sort)
			->limit($limit)
			->offset($start);

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id']] = [
				'id'        => $row['id'],
				'type'      => Lang::$txt['lp_' . $this->getType($row['type'])]['title'],
				'title'     => $row['title'],
				'placement' => Utils::$context['lp_block_placements'][$this->getPlacement($row['bar'])],
			];
		}

		return $items;
	}

	public function getTotalCount(): int
	{
		if (! $this->sql->tableExists('tp_blocks'))
			return 0;

		$select = $this->sql->select()
			->from('tp_blocks')
			->columns(['count' => new Expression('COUNT(*)')])
			->where(['type' => $this->supportedTypes]);

		$result = $this->sql->execute($select)->current();

		return $result['count'];
	}

	protected function getItems(array $ids): array
	{
		$select = $this->sql->select()
			->from('tp_blocks')
			->columns(['id', 'type', 'title', 'body', 'access', 'bar', 'off'])
			->where(['type' => $this->supportedTypes]);

		if ($ids !== []) {
			$select->where->in('id', $ids);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id']] = [
				'type'          => $this->getType($row['type']),
				'title'         => $row['title'],
				'content'       => $row['body'],
				'placement'     => $this->getPlacement($row['bar']),
				'permissions'   => $this->getPermission($row),
				'status'        => $row['off'] === 0 ? 1 : 0,
				'title_class'   => TitleClass::first(),
				'content_class' => ContentClass::first(),
			];
		}

		return $items;
	}

	protected function getType(mixed $type): string
	{
		return match ($type) {
			5  => ContentType::BBC->name(),
			10 => ContentType::PHP->name(),
			default => ContentType::HTML->name(),
		};
	}

	protected function getPlacement(int $col): string
	{
		return match ($col) {
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
		return array_map(intval(...), explode(',', (string) $row['access']));
	}
}
