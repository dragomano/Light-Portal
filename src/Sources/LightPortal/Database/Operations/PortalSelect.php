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

use InvalidArgumentException;
use Laminas\Db\Sql\Select;

if (! defined('SMF'))
	die('No direct access...');

class PortalSelect extends Select
{
	public function __construct($table = null, private readonly string $prefix = '')
	{
		if (! is_string($table) && $table !== null) {
			throw new InvalidArgumentException('Table name must be a string or null');
		}

		parent::__construct($table);
	}

	public function from($table): self
	{
		if (is_string($table)) {
			$table = $this->prefix . $table;
		} elseif (is_array($table)) {
			foreach ($table as $alias => $tbl) {
				if (is_string($tbl)) {
					$table[$alias] = $this->prefix . $tbl;
				}
			}

			$this->table = $table;

			return $this;
		}

		return parent::from($table);
	}

	public function join($name, $on, $columns = self::SQL_STAR, $type = self::JOIN_INNER): self
	{
		if (is_string($name)) {
			$name = $this->prefix . $name;
		} elseif (is_array($name)) {
			foreach ($name as $alias => $tbl) {
				if (is_string($tbl)) {
					$name[$alias] = $this->prefix . $tbl;
				}
			}
		}

		return parent::join($name, $on, $columns, $type);
	}
}
