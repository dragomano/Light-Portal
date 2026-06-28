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

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

if (! defined('SMF'))
	die('No direct access...');

final class Params implements ArrayAccess, IteratorAggregate, Countable
{
	public function __construct(private array $parameters = []) {}

	public function get(string $key, mixed $default = null): mixed
	{
		return array_key_exists($key, $this->parameters)
			? $this->parameters[$key]
			: $default;
	}

	public function set(string $key, mixed $value): self
	{
		$this->parameters[$key] = $value;

		return $this;
	}

	public function has(string $key): bool
	{
		return array_key_exists($key, $this->parameters);
	}

	public function remove(string $key): void
	{
		unset($this->parameters[$key]);
	}

	public function replace(array $parameters): self
	{
		$this->parameters = $parameters;

		return $this;
	}

	public function merge(array $parameters): self
	{
		$this->parameters = array_merge($this->parameters, $parameters);

		return $this;
	}

	public function all(): array
	{
		return $this->parameters;
	}

	public function isEmpty(): bool
	{
		return $this->parameters === [];
	}

	public function count(): int
	{
		return count($this->parameters);
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->parameters);
	}

	public function offsetExists(mixed $offset): bool
	{
		return is_string($offset) && $this->has($offset);
	}

	public function offsetGet(mixed $offset): mixed
	{
		return is_string($offset)
			? $this->get($offset)
			: null;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (! is_string($offset)) {
			throw new InvalidArgumentException('Parameter name must be a string.');
		}

		$this->set($offset, $value);
	}

	public function offsetUnset(mixed $offset): void
	{
		if (is_string($offset)) {
			$this->remove($offset);
		}
	}
}
