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

namespace LightPortal\Database\Operations;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use LightPortal\Database\ResultSetWrapper;
use RuntimeException;

if (! defined('SMF'))
	die('No direct access...');

class PortalReplace extends PortalInsert
{
	protected array $conflictKeys = [];

	public function setConflictKeys(array $keys): static
	{
		$this->conflictKeys = $keys;

		return $this;
	}

	public function executeReplace(AdapterInterface $adapter): ResultInterface
	{
		$platform = $adapter->getPlatform()->getName();

		return match ($platform) {
			'MySQL', 'SQLite' => $this->executeReplaceInto($adapter),
			'PostgreSQL' => $this->executeUpsert($adapter),
			default => throw new RuntimeException("REPLACE operation not supported for platform: $platform"),
		};
	}

	public function executeBatchReplace(AdapterInterface $adapter): ResultInterface
	{
		if (empty($this->batchValues)) {
			return $adapter->query('SELECT 1 WHERE 0 = 1', []);
		}

		$platform = $adapter->getPlatform()->getName();

		return match ($platform) {
			'MySQL', 'SQLite' => $this->executeBatchReplaceInto($adapter),
			'PostgreSQL' => $this->executeBatchUpsert($adapter),
			default => throw new RuntimeException("Batch REPLACE operation not supported for platform: $platform"),
		};
	}

	protected function getQueryData(): array
	{
		$columns = $this->getColumns();
		$values  = $this->getValues();
		$placeholders = $this->buildPlaceholders(count($columns));

		return [$columns, $values, $placeholders];
	}

	protected function getBatchQueryData(): array
	{
		$columns = array_keys($this->batchValues[0]);
		$placeholders = $allValues = [];

		foreach ($this->batchValues as $row) {
			$placeholders[] = $this->buildPlaceholders(count($columns));
			$allValues = array_merge($allValues, array_values($row));
		}

		return [$columns, $placeholders, $allValues];
	}

	protected function buildPlaceholders(int $count): string
	{
		return '(' . implode(',', array_fill(0, $count, '?')) . ')';
	}

	protected function buildColumnList(AdapterInterface $adapter, array $columns): string
	{
		$quotedColumns = array_map([$adapter->getPlatform(), 'quoteIdentifier'], $columns);

		return implode(',', $quotedColumns);
	}

	protected function buildUpdateClause(AdapterInterface $adapter, array $columns): string
	{
		return implode(', ', array_map(
			fn($col) => sprintf('%1$s = EXCLUDED.%1$s', $adapter->getPlatform()->quoteIdentifier($col)),
			$columns
		));
	}

	protected function buildConflictClause(AdapterInterface $adapter, array $defaultColumns): string
	{
		$conflictColumns = $this->conflictKeys ?: $defaultColumns;

		return implode(',', array_map([$adapter->getPlatform(), 'quoteIdentifier'], $conflictColumns));
	}

	private function executeReplaceInto(AdapterInterface $adapter): ResultInterface
	{
		[$columns, $values, $placeholders] = $this->getQueryData();
		$columnList = $this->buildColumnList($adapter, $columns);

		$table = $adapter->getPlatform()->quoteIdentifier($this->table);

		$sql = sprintf(
			"REPLACE INTO %s (%s) VALUES %s",
			$table,
			$columnList,
			$placeholders
		);

		return $adapter->query($sql, array_values($values));
	}

	private function executeUpsert(AdapterInterface $adapter): ResultInterface
	{
		[$columns, $values, $placeholders] = $this->getQueryData();
		$columnList = $this->buildColumnList($adapter, $columns);

		$updateClause = $this->buildUpdateClause($adapter, $columns);
		$conflictList = $this->buildConflictClause($adapter, [$columns[0]]);

		$table = $adapter->getPlatform()->quoteIdentifier($this->table);

		$sql = sprintf(
			/** @lang text */ "INSERT INTO %s (%s) VALUES %s ON CONFLICT (%s) DO UPDATE SET %s%s",
			$table,
			$columnList,
			$placeholders,
			$conflictList,
			$updateClause,
			$this->getReturning($adapter)
		);

		$result = $adapter->query($sql, array_values($values));

		if ($this->returning && $result instanceof ResultSet) {
			return new ResultSetWrapper($result);
		}

		return $result;
	}

	private function executeBatchReplaceInto(AdapterInterface $adapter): ResultInterface
	{
		[$columns, $placeholders, $allValues] = $this->getBatchQueryData();
		$columnList = $this->buildColumnList($adapter, $columns);

		$table = $adapter->getPlatform()->quoteIdentifier($this->table);

		$sql = sprintf(
			"REPLACE INTO %s (%s) VALUES %s%s",
			$table,
			$columnList,
			implode(',', $placeholders),
			$this->getReturning($adapter)
		);

		$queryResult = $adapter->query($sql, $allValues);

		if ($this->returning && $queryResult instanceof ResultSet) {
			return new ResultSetWrapper($queryResult);
		}

		return $queryResult;
	}

	private function executeBatchUpsert(AdapterInterface $adapter): ResultInterface
	{
		[$columns, $placeholders, $allValues] = $this->getBatchQueryData();
		$columnList = $this->buildColumnList($adapter, $columns);

		$updateClause = $this->buildUpdateClause($adapter, $columns);
		$conflictList = $this->buildConflictClause($adapter, [$columns[0]]);

		$table = $adapter->getPlatform()->quoteIdentifier($this->table);

		$sql = sprintf(
			/** @lang text */ "INSERT INTO %s (%s) VALUES %s ON CONFLICT (%s) DO UPDATE SET %s%s",
			$table,
			$columnList,
			implode(',', $placeholders),
			$conflictList,
			$updateClause,
			$this->getReturning($adapter)
		);

		$result = $adapter->query($sql, $allValues);

		$this->resetBatch();

		if ($this->returning && $result instanceof ResultSet) {
			return new ResultSetWrapper($result);
		}

		return $result;
	}
}
