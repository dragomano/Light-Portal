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
 * @version 1.2
 */

class Request extends Arr
{
	public static $obj;

	public function __construct()
	{
		static::$obj = $_REQUEST;
	}

	/**
	 * Get the current path
	 *
	 * @return string
	 */
	public static function path()
	{
		return $_SERVER['REQUEST_URI'] ?? '';
	}

	/**
	 * Get the full url without queries
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
	 * @return string
	 */
	public static function fullUrl()
	{
		return $_SERVER['REQUEST_URL'] ?? '';
	}

	/**
	 * Get the current page url with queries
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
