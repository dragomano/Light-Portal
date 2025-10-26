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

namespace LightPortal\Database;

use Bugo\Compat\ErrorHandler;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;
use Laminas\Db\Sql\PreparableSqlInterface;
use Laminas\Db\Sql\Sql;
use LightPortal\Database\Operations\PortalDelete;
use LightPortal\Database\Operations\PortalInsert;
use LightPortal\Database\Operations\PortalReplace;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\Operations\PortalUpdate;
use Throwable;

if (! defined('SMF'))
	die('No direct access...');

class PortalSql extends Sql implements PortalSqlInterface
{
	/* @var PortalAdapterInterface */
	protected $adapter;

	private readonly string $prefix;

	public function __construct(PortalAdapterInterface $adapter)
	{
		parent::__construct($adapter);

		$this->prefix = $adapter->getPrefix();
	}

	public function getPrefix(): string
	{
		return $this->prefix;
	}

	public function tableExists(string $table): bool
	{
		$platform  = strtolower($this->adapter->getTitle());
		$tableName = $this->prefix . $table;

		try {
			if ($platform === 'sqlite') {
				$result = $this->adapter
					->query(
						/** @lang text */ "SELECT 1 FROM sqlite_master WHERE type = ? AND name = ?",
						['table', $tableName]
					);

				return (bool) $result->current();
			}

			$metadata = MetadataFactory::createSourceFromAdapter($this->adapter);

			return in_array($tableName, $metadata->getTableNames(), true);
		} catch (Throwable) {
			return false;
		}
	}

	public function columnExists(string $table, string $column): bool
	{
		$platform  = strtolower($this->adapter->getTitle());
		$tableName = $this->prefix . $table;

		if ($platform === 'sqlite') {
			try {
				$statement = $this->adapter->query(
					"PRAGMA table_info($tableName)",
					Adapter::QUERY_MODE_EXECUTE
				);
				$result = $statement->execute();

				foreach ($result as $row) {
					if ($row['name'] === $column) {
						return true;
					}
				}

				return false;
			} catch (Throwable) {
				return false;
			}
		}

		try {
			$metadata = MetadataFactory::createSourceFromAdapter($this->adapter);

			return in_array($column, $metadata->getColumnNames($tableName));
		} catch (Throwable) {
			return false;
		}
	}

	public function getAdapter(): PortalAdapterInterface
	{
		return $this->adapter;
	}

	public function getTransaction(): PortalTransactionInterface
	{
		return new PortalTransaction($this->adapter);
	}

	public function select($table = null): PortalSelect
	{
		return new PortalSelect($table, $this->prefix);
	}

	public function insert($table = null, array|string $returning = null): PortalInsert
	{
		return new PortalInsert($table, $this->prefix, $returning);
	}

	public function update($table = null): PortalUpdate
	{
		return new PortalUpdate($table, $this->prefix);
	}

	public function delete($table = null): PortalDelete
	{
		return new PortalDelete($table, $this->prefix);
	}

	public function replace($table = null, array|string $returning = null): PortalReplace
	{
		return new PortalReplace($table, $this->prefix, $returning);
	}

	public function execute(PreparableSqlInterface $sqlObject): ?PortalResultInterface
	{
		try {
			if ($sqlObject instanceof PortalReplace) {
				if ($sqlObject->isBatch()) {
					$result = $sqlObject->executeBatchReplace($this->adapter);
				} else {
					$result = $sqlObject->executeReplace($this->adapter);
				}
			} elseif ($sqlObject instanceof PortalInsert) {
				if ($sqlObject->isBatch()) {
					$result = $sqlObject->executeBatch($this->adapter);
				} else {
					$result = $sqlObject->executeInsert($this->adapter);
				}
			} else {
				$result = $this->prepareStatementForSqlObject($sqlObject)->execute();
			}

			return new PortalResult($result, $this->adapter);
		} catch (Throwable $e) {
			$profiler = $this->adapter->getProfiler();
			$profiles = $profiler->getProfiles();

			$sql  = $profiles[count($profiles) - 1]['sql'] ?? '';
			$file = $e->getTrace()[1]['file'] ?? '';
			$line = $e->getTrace()[1]['line'] ?? '';

			ErrorHandler::log('[LP] queries: ' . $e->getMessage() . PHP_EOL . PHP_EOL . $sql, file: $file, line: $line);
			ErrorHandler::fatal($e->getMessage(), false);
		}

		return null;
	}
}
