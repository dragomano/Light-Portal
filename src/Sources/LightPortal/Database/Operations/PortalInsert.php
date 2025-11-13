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
use Laminas\Db\Sql\Insert;
use LightPortal\Database\PortalAdapter;
use LightPortal\Database\ResultSetWrapper;

if (! defined('SMF'))
	die('No direct access...');

class PortalInsert extends Insert
{
	protected array $batchValues = [];

	protected ?array $returning = null;

	private bool $isBatchMode = false;

	public function __construct(
		$table = null,
		private readonly string $prefix = '',
		array|string|null $returning = null
	)
	{
		parent::__construct($table);

		if ($returning !== null) {
			$this->setReturning($returning);
		}
	}

	public function into($table): static
	{
		$table = $this->prefix . $table;

		return parent::into($table);
	}

	public function setReturning(array|string $columns): static
	{
		$this->returning = (array) $columns;

		return $this;
	}

	public function batch(array $values): static
	{
		$this->batchValues = $values;
		$this->isBatchMode = true;

		return $this;
	}

	public function isBatch(): bool
	{
		return $this->isBatchMode;
	}

	public function executeInsert(AdapterInterface $adapter): ResultInterface
	{
		$columns = $this->getColumns();
		$values  = $this->getValues();

		$columnList   = implode(',', $columns);
		$placeholders = implode(',', array_fill(0, count($values), '?'));

		$sql = sprintf(
			"INSERT INTO %s (%s) VALUES (%s)%s",
			$this->table,
			$columnList,
			$placeholders,
			$this->isMariaDb($adapter) ? '' : $this->getReturning($adapter)
		);

		$stmt = $adapter->createStatement($sql, array_values($values));

		return $stmt->execute();
	}

	public function executeBatch(AdapterInterface $adapter): ResultInterface
	{
		if (empty($this->batchValues)) {
			return $adapter->query('SELECT 1 WHERE 0 = 1', []);
		}

		$columns    = array_keys($this->batchValues[0]);
		$columnList = implode(',', $columns);

		$placeholders = $allValues = [];
		foreach ($this->batchValues as $row) {
			$placeholders[] = '(' . str_repeat('?,', count($columns) - 1) . '?)';
			$allValues = array_merge($allValues, array_values($row));
		}

		$sql = sprintf(
			"INSERT INTO %s (%s) VALUES %s%s",
			$this->table,
			$columnList,
			implode(',', $placeholders),
			$this->getReturning($adapter)
		);

		$queryResult = $adapter->query($sql, $allValues);

		$this->resetBatch();

		if ($this->returning && $queryResult instanceof ResultSet) {
			return new ResultSetWrapper($queryResult);
		}

		return $queryResult;
	}

	protected function resetBatch(): void
	{
		$this->batchValues = [];
		$this->isBatchMode = false;
	}

	protected function getValues(): array
	{
		return $this->getRawState()['values'] ?? [];
	}

	protected function getColumns(): array
	{
		return $this->getRawState()['columns'] ?? [];
	}

	protected function getReturning(AdapterInterface $adapter): string
	{
		$platform = $adapter->getPlatform()->getName();
		if (in_array($platform, ['PostgreSQL', 'SQLite'], true) || $this->isMariaDb($adapter)) {
			return $this->returning ? ' RETURNING ' . implode(', ', $this->returning) : '';
		}

		return '';
	}

	protected function isMariaDb(AdapterInterface $adapter): bool
	{
		return $adapter instanceof PortalAdapter && stripos($adapter->getVersion(), 'MariaDB') !== false;
	}
}
