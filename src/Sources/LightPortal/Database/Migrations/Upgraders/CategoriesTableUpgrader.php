<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Database\Migrations\Upgraders;

use Laminas\Db\Sql\Expression;

if (! defined('SMF'))
	die('No direct access...');

class CategoriesTableUpgrader extends AbstractTableUpgrader
{
	protected string $tableName = 'lp_categories';

	public function upgrade(): void
	{
		$this->migrateData();

		$this->addColumn('slug', ['nullable' => true]);
		$this->addColumn('parent_id', ['unsigned' => true, 'type' => 'int', 'size' => 10, 'default' => 0]);
	}

	protected function migrateData(): void
	{
		$select = $this->sql->select($this->tableName);
		$select->columns([
			'category_id',
			'description' => new Expression("COALESCE(description, '')"),
		]);

		$rows = $this->sql->execute($select);

		$this->migrateRowsToTranslations('category_id', 'category', $rows);

		$this->dropColumn('description');
	}
}
