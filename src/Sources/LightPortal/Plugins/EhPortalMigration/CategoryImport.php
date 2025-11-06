<?php declare(strict_types=1);

/**
 * @package EhPortalMigration (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 09.10.25
 */

namespace LightPortal\Plugins\EhPortalMigration;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Lang;
use Laminas\Db\Sql\Expression;
use LightPortal\DataHandlers\Imports\Database\AbstractDatabaseCategoryImport;
use LightPortal\UI\Tables\CheckboxColumn;
use LightPortal\UI\Tables\TitleColumn;

if (! defined('LP_NAME'))
	die('No direct access...');

class CategoryImport extends AbstractDatabaseCategoryImport
{
	protected string $langKey = 'lp_eh_portal_migration';

	protected string $formAction = 'import_from_ep';

	protected string $uiTableId = 'ep_categories';

	protected function defineUiColumns(): array
	{
		return [
			TitleColumn::make()
				->setData('title', 'word_break'),
			Column::make('status', Lang::$txt['status'])
				->setData('status', 'centertext')
				->setSort('status DESC', 'status'),
			CheckboxColumn::make(entity: 'categories'),
		];
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'id_category'): array
	{
		if (! $this->sql->tableExists('sp_categories'))
			return [];

		$select = $this->sql->select()
			->from('sp_categories')
			->columns(['id_category', 'title' => 'name', 'publish'])
			->order($sort)
			->limit($limit)
			->offset($start);

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id_category']] = [
				'id'     => $row['id_category'],
				'title'  => $row['title'],
				'status' => $row['publish'],
			];
		}

		return $items;
	}

	public function getTotalCount(): int
	{
		if (! $this->sql->tableExists('sp_categories'))
			return 0;

		$select = $this->sql->select()
			->from('sp_categories')
			->columns(['count' => new Expression('COUNT(*)')]);

		$result = $this->sql->execute($select)->current();

		return $result['count'];
	}

	protected function getItems(array $ids): array
	{
		$select = $this->sql->select()
			->from('sp_categories')
			->columns(['id_category', 'title' => 'name', 'publish']);

		if ($ids !== []) {
			$select->where->in('id_category', $ids);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['id_category']] = [
				'title'       => $row['title'],
				'parent_id'   => 0,
				'slug'        => $this->generateSlug(['english' => $row['title']]),
				'icon'        => '',
				'description' => '',
				'priority'    => 0,
				'status'      => $row['publish'],
			];
		}

		return $items;
	}
}
