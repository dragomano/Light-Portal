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
	 * Пытаемся запустить данный объект как функцию
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __invoke($key, $default = null)
	{
		return static::get($key) ?? $default;
	}

	/**
	 * Get the $obj[$key] value
	 *
	 * Получаем значение $obj[$key]
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
	 * Put $value into the $obj[$key]
	 *
	 * Сохраняем $value в ячейке $obj[$key]
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public static function put($key, $value)
	{
		static::$obj[$key] = &$value;
	}

	/**
	 * Get all $obj values
	 *
	 * Получаем все содержимое $obj
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
	 * Получаем значения только запрошенных ключей $keys в $obj
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
	 * Получаем значения только тех ключей в $obj, которые не перечислены в $keys
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
	 * Push a value into the key-array
	 *
	 * Сохраняем значение $value в переменную-массив $key
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
	 * Unset the $obj key
	 *
	 * Очищаем содержимое ячейки $obj[$key]
	 *
	 * @param string $key
	 * @return void
	 */
	public static function forget($key)
	{
		unset(static::$obj[$key]);
	}

	/**
	 * Unset all $obj array
	 *
	 * Очищаем все содержимое $obj
	 *
	 * @return void
	 */
	public static function flush()
	{
		unset(static::$obj);
	}

	/**
	 * Get and unset the key
	 *
	 * Получаем значение ячейки $obj[$key] и тут же очищаем её
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
	 * Check if the key is set
	 *
	 * Проверяем, существует ли ключ $key в $obj
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
	 * Check if the key is not empty
	 *
	 * Проверяем, не пуста ли ячейка $obj[$key]
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function filled($key)
	{
		return ! static::isEmpty($key);
	}

	/**
	 * Check if the key is empty
	 *
	 * Проверяем, пуста ли ячейка $obj[$key]
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function isEmpty($key)
	{
		return empty(static::$obj[$key]);
	}
}
