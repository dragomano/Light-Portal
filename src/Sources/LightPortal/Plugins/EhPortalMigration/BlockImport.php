<?php declare(strict_types=1);

/**
 * @package EhPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 08.11.25
 */

namespace LightPortal\Plugins\EhPortalMigration;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Laminas\Db\Sql\Predicate\Expression;
use LightPortal\DataHandlers\Imports\Database\AbstractDatabaseBlockImport;
use LightPortal\Enums\ContentClass;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Placement;
use LightPortal\Enums\TitleClass;
use LightPortal\UI\Tables\CheckboxColumn;
use LightPortal\UI\Tables\TitleColumn;

if (! defined('LP_NAME'))
	die('No direct access...');

class BlockImport extends AbstractDatabaseBlockImport
{
	protected string $langKey = 'lp_eh_portal_migration';

	protected string $formAction = 'import_from_ep';

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
		if (! $this->sql->tableExists('sp_blocks')) {
			return [];
		}

		$select = $this->sql->select()
			->from('sp_blocks')
			->columns(['id_block', 'type', 'title' => 'label', 'col'])
			->where(['type' => $this->supportedTypes])
			->order($sort)
			->limit($limit)
			->offset($start);

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id_block']] = [
				'id'        => $row['id_block'],
				'type'      => Lang::$txt['lp_' . $this->getType($row['type'])]['title'],
				'title'     => $row['title'],
				'placement' => Utils::$context['lp_block_placements'][$this->getPlacement($row['col'])],
			];
		}

		return $items;
	}

	public function getTotalCount(): int
	{
		if (! $this->sql->tableExists('sp_blocks')) {
			return 0;
		}

		$select = $this->sql->select()
			->from('sp_blocks')
			->columns(['count' => new Expression('COUNT(*)')])
			->where(['type' => $this->supportedTypes]);

		$result = $this->sql->execute($select)->current();

		return (int) $result['count'];
	}

	protected function getItems(array $ids): array
	{
		$select = $this->sql->select()
			->from(['b' => 'sp_blocks'])
			->columns([
				'id_block', 'type', 'title' => 'label', 'col', 'permission_set', 'groups_allowed', 'status' => 'state',
			])
			->join(
				['p' => 'sp_parameters'],
				new Expression('b.id_block = p.id_block AND p.variable = ?', ['content']),
				['content' => 'value']
			)
			->where(['b.type' => $this->supportedTypes]);

		if ($ids !== []) {
			$select->where->in('b.id_block', $ids);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id_block']] = [
				'type'          => $this->getType($row['type']),
				'title'         => $row['title'],
				'content'       => $row['content'],
				'placement'     => $this->getPlacement($row['col']),
				'permissions'   => $this->getPermission($row),
				'status'        => $row['status'],
				'title_class'   => TitleClass::first(),
				'content_class' => ContentClass::first(),
			];
		}

		return $items;
	}

	protected function getType(mixed $type): string
	{
		return str_replace('sp_', '', $type);
	}

	protected function getPlacement(int $col): string
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

	protected function extractPermissions(array $row): int|array
	{
		if (! empty($row['permission_set'])) {
			return $row['permission_set'];
		}

		return match ($row['groups_allowed']) {
			-1      => Permission::GUEST->value,
			0       => Permission::MEMBER->value,
			1       => Permission::ADMIN->value,
			default => Permission::ALL->value,
		};
	}
}
