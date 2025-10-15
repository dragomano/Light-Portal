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

use Bugo\LightPortal\Database\Migrations\Columns\UnsignedInteger;
use Laminas\Db\Sql\Ddl\Column\ColumnInterface;
use Laminas\Db\Sql\Expression;

if (! defined('SMF'))
	die('No direct access...');

class CategoriesTableUpgrader extends AbstractTableUpgrader
{
	protected string $tableName = 'lp_categories';

	public function upgrade(): void
	{
		$this->migrateData();

		$this->addColumn('slug', ['default' => '']);
		$this->addColumn('parent_id', ['type' => 'int', 'size' => 10, 'default' => 0]);
	}

	protected function defineColumn(string $columnName, array $params = []): ColumnInterface
	{
		$type     = $params['type'] ?? 'varchar';
		$nullable = $params['nullable'] ?? false;
		$default  = $params['default'] ?? null;

		if ($type === 'int') {
			$column = new UnsignedInteger($columnName, $nullable, $default);
			$size   = $params['size'] ?? null;

			if ($size) {
				$column->setOption('length', $size);
			}

			return $column;
		}

		return parent::defineColumn($columnName, $params);
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
