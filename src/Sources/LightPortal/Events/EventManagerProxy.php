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

namespace LightPortal\Events;

use LightPortal\Enums\PortalHook;

readonly class EventManagerProxy implements EventDispatcherInterface
{
	public function __construct(private EventManagerFactory $factory, private array $plugins = []) {}

	public function dispatch(PortalHook $hook, array $params = []): void
	{
		$manager = ($this->factory)($this->plugins);
		$manager->dispatch($hook, $params);
	}

	public function withPlugins(array $plugins): self
	{
		return new self($this->factory, $plugins);
	}

	public function getAll(string $event = ''): array
	{
		$manager = ($this->factory)($this->plugins);

		return $manager->getAll($event);
	}
}
