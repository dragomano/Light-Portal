<?php

namespace Bugo\LightPortal\Utils;

/**
 * Request.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

class Request extends Arr
{
	public static $obj;

	public function __construct()
	{
		static::$obj = &$_REQUEST;
	}

	/**
	 * Get the current $_SERVER['REQUEST_URI']
	 *
	 * Получаем текущий $_SERVER['REQUEST_URI']
	 *
	 * @return string
	 */
	public static function path()
	{
		return Server('REQUEST_URI', '');
	}

	/**
	 * Get the full url without queries
	 *
	 * Получаем полный URL без параметров
	 *
	 * @return string
	 */
	public static function url()
	{
		return explode(';', static::fullUrl())[0];
	}

	/**
	 * Get the current page url
	 *
	 * Получаем URL текущей страницы
	 *
	 * @return string
	 */
	public static function fullUrl()
	{
		return Server('REQUEST_URL', '');
	}

	/**
	 * Get the current page url with queries
	 *
	 * Получаем URL текущей страницы, вместе с параметрами запроса
	 *
	 * @param array $query
	 * @return string
	 */
	public static function fullUrlWithQuery(array $query = [])
	{
		$queries = '';

		foreach ($query as $key => $value) {
			$queries .= ";{$key}={$value}";
		}

		return static::fullUrl() . $queries;
	}

	/**
	 * Check if the current action matches one of given patterns
	 *
	 * Проверяем, соответствует ли текущий action одному из указанных в $patterns
	 *
	 * @param string|array ...$patterns
	 * @return bool
	 */
	public static function is(...$patterns)
	{
		if (static::has('action') === false)
			return false;

		if (is_array($patterns[0])) {
			$patterns = $patterns[0];
		}

		foreach ($patterns as $pattern) {
			if (static::get('action') === $pattern) {
				return true;
			}
		}

		return false;
	}
}
