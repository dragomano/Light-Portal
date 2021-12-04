<?php

namespace Bugo\LightPortal\Utils;

/**
 * Cache.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.10
 */

if (!defined('SMF'))
	die('Hacking attempt...');

final class Cache
{
	/**
	 * @var string
	 */
	private $prefix = 'lp_';

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var int
	 */
	private $lifeTime = 0;

	/**
	 * @param string|null $key
	 */
	public function __construct(string $key = null)
	{
		$this->key = $key;
	}

	/**
	 * @param int $lifeTime
	 * @return $this
	 */
	public function setLifeTime(int $lifeTime): Cache
	{
		$this->lifeTime = $lifeTime;

		return $this;
	}

	/**
	 * @param string $className
	 * @param string $methodName
	 * @param ...$params
	 * @return mixed
	 */
	public function setFallback(string $className, string $methodName, ...$params)
	{
		if (empty($methodName) || empty($className) || $this->lifeTime === 0)
			$this->forget($this->key);

		if ((${$this->key} = $this->get($this->key, $this->lifeTime)) === null) {
			${$this->key} = null;

			if (method_exists($className, $methodName)) {
				${$this->key} = (new $className)->{$methodName}(...$params);
			}

			$this->put($this->key, ${$this->key}, $this->lifeTime);
		}

		return ${$this->key};
	}

	/**
	 * @param string $key
	 * @param int|null $time
	 * @return mixed
	 */
	public function get(string $key, int $time = null)
	{
		return cache_get_data($this->prefix . $key, $time ?? $this->lifeTime);
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param int|null $time
	 * @return void
	 */
	public function put(string $key, $value, int $time = null)
	{
		cache_put_data($this->prefix . $key, $value, $time ?? $this->lifeTime);
	}

	/**
	 * @param string $key
	 * @return void
	 */
	public function forget(string $key)
	{
		$this->put($key, null);
	}

	/**
	 * @return void
	 */
	public function flush()
	{
		clean_cache();
	}
}
