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

use ArrayAccess;

class ParamWrapper implements ArrayAccess
{
	public function __construct(private array $storage = []) {}

	public function offsetExists(mixed $offset): bool
	{
		return isset($this->storage[$offset]);
	}

	public function offsetGet(mixed $offset): mixed
	{
		return $this->storage[$offset] ?? null;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->storage[$offset] = $value;
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->storage[$offset]);
	}
}
