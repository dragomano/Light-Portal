<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\CacheApi;

use function method_exists;

if (! defined('SMF'))
	die('No direct access...');

final class Cache implements CacheInterface
{
	private string $prefix = 'lp_';

	public function __construct(private readonly ?string $key = null, private int $lifeTime = LP_CACHE_TIME ?? 0)
	{
	}

	public function setLifeTime(int $lifeTime): self
	{
		$this->lifeTime = $lifeTime;

		return $this;
	}

	public function setFallback(string $className, string $methodName = '__invoke', ...$params): mixed
	{
		if (empty($methodName) || empty($className) || $this->lifeTime === 0) {
			$this->forget($this->key);
		}

		if (($cachedValue = $this->get($this->key, $this->lifeTime)) === null) {
			$cachedValue = $this->callMethod($className, $methodName, ...$params);
			$this->put($this->key, $cachedValue, $this->lifeTime);
		}

		return $cachedValue;
	}

	public function get(string $key, ?int $time = null): mixed
	{
		return CacheApi::get($this->prefix . $key, $time ?? $this->lifeTime);
	}

	public function put(string $key, mixed $value, ?int $time = null): void
	{
		CacheApi::put($this->prefix . $key, $value, $time ?? $this->lifeTime);
	}

	public function forget(string $key): void
	{
		$this->put($key, null);
	}

	public function flush(): void
	{
		CacheApi::clean();
	}

	protected function callMethod(string $className, string $methodName, ...$params): mixed
	{
		if (method_exists($className, $methodName)) {
			return (new $className())->{$methodName}(...$params);
		}

		return null;
	}
}
