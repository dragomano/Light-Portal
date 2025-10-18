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
use Laminas\Db\Sql\Insert;

if (! defined('SMF'))
	die('No direct access...');

class PortalInsert extends Insert
{
	protected array $batchValues = [];

	private bool $isBatchMode = false;

	public function __construct($table = null, private readonly string $prefix = '')
	{
		parent::__construct($table);
	}

	public function into($table): self
	{
		$table = $this->prefix . $table;

		return parent::into($table);
	}

	public function batch(array $values): self
	{
		$this->batchValues = $values;
		$this->isBatchMode = true;

		return $this;
	}

	public function isBatch(): bool
	{
		return $this->isBatchMode;
	}

	public function executeBatch(AdapterInterface $adapter): ResultInterface
	{
		if (empty($this->batchValues)) {
			return $adapter->query('SELECT 1 WHERE 0 = 1', []);
		}

		$columns = array_keys($this->batchValues[0]);
		$columnList = implode(',', $columns);

		$placeholders = $allValues = [];
		foreach ($this->batchValues as $row) {
			$placeholders[] = '(' . str_repeat('?,', count($columns) - 1) . '?)';
			$allValues = array_merge($allValues, array_values($row));
		}

		$sql = sprintf(
			"INSERT INTO %s (%s) VALUES %s",
			$this->table,
			$columnList,
			implode(',', $placeholders)
		);

		$result = $adapter->query($sql, $allValues);

		$this->resetBatch();

		return $result;
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
}
