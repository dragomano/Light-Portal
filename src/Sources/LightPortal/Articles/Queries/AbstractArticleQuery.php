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

namespace LightPortal\Articles\Queries;

use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Events\EventDispatcherInterface;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractArticleQuery implements ArticleQueryInterface
{
	protected string $sorting = 'created;desc';

	protected array $columns = [];

	protected array $joins   = [];

	protected array $wheres  = [];

	protected array $params  = [];

	protected array $orders  = [];

	public function __construct(
		protected readonly PortalSqlInterface $sql,
		protected readonly EventDispatcherInterface $events
	) {}

	public function init(array $params): void
	{
		$this->params = $params;
	}

	public function setSorting(?string $sortType): void
	{
		$this->sorting = $sortType && isset($this->orders[$sortType]) ? $sortType : $this->sorting;
	}

	public function getSorting(): string
	{
		return $this->sorting;
	}

	public function prepareParams(int $start, int $limit): void
	{
		$this->params += [
			'start' => $start,
			'limit' => $limit,
			'sort'  => new Expression($this->orders[$this->sorting]),
		];
	}

	public function getRawData(): iterable
	{
		$select = $this->buildDataSelect();

		$this->applyColumns($select);
		$this->applyJoins($select);
		$this->applyWheres($select);

		$select
			->order($this->params['sort'])
			->limit($this->params['limit'])
			->offset($this->params['start']);

		return $this->sql->execute($select);
	}

	public function getTotalCount(): int
	{
		$select = $this->buildCountSelect();

		$this->applyJoins($select);
		$this->applyWheres($select);

		$result = $this->sql->execute($select)->current();

		return (int) ($result['count'] ?? 0);
	}

	abstract protected function applyBaseConditions(Select $select): void;

	protected function applyColumns(Select $select): void
	{
		$existingColumns = $select->getRawState(Select::COLUMNS) ?? [];

		$additionalColumns = [];
		foreach ($this->columns as $column) {
			if (is_string($column) && str_contains($column, ',')) {
				$parts = array_map('trim', explode(',', $column));
				$additionalColumns = array_merge($additionalColumns, $parts);
			} elseif (is_array($column)) {
				$additionalColumns = array_merge($additionalColumns, $column);
			} else {
				$additionalColumns[] = $column;
			}
		}

		$mergedColumns = array_merge($existingColumns, $additionalColumns);
		$select->columns($mergedColumns);
	}

	protected function applyJoins(Select $select): void
	{
		foreach ($this->joins as $join) {
			if (is_callable($join)) {
				$join($select);
			}
		}
	}

	protected function applyWheres(Select $select): void
	{
		$this->applyBaseConditions($select);

		foreach ($this->wheres as $where) {
			if (is_callable($where)) {
				$where($select);
			} else {
				$select->where($where);
			}
		}
	}
}
