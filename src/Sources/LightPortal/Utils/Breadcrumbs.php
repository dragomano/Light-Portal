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

use Bugo\Compat\Utils;

use function array_filter;

class Breadcrumbs
{
	private array $items;

	public function __construct()
	{
		Utils::$context['linktree'] ??= [];

		$this->items = &Utils::$context['linktree'];
	}

	public function add(string $name, ?string $url = null, ?string $before = null, ?string $after = null): self
	{
		$this->items[] = array_filter([
			'name'         => $name,
			'url'          => $url,
			'extra_before' => $before,
			'extra_after'  => $after,
		]);

		return $this;
	}

	public function get(): array
	{
		return $this->items;
	}

	public function getByIndex(int $index): ?array
	{
		return $this->items[$index] ?? null;
	}

	public function update(int $index, string $key, mixed $value): self
	{
		if (isset($this->items[$index])) {
			$this->items[$index][$key] = $value;
		}

		return $this;
	}

	public function remove(int $index): self
	{
		if (isset($this->items[$index])) {
			unset($this->items[$index]);
		}

		return $this;
	}

	public function clear(): void
	{
		$this->items = [];
	}
}
