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
use Bugo\LightPortal\Migrations\Columns\MediumText;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;
use Laminas\Db\Sql\Ddl\AlterTable;
use Laminas\Db\Sql\Ddl\Column\Column;
use Laminas\Db\Sql\Ddl\Column\ColumnInterface;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Select;

class TitlesTableUpgrader extends AbstractTableUpgrader
{
	public function upgrade(): void
	{
		if (! $this->tableExists('titles')) {
			return;
		}

		$this->addColumn('titles', 'content', 'mediumtext');
		$this->addColumn('titles', 'description', 'varchar', 510);

		$this->renameColumn('titles', 'value', 'title');

		$this->migratePagesData();
		$this->migrateBlocksData();
		$this->migrateCategoriesData();

		$this->renameTable('titles', 'translations');

		$this->addIndex('translations', 'idx_translations_entity', ['type', 'item_id', 'lang']);
		$this->addPrefixIndex($this->getTableName('translations'), 'title_prefix', 'title', 100);
	}

	protected function addColumn(string $table, string $columnName, string $type, int $size = null): void
	{
		$alter = new AlterTable($this->getTableName($table));
		$alter->addColumn($this->defineColumn($columnName, $type, $size));

		$this->executeSql($alter);
	}

	protected function renameColumn(string $table, string $oldName, string $newName): void
	{
		$sql = "ALTER TABLE " . $this->getTableName($table) . " CHANGE $oldName $newName VARCHAR(255) DEFAULT NULL";

		$this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
	}

	protected function defineColumn(string $columnName, string $type, int $size = null): ColumnInterface
	{
		return match ($type) {
			'varchar'    => new Varchar($columnName, $size, true),
			'mediumtext' => new MediumText($columnName, nullable: true),
			'int'        => new Integer($columnName, false),
			default      => new Column($columnName, $type, true),
		};
	}

	protected function migratePagesData(): void
	{
		$select = new Select($this->getTableName('pages'));
		$select->columns([
			'page_id',
			'content' => new Expression("COALESCE(content, '')"),
			'description' => new Expression("COALESCE(description, '')"),
		]);

		$result = $this->sql->prepareStatementForSqlObject($select)->execute();

		foreach ($result as $row) {
			$this->migrateRowToTitles($row['page_id'], 'page', $row['content'], $row['description']);
		}

		$this->removeColumn('pages', 'content');
		$this->removeColumn('pages', 'description');
	}
	protected function renameTable(string $old, string $new): void
	{
		$oldTable = $this->adapter->getPrefix() . 'lp_' . $old;
		$newTable = $this->adapter->getPrefix() . 'lp_' . $new;
		$sql = "RENAME TABLE $oldTable TO $newTable";

		$this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
	}

	protected function addIndex(string $table, string $indexName, array $columns): void
	{
		$columnList = implode(', ', $columns);
		$sql = "CREATE INDEX $indexName ON {$this->getTableName($table)} ($columnList)";

		$this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
	}

	protected function addPrefixIndex(string $fullTableName, string $indexName, string $column, int $length): void
	{
		$sql = "CREATE INDEX $indexName ON $fullTableName ($column($length))";

		$this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
	}

	protected function removeColumn(string $table, string $column): void
	{
		$alter = new AlterTable($this->getTableName($table));
		$alter->dropColumn($column);

		$this->executeSql($alter);
	}

	protected function migrateBlocksData(): void
	{
		$select = new Select($this->getTableName('blocks'));
		$select->columns([
			'block_id',
			'content' => new Expression("COALESCE(content, '')"),
			'description' => new Expression("COALESCE(note, '')"),
		]);

		$result = $this->sql->prepareStatementForSqlObject($select)->execute();

		foreach ($result as $row) {
			$this->migrateRowToTitles($row['block_id'], 'block', $row['content'], $row['description']);
		}

		$this->removeColumn('blocks', 'content');
		$this->removeColumn('blocks', 'note');
	}

	protected function migrateCategoriesData(): void
	{
		$select = new Select($this->getTableName('categories'));
		$select->columns([
			'category_id',
			'description' => new Expression("COALESCE(description, '')"),
		]);

		$result = $this->sql->prepareStatementForSqlObject($select)->execute();

		foreach ($result as $row) {
			$this->migrateRowToTitles($row['category_id'], 'category', '', $row['description']);
		}

		$this->removeColumn('categories', 'description');
	}

	protected function migrateRowToTitles(int $itemId, string $type, string $content, string $description): void
	{
		$insert = new Insert($this->getTableName('titles'));
		$insert->values([
			'item_id'     => $itemId,
			'type'        => $type,
			'lang'        => $this->getLanguage(),
			'content'     => $content,
			'description' => $description,
		]);

		$this->executeSql($insert);
	}

	protected function tableExists(string $tableName): bool
	{
		$metadata = MetadataFactory::createSourceFromAdapter($this->adapter);

		return in_array($this->getTableName($tableName), $metadata->getTableNames());
	}

	protected function getLanguage(): string
	{
		return Config::$language ?? 'english';
	}
}
