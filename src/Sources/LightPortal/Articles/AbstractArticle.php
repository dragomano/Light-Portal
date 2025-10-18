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

namespace LightPortal\Articles;

use LightPortal\Database\PortalSqlInterface;
use LightPortal\Events\HasEvents;
use LightPortal\Utils\Traits\HasRequest;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractArticle implements ArticleInterface
{
	use HasEvents;
	use HasRequest;

	protected string $sorting;

	protected array $columns = [];

	protected array $joins   = [];

	protected array $wheres  = [];

	protected array $params  = [];

	protected array $orders  = [];

	public function __construct(protected PortalSqlInterface $sql) {}

	abstract public function init(): void;

	abstract public function getSortingOptions(): array;

	abstract public function getData(int $start, int $limit, string $sortType = null): iterable;

	abstract public function getTotalCount(): int;

	abstract protected function applyBaseConditions(Select $select): void;

	protected function setSorting(?string $sortType): void
	{
		$this->sorting = $sortType ?: $this->sorting;
	}

	protected function prepareParams(int $start, int $limit): void
	{
		$this->params += [
			'start' => $start,
			'limit' => $limit,
			'sort'  => new Expression($this->orders[$this->sorting]),
		];
	}

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
