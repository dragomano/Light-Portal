<?php

namespace Bugo\LightPortal\Utils;

/**
 * Arr.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.2
 */

abstract class Arr
{
	private static $obj;

	/**
	 * Try run this object as a function
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __invoke($name, $default = null)
	{
		return static::get($name) ?? $default;
	}

	/**
	 * Get the session key
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function &get($key)
	{
		return static::$obj[$key];
	}

	/**
	 * Put the key into a session
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public static function put($key, &$value)
	{
		static::$obj[$key] = &$value;
	}

	/**
	 * Get all request array
	 *
	 * @return array
	 */
	public static function all()
	{
		return static::$obj;
	}

	/**
	 * Get only the request keys that defined in $keys
	 *
	 * @param array|string $keys
	 * @return array
	 */
	public static function only($keys)
	{
		$result = [];

		if (is_string($keys))
			$keys = explode(',', $keys);

		foreach ($keys as $key) {
			if (isset(static::$obj[$key]))
				$result[$key] = static::$obj[$key];
		}

		return $result;
	}

	/**
	 * Get only the request keys that not defined in $keys
	 *
	 * @param array|string $keys
	 * @return array
	 */
	public static function except($keys)
	{
		$result = [];

		if (is_string($keys))
			$keys = explode(',', $keys);

		foreach ($keys as $key) {
			if (isset(static::$obj[$key]))
				$result[$key] = static::$obj[$key];
		}

		return array_diff(static::$obj, $result);
	}

	/**
	 * Push a value into the session key-array
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public static function push($key, $value)
	{
		if (! static::has($key) || ! is_array(static::$obj[$key]))
			return;

		if (strpos($key, '.') !== false) {
			$subkey = explode('.', $key)[0];
			static::$obj[$key][$subkey] = $value;
		} else {
			static::$obj[$key][] = $value;
		}
	}

	/**
	 * Unset the session key
	 *
	 * @param string $key
	 * @return void
	 */
	public static function forget($key)
	{
		unset(static::$obj[$key]);
	}

	/**
	 * Unset all session array
	 *
	 * @return void
	 */
	public static function flush()
	{
		unset(static::$obj);
	}

	/**
	 * Get and unset the session key
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return void
	 */
	public static function pull($key, $default = null)
	{
		$result = static::get($key, $default);

		static::forget($key);

		return $result;
	}

	/**
	 * Check if the session key is set
	 *
	 * @param string|array $key
	 * @return bool
	 */
	public static function has($key)
	{
		if (is_array($key)) {
			foreach ($key as $k) {
				if (! isset(static::$obj[$k]))
					return false;
			}

			return true;
		}

		return isset(static::$obj[$key]);
	}

	public static function exists($key)
	{
		return static::has(static::$obj, $key);
	}

	/**
	 * Check if the session key exists
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function filled($key)
	{
		return ! static::isEmpty($key);
	}

	public static function isEmpty($key)
	{
		return empty(static::$obj[$key]);
	}
}
