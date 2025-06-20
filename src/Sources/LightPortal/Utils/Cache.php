<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\Cache\CacheApi;
use Bugo\LightPortal\Utils\Traits\HasRequest;

if (! defined('SMF'))
	die('No direct access...');

final class Cache implements CacheInterface
{
	use HasRequest;

	private string $prefix = 'lp_';

	public function __construct(private readonly ?string $key = null, private int $lifeTime = LP_CACHE_TIME ?? 0) {}

	public function withKey(?string $key): self
	{
		return new self($key);
	}

	public function setLifeTime(int $lifeTime): self
	{
		if ($this->request()->has('preview')) {
			$lifeTime = 0;
		}

		$this->lifeTime = $lifeTime;

		return $this;
	}

	public function remember(string $key, callable $callback, int $time = LP_CACHE_TIME ?? 0): mixed
	{
		if ($time === 0) {
			$this->forget($key);
		}

		$data = $this->get($key, $time);

		if ($data === null) {
			$data = $callback();

			$this->put($key, $data, $time);
		}

		return $data;
	}

	public function setFallback(callable $callback): mixed
	{
		return $this->remember($this->key, fn() => app(Weaver::class)($callback), $this->lifeTime);
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
}
