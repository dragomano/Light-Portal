<?php

namespace Bugo\LightPortal\Utils;

/**
 * Cache.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Cache
{
	private static $prefix = 'light_portal_';

	/**
	 * Get data from cache
	 *
	 * Получаем данные из кэша
	 *
	 * @param string|null $key
	 * @param string|null $funcName
	 * @param string|null $class
	 * @param int $time (in seconds)
	 * @param mixed $vars
	 * @return mixed
	 */
	public function __invoke(?string $key, ?string $funcName, ?string $class, int $time = 3600, ...$vars)
	{
		if (empty($key))
			return false;

		if ($funcName === null || $class === null || $time === 0)
			static::forget($key);

		if (($$key = static::get($key, $time)) === null) {
			$$key = null;

			if (method_exists($class, $funcName)) {
				$$key = (new $class)->$funcName(...$vars);
			} elseif (function_exists($funcName)) {
				$$key = $funcName(...$vars);
			}

			static::put($key, $$key, $time);
		}

		return $$key;
	}

	/**
	 * Get $key value from the cache
	 *
	 * Получаем значение ячейки $key из кэша
	 *
	 * @param string $key
	 * @param int $time
	 * @return mixed
	 */
	public static function get(string $key, $time = 120)
	{
		return cache_get_data(static::$prefix . $key, $time);
	}

	/**
	 * Put $value into $key in the cache for $time ms
	 *
	 * Кладем $value в ячейку $key в кэше, на $time мс
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param int $time
	 * @return void
	 */
	public static function put(string $key, $value, $time = 120)
	{
		cache_put_data(static::$prefix . $key, $value, $time);
	}

	/**
	 * Clear $key from the cache
	 *
	 * Очищаем ячейку $key в кэше
	 *
	 * @param string $key
	 * @return void
	 */
	public static function forget(string $key)
	{
		self::put($key, null);
	}

	/**
	 * Clear cache
	 *
	 * Очищаем кэш
	 *
	 * @return void
	 */
	public static function flush()
	{
		clean_cache();
	}
}
