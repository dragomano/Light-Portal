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

namespace LightPortal\Database\Migrations;

use Laminas\Db\Adapter\Platform\PlatformInterface;

class DbPlatform
{
	private static ?PlatformInterface $platform = null;

	public static function set(?PlatformInterface $platform): void
	{
		self::$platform = $platform;
	}

	public static function get(): ?PlatformInterface
	{
		return self::$platform;
	}
}
