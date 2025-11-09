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

namespace LightPortal\Utils;

use Bugo\Compat\Cache\CacheApi;
use LightPortal\Utils\Traits\HasRequest;

use const LP_CACHE_TIME;

if (! defined('SMF'))
	die('No direct access...');

final class Cache implements CacheInterface
{
	use HasRequest;

	private string $prefix = 'lp_';

	public function __construct(private readonly ?string $key = null, private ?int $lifeTime = null)
	{
		if ($lifeTime === null) {
			$this->lifeTime = LP_CACHE_TIME ?? 0;
		}
	}

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

	public function remember(string $key, callable $callback, ?int $time = null): mixed
	{
		if ($time === null) {
			$time = $this->lifeTime;
		}

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
		return $this->remember($this->key, $callback, $this->lifeTime);
	}

	public function get(?string $key = null, ?int $time = null): mixed
	{
		$key ??= $this->key;

		return CacheApi::get($this->prefix . $key, $time ?? $this->lifeTime);
	}

	public function put(?string $key = null, mixed $value = null, ?int $time = null): void
	{
		$key ??= $this->key;

		CacheApi::put($this->prefix . $key, $value, $time ?? $this->lifeTime);
	}

	public function forget(?string $key = null): void
	{
		$key ??= $this->key;

		$this->put($key);
	}

	public function flush(): void
	{
		CacheApi::clean();
	}
}
