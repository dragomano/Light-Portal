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

use const LP_CACHE_TIME;

interface CacheInterface
{
	public function withKey(?string $key): self;

	public function setLifeTime(int $lifeTime): self;

	public function remember(string $key, callable $callback, int $time = LP_CACHE_TIME ?? 0): mixed;

	public function setFallback(callable $callback): mixed;

	public function get(string $key, int $time): mixed;

	public function put(string $key, mixed $value, int $time): void;

	public function forget(string $key): void;

	public function flush(): void;
}
