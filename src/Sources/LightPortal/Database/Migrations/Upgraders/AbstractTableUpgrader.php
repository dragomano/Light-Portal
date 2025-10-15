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

use Bugo\Compat\Config;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Ddl\AlterTable;
use Laminas\Db\Sql\Ddl\Column\Column;
use Laminas\Db\Sql\Ddl\Column\ColumnInterface;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\SqlInterface;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractTableUpgrader implements TableUpgraderInterface
{
	protected string $tableName;

	public function __construct(protected PortalSqlInterface $sql) {}

	public function updateTable(): void
	{
		if (! $this->sql->tableExists($this->tableName)) {
			return;
		}

		$this->upgrade();
	}

	protected function defineColumn(string $columnName, array $params = []): ColumnInterface
	{
		$type     = $params['type'] ?? 'varchar';
		$size     = $params['size'] ?? 255;
		$nullable = $params['nullable'] ?? false;
		$default  = $params['default'] ?? null;

		$column = match ($type) {
			'varchar' => new Varchar($columnName, $size, $nullable, $default),
			'int'     => new Integer($columnName, $nullable, $default),
			default   => new Column($columnName, $nullable, $default, options: ['type' => $type]),
		};

		if ($type === 'int' && $size) {
			$column->setOption('length', $size);
		}

		return $column;
	}

	protected function getFullTableName(?string $name = null): string
	{
		return $this->sql->getPrefix() . ($name ?? $this->tableName);
	}

	protected function executeSql(SqlInterface $builder): void
	{
		$sqlString = $this->sql->buildSqlString($builder);

		$this->sql->getAdapter()->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
	}

	protected function alterColumn(
		string $action,
		string $columnName,
		array $params = [],
		?string $newName = null
	): void
	{
		$exists = $this->sql->columnExists($this->tableName, $columnName);

		if (($action === 'add' && $exists) || ($action === 'drop' && ! $exists)) {
			return;
		}

		if ($action === 'change' && ! $exists) {
			return;
		}

		$alter = new AlterTable($this->getFullTableName());

		if ($action === 'add') {
			$alter->addColumn($this->defineColumn($columnName, $params));
		} elseif ($action === 'change') {
			$alter->changeColumn($columnName, $this->defineColumn($newName, $params));
		} elseif ($action === 'drop') {
			$alter->dropColumn($columnName);
		}

		$this->executeSql($alter);
	}

	protected function addColumn(string $columnName, array $params = []): void
	{
		$this->alterColumn('add', $columnName, $params);
	}

	protected function changeColumn(string $oldName, string $newName, array $params = []): void
	{
		$this->alterColumn('change', $oldName, $params, $newName);
	}

	protected function dropColumn(string $column): void
	{
		if ($this->isSqlite()) {
			$this->dropColumnSqlite($column);
		}

		$this->alterColumn('drop', $column);
	}

	protected function addIndex(string $indexName, array $columns): void
	{
		$columnList = implode(', ', $columns);
		$this->createIndexIfNotExists($indexName, $columnList);
	}

	protected function addPrefixIndex(string $indexName, string $column, int $length): void
	{
		$platformName = strtolower($this->sql->getAdapter()->getTitle());

		$expr = match ($platformName) {
			'postgresql' => "substring($column FROM 1 FOR $length)",
			'sqlite'     => "substr($column, 1, $length)",
			default      => "$column($length)",
		};

		$this->createIndexIfNotExists($indexName, $expr);
	}

	protected function renameTable(string $new): void
	{
		$oldTable = $this->getFullTableName();
		$newTable = $this->getFullTableName($new);
		$sql = "ALTER TABLE $oldTable RENAME TO $newTable";

		$this->sql->getAdapter()->query($sql, Adapter::QUERY_MODE_EXECUTE);
	}

	protected function migrateRowToTranslations(int $itemId, string $type, string $content, string $description): void
	{
		$lang = Config::$language ?? 'english';

		$select = $this->sql->select('lp_translations');
		$select->columns(['count' => new Expression('COUNT(*)')], false);
		$select->where([
			'item_id' => $itemId,
			'type'    => $type,
			'lang'    => $lang,
		]);

		$result = $this->sql->execute($select);

		$row = $result->current();

		if ($row['count'] == 0) {
			$insert = $this->sql->insert('lp_translations');
			$insert->values([
				'item_id'     => $itemId,
				'type'        => $type,
				'lang'        => $lang,
				'content'     => $content,
				'description' => $description,
			]);

			$this->sql->execute($insert);
		}
	}

	protected function isSqlite(): bool
	{
		return strtolower($this->sql->getAdapter()->getTitle()) === 'sqlite';
	}

	protected function dropColumnSqlite(string $columnName): void
	{
		$fullTableName = $this->getFullTableName();

		$columns = $this->sql->getAdapter()->query(
			"PRAGMA table_info($fullTableName)",
			Adapter::QUERY_MODE_EXECUTE
		);

		$columnNames = $columnDefs = [];
		foreach ($columns as $column) {
			if ($column['name'] === $columnName) {
				continue;
			}

			$columnNames[] = $column['name'];

			$def = $column['name'] . ' ' . $column['type'];
			if ($column['notnull']) {
				$def .= ' NOT NULL';
			}

			if ($column['dflt_value'] !== null) {
				$def .= ' DEFAULT ' . $column['dflt_value'];
			}

			if ($column['pk']) {
				$def .= ' PRIMARY KEY';
			}

			$columnDefs[] = $def;
		}

		$tempTable = $fullTableName . '_temp_' . time();

		$createSql = "CREATE TABLE $tempTable (" . implode(', ', $columnDefs) . ")";
		$this->sql->getAdapter()->query($createSql, Adapter::QUERY_MODE_EXECUTE);

		$selectColumns = implode(', ', $columnNames);
		$insertSql = "INSERT INTO $tempTable ($selectColumns) SELECT $selectColumns FROM $fullTableName";
		$this->sql->getAdapter()->query($insertSql, Adapter::QUERY_MODE_EXECUTE);

		$this->sql->getAdapter()->query(
			"DROP TABLE $fullTableName",
			Adapter::QUERY_MODE_EXECUTE
		);
		$this->sql->getAdapter()->query(
			"ALTER TABLE $tempTable RENAME TO " . str_replace($this->sql->getPrefix(), '', $fullTableName),
			Adapter::QUERY_MODE_EXECUTE
		);
	}

	private function createIndexIfNotExists(string $indexName, string $columnsExpr): void
	{
		$fullTableName = $this->getFullTableName();
		$platformName = strtolower($this->sql->getAdapter()->getTitle());

		$quotedIndexName = $indexName;
		if ($platformName === 'mysql') {
			$quotedIndexName = $this->sql->getAdapter()->getPlatform()->quoteValue($indexName);
		}

		if ($platformName === 'mysql') {
			$checkSql = "SHOW INDEX FROM $fullTableName WHERE Key_name = $quotedIndexName";
			$result = $this->sql->getAdapter()->query($checkSql, Adapter::QUERY_MODE_EXECUTE);

			if (empty($result->toArray())) {
				$sql = "CREATE INDEX $quotedIndexName ON $fullTableName ($columnsExpr)";
				$this->sql->getAdapter()->query($sql, Adapter::QUERY_MODE_EXECUTE);
			}
		} else {
			$sql = "CREATE INDEX IF NOT EXISTS $quotedIndexName ON $fullTableName ($columnsExpr)";
			$this->sql->getAdapter()->query($sql, Adapter::QUERY_MODE_EXECUTE);
		}
	}
}

