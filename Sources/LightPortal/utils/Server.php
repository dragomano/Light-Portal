<?php

namespace Bugo\LightPortal\Utils;

/**
 * Server.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

class Server extends AbstractArray
{
	public static $obj;

	public function __construct()
	{
		static::$obj = &$_SERVER;
	}

	/**
	 * Try run this object as a function
	 *
	 * Пытаемся запустить данный объект как функцию
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function __invoke(string $key, $default = null)
	{
		return static::get($key) ?? getenv($key) ?? $default;
	}
}
