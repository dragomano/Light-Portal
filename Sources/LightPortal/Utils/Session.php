<?php

declare(strict_types = 1);

/**
 * Session.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Utils;

final class Session
{
	private array $storage = [];

	public function __construct()
	{
		$this->storage = &$_SESSION;
	}

	/**
	 * @param string $key
	 * @return bool|int|string
	 */
	public function get(string $key)
	{
		return $this->storage[$key] ?? false;
	}

	/**
	 * @param string $key
	 * @param bool|int|string $value
	 * @return void
	 */
	public function put(string $key, $value)
	{
		$this->storage[$key] = $value;
	}

	public function isEmpty(string $key): bool
	{
		return empty($this->get($key));
	}
}
