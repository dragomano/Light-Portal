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

use Bugo\Compat\Config;

class PortalAdapterFactory
{
	public static function create(): PortalAdapter
	{
		return new PortalAdapter([
			'driver'   => Config::$db_type === 'mysql' ? 'Pdo_Mysql' : 'Pdo_Pgsql',
			'hostname' => Config::$db_server,
			'database' => Config::$db_name,
			'prefix'   => str_replace('`' . Config::$db_name . '`.', '', Config::$db_prefix),
			'username' => Config::$db_user,
			'password' => Config::$db_passwd,
		]);
	}
}
