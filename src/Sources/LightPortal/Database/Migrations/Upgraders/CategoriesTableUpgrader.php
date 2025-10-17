<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Database\Migrations\Upgraders;

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

		$result = $this->sql->execute($select);

		foreach ($result as $row) {
			$this->migrateRowToTranslations($row['category_id'], 'category', '', $row['description']);
		}

		$this->dropColumn('description');
	}
}
