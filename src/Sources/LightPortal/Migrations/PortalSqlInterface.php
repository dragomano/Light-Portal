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

interface PortalSqlInterface
{
	public function select($table = null): PortalSelect;

	public function insert($table = null): PortalInsert;

	public function update($table = null): PortalUpdate;

	public function delete($table = null): PortalDelete;
}
