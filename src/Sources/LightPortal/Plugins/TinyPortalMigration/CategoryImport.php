<?php declare(strict_types=1);

/**
 * @package TinyPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 08.11.25
 */

namespace LightPortal\Plugins\TinyPortalMigration;

use Laminas\Db\Sql\Expression;
use LightPortal\DataHandlers\Imports\Database\AbstractDatabaseCategoryImport;
use LightPortal\UI\Tables\CheckboxColumn;
use LightPortal\UI\Tables\TitleColumn;

if (! defined('LP_NAME'))
	die('No direct access...');

class CategoryImport extends AbstractDatabaseCategoryImport
{
	protected string $langKey = 'lp_tiny_portal_migration';

	protected string $formAction = 'import_from_tp';

	protected string $uiTableId = 'tp_categories';

	protected function defineUiColumns(): array
	{
		return [
			TitleColumn::make()
				->setData('title', 'word_break'),
			CheckboxColumn::make(entity: 'categories'),
		];
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id'): array
	{
		if (! $this->sql->tableExists('tp_variables')) {
			return [];
		}

		$select = $this->sql->select()
			->from('tp_variables')
			->columns(['id', 'title' => 'value1'])
			->where(['type' => 'category'])
			->order($sort)
			->limit($limit)
			->offset($start);

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id']] = [
				'id'    => $row['id'],
				'title' => $row['title'],
			];
		}

		return $items;
	}

	public function getTotalCount(): int
	{
		if (! $this->sql->tableExists('tp_variables')) {
			return 0;
		}

		$select = $this->sql->select()
			->from('tp_variables')
			->columns(['count' => new Expression('COUNT(*)')])
			->where(['type' => 'category']);

		$result = $this->sql->execute($select)->current();

		return (int) $result['count'];
	}

	protected function getItems(array $ids): array
	{
		$select = $this->sql->select()
			->from('tp_variables')
			->columns(['id', 'title' => 'value1'])
			->where(['type' => 'category']);

		if ($ids !== []) {
			$select->where->in('id', $ids);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id']] = [
				'title'       => $row['title'],
				'parent_id'   => 0,
				'slug'        => $this->generateSlug(['english' => $row['title']]),
				'icon'        => '',
				'description' => '',
				'priority'    => 0,
				'status'      => 1,
			];
		}

		return $items;
	}
}
