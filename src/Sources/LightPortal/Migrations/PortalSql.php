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

namespace Bugo\LightPortal\Migrations;

use Bugo\LightPortal\Migrations\Operations\PortalDelete;
use Bugo\LightPortal\Migrations\Operations\PortalInsert;
use Bugo\LightPortal\Migrations\Operations\PortalSelect;
use Bugo\LightPortal\Migrations\Operations\PortalUpdate;
use Laminas\Db\Sql\Sql;

if (! defined('SMF'))
	die('No direct access...');

class PortalSql extends Sql implements PortalSqlInterface
{
	private readonly string $prefix;

	public function __construct(PortalAdapter $adapter)
	{
		parent::__construct($adapter);

		$this->prefix = $adapter->getPrefix();
	}

	public function select($table = null): PortalSelect
	{
		return new PortalSelect($table, $this->prefix);
	}

	public function insert($table = null): PortalInsert
	{
		return new PortalInsert($table, $this->prefix);
	}

	public function update($table = null): PortalUpdate
	{
		return new PortalUpdate($table, $this->prefix);
	}

	public function delete($table = null): PortalDelete
	{
		return new PortalDelete($table, $this->prefix);
	}
}
