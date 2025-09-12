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

namespace Bugo\LightPortal\Migrations\Upgraders;

use Bugo\LightPortal\Migrations\Columns\UnsignedInteger;
use Laminas\Db\Sql\Ddl\AlterTable;
use Laminas\Db\Sql\Ddl\Column\Column;
use Laminas\Db\Sql\Ddl\Column\ColumnInterface;
use Laminas\Db\Sql\Ddl\Column\Varchar;

class CategoriesUpgradeTask extends AbstractTableUpgrader
{
	public function upgrade(): void
	{
		$this->addColumn('categories', 'slug', 'varchar', 255);
		$this->addColumn('categories', 'parent_id', 'int', 10, 0);
	}

	protected function addColumn(
		string $table,
		string $columnName,
		string $type,
		int $size = null,
		int $default = null
	): void
	{
		if ($this->columnExists($table, $columnName)) {
			return;
		}

		$alter = new AlterTable($this->getTableName($table));
		$alter->addColumn($this->defineColumn($columnName, $type, $size, $default));

		$this->executeSql($alter);
	}

	protected function defineColumn(
		string $columnName,
		string $type,
		int $size = null,
		int $default = null
	): ColumnInterface
	{
		$column = match ($type) {
			'varchar' => new Varchar($columnName, $size),
			'int'     => new UnsignedInteger($columnName, default: $default),
			default   => new Column($columnName, default: $default, options: ['type' => $type]),
		};

		if ($type === 'int' && $size) {
			$column->setOption('length', $size);
		}

		return $column;
	}
}
