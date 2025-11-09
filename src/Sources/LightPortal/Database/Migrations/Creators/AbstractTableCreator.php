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

namespace LightPortal\Database\Migrations\Creators;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Ddl\DropTable;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\SqlInterface;
use LightPortal\Database\Migrations\PortalTable;
use LightPortal\Database\PortalSqlInterface;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractTableCreator implements TableCreatorInterface
{
	protected string $tableName;

	private ?PortalTable $table = null;

	public function __construct(protected PortalSqlInterface $sql) {}

	public function createTable(): void
	{
		if ($this->sql->tableExists($this->tableName)) {
			return;
		}

		$this->table = new PortalTable($this->getFullTableName());
		$this->defineColumns($this->table);
		$this->executeSql($this->table);

		$adapter = $this->sql->getAdapter();

		foreach ($this->table->getIndexSqlStatements($adapter->getPlatform()) as $indexSql) {
			$adapter->query($indexSql, Adapter::QUERY_MODE_EXECUTE);
		}
	}

	public function getSql(): string
	{
		if ($this->table === null) {
			$this->table = new PortalTable($this->getFullTableName());
			$this->defineColumns($this->table);
		}

		return $this->sql->buildSqlString($this->table);
	}

	public function insertDefaultData(): void
	{
		$data = $this->getDefaultData();

		if (empty($data)) {
			return;
		}

		$this->insertDefaultIfNotExists(...$data);
	}

	public function dropTable(): void
	{
		if (! $this->sql->tableExists($this->tableName)) {
			return;
		}

		$dropTable = new DropTable($this->getFullTableName());
		$this->executeSql($dropTable);
	}

	abstract protected function defineColumns(PortalTable $table): void;

	abstract protected function getDefaultData(): array;

	protected function getFullTableName(): string
	{
		return $this->sql->getPrefix() . $this->tableName;
	}

	protected function executeSql(SqlInterface $builder): void
	{
		$sqlString = $this->sql->buildSqlString($builder);

		$this->sql->getAdapter()->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
	}

	protected function insertDefaultIfNotExists(array $where, array $columns, array $values): void
	{
		$select = $this->sql->select($this->tableName);
		$select->columns(['count' => new Expression('COUNT(*)')], false);
		$select->where($where);

		$row = $this->sql->execute($select)->current();

		if ($row['count'] == 0) {
			$insert = $this->sql->insert($this->tableName);

			if (is_array($values[0])) {
				foreach ($values as $value) {
					$insert->columns($columns)->values($value);
					$this->sql->execute($insert);
				}

				return;
			}

			$insert->columns($columns)->values($values);
			$this->sql->execute($insert);
		}
	}
}
