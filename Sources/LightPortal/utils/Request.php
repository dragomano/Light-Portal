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

class Request extends AbstractArray
{
	public static $obj;

	public function __construct()
	{
		static::$obj = &$_REQUEST;
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

	/**
	 * Get the JSON payload for the request
	 *
	 * Получаем данные JSON из запроса
	 *
	 * @param string|null $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function json($key = null, $default = null)
	{
		$data = json_decode(file_get_contents('php://input'), true);

		if (isset($data[$key])) {
			return $data[$key] ?: $default;
		}

		return $data;
	}
}
