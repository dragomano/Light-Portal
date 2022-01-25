<?php declare(strict_types=1);

/**
 * AbstractRequest.php
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

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractRequest
{
	protected array $storage = [];

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function &get(string $key)
	{
		return $this->storage[$key];
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function put(string $key, $value)
	{
		$this->storage[$key] = &$value;
	}

	public function all(): array
	{
		return $this->storage;
	}

	public function only(array $keys): array
	{
		$result = [];

		foreach ($keys as $key) {
			$key = trim($key);

			if (isset($this->storage[$key])) {
				$result[$key] = $this->storage[$key];
			}
		}

		return $result;
	}

	/**
	 * @param string|array $keys
	 * @return bool
	 */
	public function has($keys): bool
	{
		if (is_array($keys)) {
			foreach ($keys as $key) {
				if (! isset($this->storage[$key])) {
					return false;
				}
			}

			return true;
		}

		return isset($this->storage[$keys]);
	}

	public function isEmpty(string $key): bool
	{
		return empty($this->storage[$key]);
	}

	public function isNotEmpty(string $key): bool
	{
		return empty($this->isEmpty($key));
	}
}
