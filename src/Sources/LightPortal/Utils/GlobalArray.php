<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Utils;

use function array_flip;
use function array_intersect_key;
use function array_reduce;

if (! defined('SMF'))
	die('No direct access...');

abstract class GlobalArray
{
	protected array $storage = [];

	public function &get(string $key): mixed
	{
		return $this->storage[$key];
	}

	public function put(string $key, mixed $value): void
	{
		$this->storage[$key] = &$value;
	}

	public function all(): array
	{
		return $this->storage;
	}

	public function only(array $keys): array
	{
		return array_intersect_key($this->all(), array_flip($keys));
	}

	public function has(array|string $keys): bool
	{
		return array_reduce((array) $keys, fn($carry, $key) => $carry && isset($this->storage[$key]), true);
	}

	public function hasNot(array|string $keys): bool
	{
		return array_reduce((array) $keys, fn($carry, $key) => $carry && !isset($this->storage[$key]), true);
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
