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

use Bugo\Compat\Config;
use Bugo\LightPortal\Migrations\PortalAdapterFactory;
use Bugo\LightPortal\Migrations\PortalAdapterInterface;
use Bugo\LightPortal\Migrations\PortalSqlInterface;
use Exception;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;
use Laminas\Db\Sql\Ddl\AlterTable;
use Laminas\Db\Sql\Ddl\Column\Column;
use Laminas\Db\Sql\Ddl\Column\ColumnInterface;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Expression;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractTableUpgrader implements TableUpgraderInterface
{
	protected string $tableName;

	protected PortalSqlInterface $sql;

	public function __construct(protected ?PortalAdapterInterface $adapter = null)
	{
		$this->adapter ??= PortalAdapterFactory::create();
		$this->sql = $this->adapter->getSqlBuilder();
	}

	public function updateTable(): void
	{
		if (! $this->tableExists()) {
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
		return $this->adapter->getPrefix() . ($name ?? $this->tableName);
	}

	protected function executeSql($builder): void
	{
		$sqlString = $this->sql->buildSqlString($builder);

		$this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
	}

	protected function alterColumn(
		string $action,
		string $columnName,
		array $params = [],
		?string $newName = null
	): void
	{
		if ($this->columnExists($columnName)) {
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
		$this->alterColumn('drop', $column);
	}

	protected function addIndex(string $indexName, array $columns): void
	{
		$fullTableName = $this->getFullTableName();
		$columnList = implode(', ', $columns);
		$sql = "CREATE INDEX IF NOT EXISTS $indexName ON $fullTableName ($columnList)";

		$this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
	}

	protected function addPrefixIndex(string $indexName, string $column, int $length): void
	{
		$fullTableName = $this->getFullTableName();
		$sql = "CREATE INDEX IF NOT EXISTS $indexName ON $fullTableName ($column($length))";

		$this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
	}

	protected function renameTable(string $new): void
	{
		$oldTable = $this->getFullTableName();
		$newTable = $this->getFullTableName($new);
		$sql = "RENAME TABLE $oldTable TO $newTable";

		$this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
	}

	protected function tableExists(): bool
	{
		$metadata = MetadataFactory::createSourceFromAdapter($this->adapter);

		return in_array($this->getFullTableName(), $metadata->getTableNames());
	}

	protected function columnExists(string $columnName): bool
	{
		try {
			$metadata = MetadataFactory::createSourceFromAdapter($this->adapter);

			return in_array($columnName, $metadata->getColumnNames($this->getFullTableName()));
		} catch (Exception) {
			return false;
		}
	}

	protected function migrateRowToTranslations(int $itemId, string $type, string $content, string $description): void
	{
		$lang = Config::$language ?? 'english';

		$select = $this->sql->select('lp_translations');
		$select->where([
			'item_id' => $itemId,
			'type'    => $type,
			'lang'    => $lang,
		]);

		$select->columns(['count' => new Expression('COUNT(*)')], false);

		$result = $this->sql->prepareStatementForSqlObject($select)->execute();
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

			$this->executeSql($insert);
		}
	}
}
