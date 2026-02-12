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
use Laminas\Db\Extra\Adapter\AdapterFactory;
use Laminas\Db\Extra\Adapter\ExtendedProfiler;

if (! defined('SMF'))
	die('No direct access...');

class PortalAdapterFactory extends AdapterFactory
{
	public static function create(array $config = []): PortalAdapterInterface
	{
		$driver   = self::getDriver();
		$profiler = new ExtendedProfiler(self::getPlatform($driver));

		$baseConfig = [
			'driver'   => $driver,
			'hostname' => Config::$db_server,
			'database' => Config::$db_name,
			'prefix'   => str_replace('`' . Config::$db_name . '`.', '', Config::$db_prefix),
			'username' => Config::$db_user,
			'password' => Config::$db_passwd,
			'profiler' => $profiler,
		];

		return new PortalAdapter(array_merge($baseConfig, $config));
	}

	protected static function getDriver(): string
	{
		return match (Config::$db_type) {
			'postgresql' => 'Pdo_Pgsql',
			'sqlite'     => 'Pdo_Sqlite',
			default      => 'Pdo_Mysql',
		};
	}
}
