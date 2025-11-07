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
use Laminas\Db\Adapter\Platform\Sql92 as DefaultAdapterPlatform;
use Laminas\Db\Sql\Ddl\Column\ColumnInterface;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use Laminas\Db\Sql\Ddl\Constraint\UniqueKey;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\Index\Index;

if (! defined('SMF'))
	die('No direct access...');

class PortalTable extends CreateTable
{
	private array $separateIndexes = [];


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

	public function addIndex(string $name, array $columns): static
	{
		$index = new Index($columns, $name);
		$this->addConstraint($index);

		return $this;
	}

	public function getSqlString(PlatformInterface|null $adapterPlatform = null): string
	{
		$platform = $adapterPlatform ?? new DefaultAdapterPlatform();
		$platformName = strtolower($platform->getName());

		if (in_array($platformName, ['sqlite', 'postgresql'])) {
			$originalConstraints = $this->constraints;
			$filteredConstraints = [];
			$this->separateIndexes = [];

			foreach ($this->constraints as $constraint) {
				if ($constraint instanceof Index) {
					$this->separateIndexes[] = $constraint;
				} else {
					$filteredConstraints[] = $constraint;
				}
			}

			$this->constraints = $filteredConstraints;
			$createSql = parent::getSqlString($adapterPlatform);
			$this->constraints = $originalConstraints;

			return $createSql;
		}

		return parent::getSqlString($adapterPlatform);
	}

	public function getIndexSqlStatements(PlatformInterface|null $adapterPlatform = null): array
	{
		$platform = $adapterPlatform ?? new DefaultAdapterPlatform();
		$platformName = strtolower($platform->getName());

		if (! in_array($platformName, ['sqlite', 'postgresql'])) {
			return [];
		}

		if (empty($this->separateIndexes)) {
			foreach ($this->constraints as $constraint) {
				if ($constraint instanceof Index) {
					$this->separateIndexes[] = $constraint;
				}
			}
		}

		$indexStatements = [];
		foreach ($this->separateIndexes as $index) {
			$indexName = $platform->quoteIdentifier($index->getName());
			$tableName = $platform->quoteIdentifier($this->table);
			$quotedColumns = array_map($platform->quoteIdentifier(...), $index->getColumns());
			$columnList = implode(', ', $quotedColumns);
			$indexStatements[] = "CREATE INDEX $indexName ON $tableName ($columnList)";
		}

		return $indexStatements;
	}
}
