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

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use PDOStatement;
use Throwable;

readonly class PortalResult implements PortalResultInterface
{
	public function __construct(private ResultInterface $result, private AdapterInterface $adapter) {}

	public function getGeneratedValue(string $name = 'id')
	{
		$driver = strtolower($this->adapter->getDriver()->getDatabasePlatformName());

		if ($driver === 'postgresql') {
			if ($this->result->isQueryResult()) {
				$this->result->rewind();
				$row = $this->result->current();

				if ($row && isset($row[$name])) {
					return $row[$name];
				}
			}
		}

		return $this->result->getGeneratedValue($name);
	}

	public function getGeneratedValues(string $name = 'id'): array
	{
		$values = [];

		if ($this->result->isQueryResult()) {
			$this->result->rewind();

			while ($this->result->valid()) {
				$row = $this->result->current();
				if ($row && isset($row[$name])) {
					$values[] = $row[$name];
				}

				$this->result->next();
			}
		} else {
			$singleValue = $this->result->getGeneratedValue($name);
			if ($singleValue !== null) {
				$values[] = $singleValue;
			}

			$tableName = $this->extractTableNameFromQuery();
			$count = $this->result->count();

			if ($tableName && $count > 1 && $singleValue !== null) {
				try {
					$result = $this->adapter->query(
						"SELECT $name FROM $tableName WHERE $name BETWEEN ? AND ?",
						[$singleValue, $singleValue + $count - 1]
					);

					$values = array_column($result->toArray(), $name);
				} catch (Throwable) {}
			}
		}

		return $values;
	}

	private function extractTableNameFromQuery(): ?string
	{
		$resource = $this->result->getResource();

		if ($resource instanceof PDOStatement) {
			$queryString = $resource->queryString ?? '';

			if (preg_match('/(?:INSERT|REPLACE)\s+INTO\s+`?(\w+)`?/i', $queryString, $matches)) {
				return $matches[1];
			}
		}

		return null;
	}

	public function count(): int
	{
		return $this->result->count();
	}

	public function current(): mixed
	{
		return $this->result->current();
	}

	public function next(): void
	{
		$this->result->next();
	}

	public function key(): mixed
	{
		return $this->result->key();
	}

	public function valid(): bool
	{
		return $this->result->valid();
	}

	public function rewind(): void
	{
		$this->result->rewind();
	}

	public function getAffectedRows(): int
	{
		return $this->result->getAffectedRows();
	}

	public function getResource()
	{
		return $this->result->getResource();
	}

	public function buffer() {
		return $this->result->buffer();
	}

	public function isBuffered(): ?bool
	{
		return $this->result->isBuffered();
	}

	public function isQueryResult(): bool
	{
		return $this->result->isQueryResult();
	}

	public function getFieldCount(): int
	{
		return $this->result->getFieldCount();
	}
}
