<?php declare(strict_types=1);

/**
 * Cache.php
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

use function cache_get_data;
use function cache_put_data;
use function clean_cache;

if (! defined('SMF'))
	die('No direct access...');

final class Cache
{
	private string $prefix = 'lp_';

	private ?string $key;

	private int $lifeTime = 0;

	public function __construct(?string $key = null)
	{
		$this->key = $key;
	}

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

	public function get(string $key, ?int $time = null): ?array
	{
		return cache_get_data($this->prefix . $key, $time ?? $this->lifeTime);
	}

	public function put(string $key, ?array $value, ?int $time = null)
	{
		cache_put_data($this->prefix . $key, $value, $time ?? $this->lifeTime);
	}

	public function forget(string $key)
	{
		$this->put($key, null);
	}

	public function flush()
	{
		clean_cache();
	}
}
