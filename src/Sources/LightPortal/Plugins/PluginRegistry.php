<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Plugins;

class PluginRegistry
{
	private array $plugins = [];

	public function add(string $name, array $plugin): void
	{
		if (! $this->has($name)) {
			$this->plugins[$name] = $plugin;
		}
	}

	public function has(string $name): bool
	{
		return isset($this->plugins[$name]);
	}

	public function get(string $name): ?array
	{
		return $this->plugins[$name] ?? null;
	}

	public function getAll(): array
	{
		return $this->plugins;
	}
}
