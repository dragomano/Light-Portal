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

namespace Bugo\LightPortal\Database\Operations;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
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

	protected function getQueryData(): array
	{
		$columns = $this->getColumns();
		$values  = $this->getValues();
		$placeholders = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';

		return [$columns, $values, $placeholders];
	}

	protected function buildColumnList(AdapterInterface $adapter, array $columns): string
	{
		$quotedColumns = array_map([$adapter->getPlatform(), 'quoteIdentifier'], $columns);

		return implode(',', $quotedColumns);
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

		$updateClause = implode(', ', array_map(
			fn($col) => sprintf('%1$s = EXCLUDED.%1$s', $adapter->getPlatform()->quoteIdentifier($col)),
			$columns
		));

		$conflictColumns = $this->conflictKeys ?: [$columns[0]];
		$conflictList = implode(',', array_map([$adapter->getPlatform(), 'quoteIdentifier'], $conflictColumns));

		$table = $adapter->getPlatform()->quoteIdentifier($this->table);

		$sql = sprintf(
			/** @lang text */ "INSERT INTO %s (%s) VALUES %s ON CONFLICT (%s) DO UPDATE SET %s",
			$table,
			$columnList,
			$placeholders,
			$conflictList,
			$updateClause
		);

		return $adapter->query($sql, array_values($values));
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

	private function executeBatchReplaceInto(AdapterInterface $adapter): ResultInterface
	{
		$columns = array_keys($this->batchValues[0]);
		$columnList = $this->buildColumnList($adapter, $columns);

		$placeholders = $allValues = [];
		foreach ($this->batchValues as $row) {
			$placeholders[] = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
			$allValues = array_merge($allValues, array_values($row));
		}

		$table = $adapter->getPlatform()->quoteIdentifier($this->table);

		$sql = sprintf(
			"REPLACE INTO %s (%s) VALUES %s",
			$table,
			$columnList,
			implode(',', $placeholders)
		);

		$result = $adapter->query($sql, $allValues);

		$this->resetBatch();

		return $result;
	}

	private function executeBatchUpsert(AdapterInterface $adapter): ResultInterface
	{
		$columns = array_keys($this->batchValues[0]);
		$columnList = $this->buildColumnList($adapter, $columns);

		$placeholders = $allValues = [];
		foreach ($this->batchValues as $row) {
			$placeholders[] = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
			$allValues = array_merge($allValues, array_values($row));
		}

		$updateClause = implode(', ', array_map(
			fn($col) => sprintf('%1$s = EXCLUDED.%1$s', $adapter->getPlatform()->quoteIdentifier($col)),
			$columns
		));

		$conflictColumns = $this->conflictKeys ?: [$columns[0]];
		$conflictList = implode(',', array_map([$adapter->getPlatform(), 'quoteIdentifier'], $conflictColumns));

		$table = $adapter->getPlatform()->quoteIdentifier($this->table);

		$sql = sprintf(
			/** @lang text */ "INSERT INTO %s (%s) VALUES %s ON CONFLICT (%s) DO UPDATE SET %s",
			$table,
			$columnList,
			implode(',', $placeholders),
			$conflictList,
			$updateClause
		);

		$result = $adapter->query($sql, $allValues);

		$this->resetBatch();

		return $result;
	}
}
