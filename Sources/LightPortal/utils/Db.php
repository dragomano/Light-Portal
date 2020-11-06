<?php

namespace Bugo\LightPortal\Utils;

/**
 * Db.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.2
 */

/**
 * Simple wrapper for some $smcFunc['db_query'] functions
 * to work with the database in Laravel Database Query Builder style
 */
class Db
{
	/** @var array */
	protected $db = [];

	/** @var string|null */
	protected $table;

	/** @var array */
	protected $columns = [];

	/** @var array */
	protected $joins = [];

	/** @var array */
	protected $wheres = [];

	/** @var array */
	protected $groupBy = [];

	/** @var array */
	protected $orderBy = [];

	/** @var string|null */
	protected $having;

	/** @var string|null */
	protected $limit;

	/** @var int|null */
	protected $offset;

	/** @var array */
	protected $params = [];

	/** @var array */
	protected $replaces = [];

	public function __construct($name = null)
	{
		$smcFunc = $GLOBALS['smcFunc'];

		$this->db = [
			'query'         => $smcFunc['db_query'],
			'insert'        => $smcFunc['db_insert'],
			'fetch_assoc'   => $smcFunc['db_fetch_assoc'],
			'fetch_row'     => $smcFunc['db_fetch_row'],
			'fetch_all'     => $smcFunc['db_fetch_all'],
			'affected_rows' => $smcFunc['db_affected_rows'],
			'free_result'   => $smcFunc['db_free_result'],
			'sql'           => &$GLOBALS['context']['lp_current_queries']
		];

		$this->replaces = ['{db_prefix}' => $GLOBALS['db_prefix'], '  ' => "<br>"];

		if ($name) {
			$this->table($name);
		}
	}

	/**
	 * Set the table which the query is targeting
	 *
	 * Задаем таблицу для нашего запроса
	 *
	 * @param string $name
	 * @param string|null $as
	 * @return $this
	 */
	public function table($name, $as = null)
	{
		$this->table = "{$name} {$as}";

		return $this;
	}

	/**
	 * Execute the query as a "select" statement
	 *
	 * Собираем и выполняем запрос к базе данных
	 *
	 * @param string $fetch_type
	 * @return mixed
	 */
	public function get($fetch_type = 'all')
	{
		if (!$this->columns) {
			$this->select(['*']);
		}

		$columns = implode(', ', $this->columns);

		$join = !empty($this->joins) ? implode('', $this->joins) : '';

		$where = $this->getPreparedWhere();

		$having = $this->having ? "  HAVING {$this->having}" : '';

		$group = !empty($this->groupBy) ? ('  GROUP BY ' . implode(', ', $this->groupBy)) : '';

		$order = !empty($this->orderBy) ? ('  ORDER BY ' . implode(', ', $this->orderBy)) : '';

		$limit = $this->limit ? "  LIMIT {$this->limit}" : '';

		$offset = $this->offset ? "  OFFSET {$this->offset}" : '';

		$sql = "SELECT {$columns}  FROM {db_prefix}{$this->table}{$join}{$where}{$having}{$group}{$order}{$limit}{$offset}";

		$this->db['sql'][] = $this->toSql($sql);

		$request = $this->db['query']('', $sql, $this->params);

		$result = $this->db['fetch_' . $fetch_type]($request);

		$this->db['free_result']($request);

		return $result;
	}

	/**
	 * Retrieve the "count" result of the query
	 *
	 * Получаем количество полученных записей
	 *
	 * @return int
	 */
	public function count()
	{
		$column = $this->columns[0] ?? '*';

		return $this->select("COUNT({$column})")->get('row')[0];
	}

	/**
	 * Retrieve the maximum value of a given column
	 *
	 * Получаем наибольшее значение выбранного столбца
	 *
	 * @param string $column
	 * @return mixed
	 */
	public function max($column)
	{
		return $this->select("MAX({$column})")->get('row')[0];
	}

