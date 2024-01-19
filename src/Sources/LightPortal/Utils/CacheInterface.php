<?php declare(strict_types=1);

/**
 * CacheInterface.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Utils;

interface CacheInterface
{
	public function setLifeTime(int $lifeTime): self;

	public function setFallback(string $className, string $methodName, ...$params): mixed;

	public function get(string $key, ?int $time = null): ?array;

	public function put(string $key, ?array $value, ?int $time = null): void;

	public function forget(string $key): void;

	public function flush(): void;
}
