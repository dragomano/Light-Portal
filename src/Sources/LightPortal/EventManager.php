<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Doctrine\Common\EventManager as DoctrineEventManager;

use function array_map;
use function array_filter;
use function in_array;
use function method_exists;

class EventManager
{
	protected DoctrineEventManager $eventManager;

	public function __construct()
	{
		$this->eventManager = new DoctrineEventManager();
	}

	private array $contentHooks = [
		PortalHook::prepareBlockParams,
		PortalHook::validateBlockParams,
		PortalHook::prepareBlockFields,
		PortalHook::parseContent,
		PortalHook::prepareContent
	];

	public function addListeners(array $hooks, Plugin $listener): void
	{
		$hooks = array_map(fn($item) => $item->name, $hooks);
		$hooks = array_filter($hooks, fn($item) => method_exists($listener, $item));

		$this->eventManager->addEventListener($hooks, $listener);
	}

	public function dispatch(PortalHook $hook, ?Event $args = null): void
	{
		foreach ($this->getAll($hook->name) as $listener) {
			if (
				$listener->type !== PluginType::BLOCK_OPTIONS->name()
				&& in_array($hook, $this->contentHooks)
				&& isset($args->args->type)
			) {
				if ($args->args->type !== $listener->getShortName()) {
					continue;
				}
			}

			$args ??= new Event(new class {});

			$listener->{$hook->name}($args);
		}
	}

	public function getAll(string $event = ''): array
	{
		return $event ? $this->eventManager->getListeners($event) : $this->eventManager->getAllListeners();
	}
}