	/**
	 * Retrieve the minimum value of a given column
	 *
	 * Получаем наименьшее значение выбранного столбца
	 *
	 * @param string $column
	 * @return mixed
	 */
	public function min($column)
	{
		return $this->select("MIN({$column})")->get('row')[0];
	}

	/**
	 * Execute the query and get the first result
	 *
	 * Выполняем запрос и получаем первую строку из результата запроса
	 *
	 * @param array|string $columns
	 * @return mixed
	 */
	public function first($columns = ['*'])
	{
		return $this->select($columns)->limit(1)->get('assoc');
	}

	/**
	 * Get an array with the values of a given column
	 *
	 * Получаем массив значений заданного столбца
	 *
	 * @param mixed $column
	 * @param mixed $key
	 * @return array
	 */
	public function pluck($column, $key = null)
	{
		return array_column($this->get(), $column, $key);
	}

	/**
	 * Add an "group by" clause to the query
	 *
	 * Добавляем условие "group by" для текущего запроса
	 *
	 * @param string $condition
	 * @return $this
	 */
	public function groupBy($condition)
	{
		$this->groupBy[] = $condition;

		return $this;
	}

	/**
	 * Add an "order by" clause to the query
	 *
	 * Добавляем условие "order by" для текущего запроса
	 *
	 * @param string $condition
	 * @return $this
	 */
	public function orderBy($condition)
	{
		$this->orderBy[] = $condition;

		return $this;
	}

	/**
	 * Add a "having" clause to the query
	 *
	 * Добавляем условие "having" для текущего запроса
	 *
	 * @param string $column
	 * @param string|null $operator
	 * @param string|null $value
	 * @param string $boolean
	 * @return $this
	 */
	public function having($column, $operator = null, $value = null, $boolean = 'and')
	{
		$this->having .= ($this->having ? " {$boolean} " : '') . "{$column} {$operator} {$value}";

		return $this;
	}

	/**
	 * Set the "limit" value of the query
	 *
	 * Задаем "limit" для текущего запроса
	 *
	 * @param int $value
	 * @param int $offset
	 * @return $this
	 */
	public function limit($value, $offset = null)
	{
		if ($value >= 0) {
			$this->limit = $value;

			if ($offset) {
				$this->limit = "{$value}, {$offset}";
			}
		}

		return $this;
	}

	/**
	 * Set the "offset" value of the query
	 *
	 * Задаем "offset" для текущего запроса
	 *
	 * @param int $value
	 * @return $this
	 */
	public function offset($value)
	{
		if ($value >= 0) {
			$this->offset = max(0, $value);
		}

		return $this;
	}

	/**
	 * Get a single column's value from the first result of a query
	 *
	 * Получаем значение заданного столбца из первой строки результата запроса
	 *
	 * @param string $column
	 * @return mixed
	 */
	public function value($column)
	{
		$result = (array) $this->first([$column]);

		return count($result) > 0 ? reset($result) : null;
	}

	/**
	 * Set the columns to be selected
	 *
	 * Задаем столбцы для выборки
	 *
	 * @param mixed $columns
	 * @return $this
	 */
	public function select($columns = ['*'])
	{
		// Support of raw queries
		if (is_string($columns) && strpos($columns, 'SELECT') !== false) {
			$sql = $columns;

			$this->db['sql'][] = $this->toSql($sql);

			$request = $this->db['query']('', $sql, []);

			$result = $this->db['fetch_all']($request);

			$this->db['free_result']($request);

			return $result;
		}

		$this->columns = is_array($columns) ? $columns : func_get_args();

		return $this;
	}

	/**
	 * Add a new select column to the query
	 *
	 * Добавляем дополнительные столбцы для выборки
	 *
	 * @param mixed $columns
	 * @return $this
	 */
	public function addSelect($columns = ['*'])
	{
		$columns = is_array($columns) ? $columns : func_get_args();

		$this->columns = array_merge($this->columns, $columns);

		return $this;
	}

