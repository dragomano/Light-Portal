<?php declare(strict_types=1);

/**
 * @package EzPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 09.10.25
 */

namespace Bugo\LightPortal\Plugins\EzPortalMigration;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\Imports\Custom\AbstractCustomBlockImport;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\Placement;
use Bugo\LightPortal\Enums\TitleClass;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Laminas\Db\Sql\Expression;

if (! defined('LP_NAME'))
	die('No direct access...');

class BlockImport extends AbstractCustomBlockImport
{
	protected string $langKey = 'lp_ez_portal_migration';

	protected string $formAction = 'import_from_ez';

	protected string $uiTableId = 'ez_blocks';

	private array $supportedTypes = ['HTML', 'PHP'];

	protected function defineUiColumns(): array
	{
		return [
			TitleColumn::make()
				->setData('title', 'word_break')
				->setSort('blocktitle', 'blocktitle DESC'),
			Column::make('type', Lang::$txt['lp_block_type'])
				->setData('type', 'centertext')
				->setSort('blocktitle DESC', 'blocktitle'),
			Column::make('placement', Lang::$txt['lp_block_placement'])
				->setData('placement', 'centertext')
				->setSort('col DESC', 'col'),
			CheckboxColumn::make(entity: 'blocks'),
		];
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id_block'): array
	{
		if (! $this->sql->tableExists('ezp_blocks'))
			return [];

		$select = $this->sql->select()
			->from(['b' => 'ezp_blocks'])
			->columns(['id_block', 'type' => 'blocktype'])
			->join(
				['bl' => 'ezp_block_layout'],
				'b.id_block = bl.id_block',
				['title' => 'customtitle', 'col' => 'id_column']
			)
			->where(['b.blocktype' => $this->supportedTypes])
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
		if (! $this->sql->tableExists('ezp_blocks'))
			return 0;

		$select = $this->sql->select()
			->from('ezp_blocks')
			->columns(['count' => new Expression('COUNT(*)')])
			->where(['blocktype' => $this->supportedTypes]);

		$result = $this->sql->execute($select)->current();

		return $result['count'];
	}

	protected function getItems(array $ids): array
	{
		$select = $this->sql->select()
			->from(['b' => 'ezp_blocks'])
			->columns(['id_block', 'type' => 'blocktype', 'blocktitle'])
			->join(
				['bl' => 'ezp_block_layout'],
				'b.id_block = bl.id_block',
				[
					'title' => 'customtitle',
					'col' => 'id_column',
					'permissions',
					'status' => 'active',
					'content' => 'blockdata',
				]
			)
			->where(['b.blocktype' => $this->supportedTypes]);

		if ($ids !== []) {
			$select->where->in('b.id_block', $ids);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id_block']] = [
				'type'          => $this->getType($row['type']),
				'title'         => $row['title'],
				'content'       => $this->decodeContent($this->decodeContent($row['content'])),
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
		return strtolower($type);
	}

	protected function decodeContent(string $content): string
	{
		return html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	protected function getPlacement(int $col): string
	{
		return match ($col) {
			1 => Placement::LEFT->name(),
			2 => Placement::TOP->name(),
			3 => Placement::RIGHT->name(),
			5 => Placement::BOTTOM->name(),
			default => Placement::HEADER->name(),
		};
	}

	protected function extractPermissions(array $row): int|array
	{
		return array_map('intval', explode(',', (string) $row['permissions']));
	}
}
