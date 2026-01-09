<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Utils\Traits;

use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;

trait HasParamJoins
{
	protected function addParamJoins(Select $select, array $config = []): void
	{
		$primary = $config['primary'] ?? 'p.page_id';
		$entity  = $config['entity']  ?? 'page';
		$params  = $config['params']  ?? [];

		if (empty($params)) {
			$this->joinAllParams($select, $primary, $entity);
			return;
		}

		$this->joinParamsArray($select, $params, $primary, $entity);
	}

	protected function joinAllParams(Select $select, string $primary, string $entity): void
	{
		$select->join(
			['pp' => 'lp_params'],
			new Expression("pp.item_id = $primary AND pp.type = ?", [$entity]),
			['name', 'value'],
			Select::JOIN_LEFT
		);
	}

	protected function joinParamsArray(Select $select, array $params, string $primary, string $entity): void
	{
		$index = 1;
		foreach ($params as $key => $param) {
			[$name, $alias, $columns] = $this->normalizeParamConfig($key, $param, $index);
			$this->joinSingleParam($select, $alias, $primary, $entity, $name, $columns);
			$index++;
		}
	}

	protected function joinSingleParam(
		Select $select,
		string $alias,
		string $primary,
		string $entity,
		string $name,
		array $columns = []
	): void {
		$select->join(
			[$alias => 'lp_params'],
			new Expression(
				"$alias.item_id = $primary AND $alias.type = ? AND $alias.name = ?",
				[$entity, $name]
			),
			$columns,
			Select::JOIN_LEFT
		);
	}

	protected function normalizeParamConfig(int|string $key, mixed $param, int $index): array
	{
		if (is_int($key)) {
			$name = $param;
			$alias = 'pp' . ($index > 1 ? $index : '');
			$columns = [];
		} else {
			// 'param_name' => ['alias' => ..., 'columns' => ...]
			$name = $key;
			$alias = $param['alias'] ?? 'pp' . ($index > 1 ? $index : '');
			$columns = $param['columns'] ?? [];
		}

		return [$name, $alias, $columns];
	}
}
