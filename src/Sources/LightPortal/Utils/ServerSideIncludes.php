<?php declare(strict_types=1);

/**
 * ServerSideIncludes.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Utils;

if (! defined('SMF'))
	die('No direct access...');

final class ServerSideIncludes
{
	public static function __callStatic(string $name, array $arguments)
	{
		$name = 'ssi_' . $name;

		if (function_exists($name))
			return $name(...$arguments);

		return false;
	}
}
