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
 * @version 1.10
 */

final class Session
{
	/**
	 * @var array
	 */
	private $storage = [];

	public function __construct()
	{
		$this->storage = &$_SESSION;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		if (isset($this->storage[$key])) {
			return $this->storage[$key];
		}
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function put($key, $value)
	{
		$this->storage[$key] = $value;
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
