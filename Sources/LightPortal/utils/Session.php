<?php

namespace Bugo\LightPortal\Utils;

/**
 * Session.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

final class Session
{
	public function __construct()
	{
		if (! isset($_SESSION)) {
			session_start();
		}
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		if (isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function put($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function isEmpty(string $key): bool
	{
		return empty($this->get($key));
	}
}
