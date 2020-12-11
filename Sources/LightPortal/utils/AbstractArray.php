<?php

namespace Bugo\LightPortal\Utils;

/**
 * AbstractArray.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

abstract class AbstractArray
{
	private static $obj;

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
		return static::get($key) ?? $default;
	}

	/**
	 * Get the $obj[$key] value
	 *
	 * Получаем значение $obj[$key]
	 *
	 * @param string $key
	 * @return mixed
	 */
	public static function &get(string $key)
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
	public static function put(string $key, $value)
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
	public static function push(string $key, $value)
	{
		if (!static::has($key) || !is_array(static::$obj[$key]))
			return;

		if (strpos($key, '.') !== false) {
			$subKey = explode('.', $key)[0];
			static::$obj[$key][$subKey] = $value;
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
	public static function forget(string $key)
	{
		unset(static::$obj[$key]);
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
				if (!isset(static::$obj[$k]))
					return false;
			}

			return true;
		}

		return isset(static::$obj[$key]);
	}

	/**
	 * Alias for static::has method
	 *
	 * Псевдоним для метода static::has
	 *
	 * @param string|array $key
	 * @return bool
	 */
	public static function exists($key)
	{
		return static::has($key);
	}

	/**
	 * Check if the key is not empty
	 *
	 * Проверяем, не пуста ли ячейка $obj[$key]
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function filled(string $key)
	{
		return !static::isEmpty($key);
	}

	/**
	 * Check if the key is empty
	 *
	 * Проверяем, пуста ли ячейка $obj[$key]
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function isEmpty(string $key)
	{
		return empty(static::$obj[$key]);
	}
}