	/**
	 * Add a new "raw" select expression to the query
	 *
	 * Добавляем необработанное выражение SELECT в текущий запрос
	 *
	 * @param string $expression
	 * @param array $bindings
	 * @return $this
	 */
	public function selectRaw($expression, array $bindings = [])
	{
		$columns = [];

		if (!empty($binding)) {
			foreach ($binding as $key => $value) {
				$columns[$key] = strtr($expression, array('?' => $value));
			}
		} else {
			$this->columns[] = $expression;
		}

		$this->columns = array_merge($this->columns, $columns);

		return $this;
	}

	/**
	 * Add a basic where clause to the query
	 *
	 * Добавляем базовое условие WHERE в запрос
	 *
	 * @param string|array $columns
	 * @param mixed $operator
	 * @param mixed $value
	 * @param string $boolean
	 * @return $this
	 */
	public function where($column, $operator = null, $value = null, $boolean = 'and')
	{
		if (empty($column)) {
			return $this;
		}

		if (is_array($column)) {
			foreach ($column as $key => $val) {
				if (is_numeric($key) && is_array($val)) {
					$this->where(...array_values($val));
				} else {
					if (is_numeric($key)) {
						$this->where($val);
					} else {
						$this->where($key, $val);
					}
				}
			}

			return $this;
		}

		// Case for where('1=1')
		if (func_num_args() === 1 || is_null($operator) && is_null($value)) {
			return $this->where(substr($column, 0, 1), '=', substr($column, 2, 3), $boolean);
		}

		// Case for where($column, $value)
		if (func_num_args() === 2 || is_null($value)) {
			return $this->where($column, '=', $operator, $boolean);
		}

		// Case for $column == 'prefix.column'
		if (($dot_position = strpos($column, '.')) !== false) {
			$old_column = $column;

			$column = substr($column, 0, $dot_position) . '_' . substr($column, $dot_position + 1);
		}

		$this->params[$column] = $value;

		switch (gettype($value)) {
			case 'integer':
				$new_value = "{int:{$column}}";
				break;

			case 'array':
				$new_value = "({array_int:{$column}})";
				break;

			case 'string':
			default:
				$new_value = "{string:{$column}}";
		}

		if (!empty($old_column)) {
			$column = $old_column;
		}

		$this->replaces[$new_value] = is_array($value) ? "(" . implode(',', $value) . ")" : (is_string($value) && empty($value) ? "''" : $value);

		$type  = 'basic';
		$value = $new_value;

		$this->wheres[] = compact(
			'type', 'column', 'operator', 'value', 'boolean'
		);

		return $this;
	}

