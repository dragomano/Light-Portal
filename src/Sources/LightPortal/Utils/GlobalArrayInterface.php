<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Utils;

interface GlobalArrayInterface
{
	public function &get(string $key): mixed;

	public function put(string $key, mixed $value): void;

	public function all(): array;

	public function only(array $keys): array;

	public function except(array $keys): array;

	public function has(array|string $keys): bool;

	public function hasNot(array|string $keys): bool;

	public function isEmpty(string $key): bool;

	public function isNotEmpty(string $key): bool;
}
