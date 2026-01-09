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

namespace LightPortal\Database;

use Bugo\Compat\Config;
use Laminas\Db\Adapter\Platform\Mysql;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Adapter\Platform\Postgresql;
use Laminas\Db\Adapter\Platform\Sqlite;

if (! defined('SMF'))
	die('No direct access...');

class PortalAdapterFactory
{
	public static function create(array $options = []): PortalAdapterInterface
	{
		$profiler = new PortalProfiler(self::getPlatform());

		$config = [
			'driver'   => self::getDriver(),
			'hostname' => Config::$db_server,
			'database' => Config::$db_name,
			'prefix'   => str_replace('`' . Config::$db_name . '`.', '', Config::$db_prefix),
			'username' => Config::$db_user,
			'password' => Config::$db_passwd,
			'profiler' => $profiler,
		];

		return new PortalAdapter(array_merge($config, $options));
	}

	protected static function getDriver(): string
	{
		return match (Config::$db_type) {
			'postgresql' => 'Pdo_Pgsql',
			'sqlite'     => 'Pdo_Sqlite',
			default      => 'Pdo_Mysql',
		};
	}

	protected static function getPlatform(): PlatformInterface
	{
		return match (Config::$db_type) {
			'postgresql' => new Postgresql(),
			'sqlite'     => new Sqlite(),
			default      => new Mysql(),
		};
	}
}