	/**
	 * Add a "and where" clause to the query
	 *
	 * Добавляем условие "and where" для текущего запроса
	 *
	 * @param string|array $columns
	 * @param mixed $operator
	 * @param mixed $value
	 * @return $this
	 */
	public function andWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value);
	}

	/**
	 * Add a "or where" clause to the query
	 *
	 * Добавляем условие "or where" для текущего запроса
	 *
	 * @param string|array $columns
	 * @param mixed $operator
	 * @param mixed $value
	 * @return $this
	 */
	public function orWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value, 'or');
	}

	/**
	 * Add a "where in" clause to the query
	 *
	 * Добавляем условие "where in" для текущего запроса
	 *
	 * @param string $column
	 * @param mixed $values
	 * @param string  boolean
	 * @param bool $not
	 * @return $this
	 */
	public function whereIn($column, $values, $boolean = 'and', $not = false)
	{
		return $this->where($column, $not ? 'NOT IN' : 'IN', $values, $boolean);
	}

	/**
	 * Add a "or where in" clause to the query
	 *
	 * Добавляем условие "or where in" для текущего запроса
	 *
	 * @param string $column
	 * @param mixed $values
	 * @param string  boolean
	 * @param bool $not
	 * @return $this
	 */
	public function orWhereIn($column, $values, $boolean = 'or', $not = false)
	{
		return $this->whereIn($column, $values, $boolean, $not);
	}

	/**
	 * Add a "where not in" clause to the query
	 *
	 * Добавляем условие "where not in" для текущего запроса
	 *
	 * @param string $column
	 * @param mixed $values
	 * @param string  boolean
	 * @return $this
	 */
	public function whereNotIn($column, $values, $boolean = 'and')
	{
		return $this->whereIn($column, $values, $boolean, true);
	}

	/**
	 * Add a "or where not in" clause to the query
	 *
	 * Добавляем условие "or where not in" для текущего запроса
	 *
	 * @param string $column
	 * @param mixed $values
	 * @return $this
	 */
	public function orWhereNotIn($column, $values)
	{
		return $this->whereNotIn($column, $values, 'or');
	}

	/**
	 * Add a "where null" clause to the query
	 *
	 * Добавляем условие "where null" для текущего запроса
	 *
	 * @param string|array $column
	 * @param string $boolean
	 * @param bool $not
	 * @return $this
	 */
	public function whereNull($column, $boolean = 'and', $not = false)
	{
		return $this->where($column, $not ? '<>' : '=', '');
	}

	/**
	 * Add a "where not null" clause to the query
	 *
	 * Добавляем условие "where not null" для текущего запроса
	 *
	 * @param string|array $column
	 * @param string $boolean
	 * @param bool $not
	 * @return $this
	 */
	public function whereNotNull($column, $boolean = 'and')
	{
		return $this->whereNull($column, $boolean, true);
	}

	/**
	 * Add a raw where clause to the query
	 *
	 * Добавляем необработанное выражение WHERE в текущий запрос
	 *
	 * @param string $sql
	 * @param mixed $bindings
	 * @param string $boolean
	 * @return $this
	 */
	public function whereRaw($sql, $bindings = [], $boolean = 'and')
	{
		$this->wheres[] = ['type' => 'raw', 'sql' => $sql, 'boolean' => $boolean];

		return $this;
	}

	/**
	 * Add a raw or where clause to the query
	 *
	 * Добавляем необработанное выражение OR WHERE в текущий запрос
	 *
	 * @param string $sql
	 * @param mixed $bindings
	 * @return $this
	 */
	public function orWhereRaw($sql, $bindings = [])
	{
		return $this->whereRaw($sql, $bindings, 'or');
	}

	/**
	 * Add a join clause to the query
	 *
	 * Добавляем дополнительную таблицу через JOIN типа $type
	 *
	 * @param string $table
	 * @param string $first
	 * @param string|null $operator
	 * @param string|null $second
	 * @param string $type
	 * @return $this
	 */
	public function join($table, $first, $operator = null, $second = null, $type = 'inner')
	{
		$type = $type === 'left' ? 'LEFT' : 'INNER';

		$this->joins[] = "  {$type} JOIN {db_prefix}{$table} ON ({$first}{$operator}{$second})";

		return $this;
	}

	/**
	 * Add a left join to the query
	 *
	 * Добавляем дополнительную таблицу через LEFT JOIN
	 *
	 * @param string $table
	 * @param string $first
	 * @param string|null $operator
	 * @param string|null $second
	 * @return $this
	 */
	public function leftJoin($table, $first, $operator = null, $second = null)
	{
		return $this->join($table, $first, $operator, $second, 'left');
	}

	/**
	 * Insert new records into the database
	 *
	 * Добавляем новые записи в базу данных
	 *
	 * @param array $values
	 * @param array $keys
	 * @param string $method (insert|replace|ignore)
	 * @return array|int
	 */
	public function insert($values, $keys = [], $method = 'insert')
	{
		if (empty($values)) {
			return 0;
		}

		if (!is_array(reset($values))) {
			$values = [$values];
		} else {
			foreach ($values as $key => $value) {
				ksort($value);

				$values[$key] = $value;
			}
		}

		$columns = [];
		foreach ($values as $key => $value) {
			foreach ($value as $k => $v) {
				$columns[$k] = gettype($v) === 'integer' ? 'int' : 'string';
			}
		}

		$type = $method == 'replace' ? 'REPLACE' : ($method == 'ignore' ? 'INSERT IGNORE' : 'INSERT');
		$sql  = "{$type} INTO {db_prefix}{$this->table}" . '(`' . implode('`, `', array_keys($columns)) . '`)  VALUES ' . $this->getPreparedValues($values);

		$this->db['sql'][] = $this->toSql($sql);

		$inserted_ids = $this->db['insert']($method, "{db_prefix}{$this->table}", $columns, $values, $keys, 2);

		return count($values) > 1 ? $inserted_ids : $inserted_ids[0];
	}

	/**
	 * Update records in the database
	 *
	 * Обновляем записи в базе данных
	 *
	 * @param array $columns ([$column => $value] or [$column => [$expression]])
	 * @return int
	 */
	public function update($columns)
	{
		if (empty($columns)) {
			return 0;
		}

		$set = '';
		foreach ($columns as $key => $value) {
			if (is_array($value)) {
				$set .= ($set ? ', ' : '') . "`{$key}` = {$value[0]}";
			} else {
				$columns[$key] = gettype($value) === 'integer' ? 'int' : 'string';

				$set .= ($set ? ', ' : '') . "`{$key}` = {{$columns[$key]}:{$key}}";

				$this->params[$key] = $value;
			}
		}

		$where = $this->getPreparedWhere();

		$sql = "UPDATE {db_prefix}{$this->table} SET {$set}{$where}";

		$this->db['sql'][] = $this->toSql($sql);

		$this->db['query']('', $sql, $this->params);

		return $this->db['affected_rows']();
	}

	/**
	 * Increment a column's value by a given amount
	 *
	 * Увеличиваем значение столбца на заданную величину
	 *
	 * @param string $column
	 * @param int $amount
	 * @param array $extra
	 * @return int
	 */
	public function increment($column, $amount = 1, $extra = [])
	{
		if (!is_numeric($amount))
			return 0;

		$columns = array_merge([$column => ["{$column} + {$amount}"]], $extra);

		return $this->update($columns);
	}

	/**
	 * Decrement a column's value by a given amount
	 *
	 * Уменьшаем значение столбца на заданную величину
	 *
	 * @param string $column
	 * @param int $amount
	 * @param array $extra
	 * @return int
	 */
	public function decrement($column, $amount = 1, $extra = [])
	{
		return $this->increment($column, -$amount, $extra);
	}

	/**
	 * Delete records from the database
	 *
	 * Удаляем записи из базы данных
	 *
	 * @return int
	 */
	public function delete()
	{
		$where = $this->getPreparedWhere();

		$sql = "DELETE FROM {db_prefix}{$this->table}{$where}";

		$this->db['sql'][] = $this->toSql($sql);

		$this->db['query']('', $sql, $this->params);

		return $this->db['affected_rows']();
	}

	/**
	 * Get the full SQL query string
	 *
	 * Получаем полную строку запроса SQL
	 *
	 * @param string $sql
	 * @return string
	 */
	public function toSql($sql)
	{
		return strtr($sql, $this->replaces);
	}

	/**
	 * Get prepared "where" string
	 *
	 * Получаем подготовленную строку "where"
	 *
	 * @return string
	 */
	private function getPreparedWhere()
	{
		$result = '';
		foreach ($this->wheres as $where) {
			extract($where);

			if ($type == 'raw') {
				if ($sql) {
					$result .= ($result ? strtoupper(" {$boolean} ") : '  WHERE ') . $sql;
				}
			} else {
				$result .= ($result ? strtoupper(" {$boolean} ") : '  WHERE ') . "{$column} {$operator} {$value}";
			}
		}

		return $result;
	}

	/**
	 * Getting a part of an SQL expression like "(value1, value2, value3)"
	 *
	 * Получаем часть SQL-выражения вида "(value1, value2, value3)"
	 *
	 * @param array $items
	 * @return string
	 */
	private function getPreparedValues(array $items)
	{
		if (empty($items))
			return '';

		$result = '';
		$cnt = count($items);
		for ($i = 0; $i < $cnt; $i++) {
			if ($i > 0)
				$result .= ', ';

			$result .= "('" . implode("', '", $items[$i]) . "')";
		}

		return $result;
	}
}
