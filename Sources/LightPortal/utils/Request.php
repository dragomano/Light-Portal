<?php

namespace Bugo\LightPortal\Utils;

/**
 * Request.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Request
{
	/**
	 * @var array
	 */
	protected $storage = [];

	public function __construct()
	{
		$this->storage = &$_REQUEST;
	}

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

	/**
	 * @return array
	 */
	public function all(): array
	{
		return $this->storage;
	}

	/**
	 * @param array $keys
	 * @return array
	 */
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
	 * @param string|array $key
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

	/**
	 * @param string $key
	 * @return bool
	 */
	public function isEmpty(string $key): bool
	{
		return empty($this->storage[$key]);
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function notEmpty(string $key): bool
	{
		return empty($this->isEmpty($key));
	}

	/**
	 * @param string|array ...$patterns
	 * @return bool
	 */
	public function is(...$patterns): bool
    {
		if ($this->has('action') === false) {
			return false;
		}

		if (is_array($patterns[0])) {
			$patterns = $patterns[0];
		}

		foreach ($patterns as $pattern) {
			if ($this->storage['action'] === $pattern) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string|array ...$patterns
	 * @return bool
	 */
	public function isNot(...$patterns): bool
	{
		return empty($this->is($patterns));
	}

	/**
	 * @param string|null $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function json(string $key = null, $default = null)
	{
		$data = json_decode(file_get_contents('php://input'), true);

		if (isset($data[$key])) {
			return $data[$key] ?: $default;
		}

		return $data;
	}

	/**
	 * @return string
	 */
	public static function url(): string
	{
		return $_SERVER['REQUEST_URL'];
	}
}
