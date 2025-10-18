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

namespace LightPortal\Database\Migrations;

use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Adapter\Platform\Sql92;
use Laminas\Db\Sql\Ddl\Column\ColumnInterface;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use Laminas\Db\Sql\Ddl\Constraint\UniqueKey;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\Index\Index;

if (! defined('SMF'))
	die('No direct access...');

class PortalTable extends CreateTable
{
	public function addAutoIncrementColumn(ColumnInterface $column): static
	{
		$this->addColumn($column);
		$this->addPrimaryKey($column->getName());

		return $this;
	}

	public function addPrimaryKey(string $column): static
	{
		$pk = new PrimaryKey($column);
		$this->addConstraint($pk);

		return $this;
	}

	public function addUniqueColumn(ColumnInterface $column, string $indexName = null): static
	{
		$this->addColumn($column);
		$this->addUniqueKey($column->getName(), $indexName);

		return $this;
	}

	public function addUniqueKey(string $column, string $name = null): static
	{
		$uk = new UniqueKey($column);

		if ($name) {
			$uk->setName($name);
		}

		$this->addConstraint($uk);

		return $this;
	}

	public function addIndex(array $columns, string $name): static
	{
		$index = new Index($columns, $name);
		$this->addConstraint($index);

		return $this;
	}

	public function getSqlString(PlatformInterface|null $adapterPlatform = null): string
	{
		$platform = $adapterPlatform ?? new Sql92();
		$platformName = strtolower($platform->getName());

		if ($platformName === 'sqlite') {
			$originalConstraints = $this->constraints;
			$filteredConstraints = $indexes = [];

			foreach ($this->constraints as $constraint) {
				if ($constraint instanceof Index) {
					$indexes[] = $constraint;
				} else {
					$filteredConstraints[] = $constraint;
				}
			}

			$this->constraints = $filteredConstraints;
			$createSql = parent::getSqlString($adapterPlatform);
			$this->constraints = $originalConstraints;

			$indexStatements = [];
			foreach ($indexes as $index) {
				$indexName = $platform->quoteIdentifier($index->getName());
				$tableName = $platform->quoteIdentifier($this->table);
				$quotedColumns = array_map(fn($col) => $platform->quoteIdentifier($col), $index->getColumns());
				$columnList = implode(', ', $quotedColumns);
				$indexStatements[] = "CREATE INDEX IF NOT EXISTS $indexName ON $tableName ($columnList)";
			}

			return $createSql . (empty($indexStatements) ? '' : '; ' . implode('; ', $indexStatements));
		}

		return parent::getSqlString($adapterPlatform);
	}
}
