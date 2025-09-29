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

namespace Bugo\LightPortal\Migrations\Creators;

use Bugo\LightPortal\Migrations\PortalAdapterInterface;
use Bugo\LightPortal\Migrations\PortalSqlInterface;
use Bugo\LightPortal\Migrations\PortalTable;
use Bugo\LightPortal\Migrations\PortalAdapterFactory;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;
use Laminas\Db\Sql\Ddl\DropTable;
use Laminas\Db\Sql\Expression;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractTableCreator implements TableCreatorInterface
{
	protected string $tableName;

	protected PortalSqlInterface $sql;

	private ?PortalTable $table = null;

	public function __construct(protected ?PortalAdapterInterface $adapter = null)
	{
		$this->adapter ??= PortalAdapterFactory::create();
		$this->sql = $this->adapter->getSqlBuilder();
	}

	public function createTable(): void
	{
		if ($this->tableExists()) {
			return;
		}

		$this->table = new PortalTable($this->getFullTableName());
		$this->defineColumns($this->table);
		$this->executeSql($this->table);
	}

	public function getSql(): string
	{
		if ($this->table === null) {
			$this->table = new PortalTable($this->getFullTableName());
			$this->defineColumns($this->table);
		}

		return $this->sql->buildSqlString($this->table);
	}

	public function insertDefaultData(): void {}

	public function dropTable(): void
	{
		if (! $this->tableExists()) {
			return;
		}

		$dropTable = new DropTable($this->getFullTableName());
		$this->executeSql($dropTable);
	}

	abstract protected function defineColumns(PortalTable $table): void;

	protected function getFullTableName(): string
	{
		return $this->adapter->getPrefix() . $this->tableName;
	}

	protected function tableExists(): bool
	{
		$metadata = MetadataFactory::createSourceFromAdapter($this->adapter);

		return in_array($this->getFullTableName(), $metadata->getTableNames());
	}

	protected function executeSql($builder): void
	{
		$sqlString = $this->sql->buildSqlString($builder);

		$this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
	}

	protected function insertDefaultIfNotExists(array $where, array $columns, array $values): void
	{
		$select = $this->sql->select($this->tableName);
		$select->where($where);
		$select->columns(['count' => new Expression('COUNT(*)')], false);

		$statement = $this->sql->prepareStatementForSqlObject($select);
		$result = $statement->execute();
		$row = $result->current();

		if ($row['count'] == 0) {
			$insert = $this->sql->insert($this->tableName);
			$insert->columns($columns)->values($values);

			$statement = $this->sql->prepareStatementForSqlObject($insert);
			$statement->execute();
		}
	}
}
