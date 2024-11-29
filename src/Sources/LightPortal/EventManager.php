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
use Bugo\LightPortal\Plugins\PluginInterface;
use Doctrine\Common\EventManager as DoctrineEventManager;

use function array_map;
use function array_filter;
use function in_array;
use function method_exists;

class EventManager
{
	private readonly DoctrineEventManager $eventManager;

	private array $contentHooks = [
		PortalHook::prepareBlockParams,
		PortalHook::validateBlockParams,
		PortalHook::prepareBlockFields,
		PortalHook::parseContent,
		PortalHook::prepareContent
	];

	public function __construct()
	{
		$this->eventManager = new DoctrineEventManager();
	}

	public function addListeners(array $hooks, PluginInterface $listener): void
	{
		$hooks = array_map(fn($item) => $item->name, $hooks);
		$hooks = array_filter($hooks, fn($item) => method_exists($listener, $item));

		$this->eventManager->addEventListener($hooks, $listener);
	}

	public function dispatch(PortalHook $hook, ?Event $e = null): void
	{
		/* @var PluginInterface $listener */
		foreach ($this->getAll($hook->name) as $listener) {
			if (
				$listener->type !== PluginType::BLOCK_OPTIONS->name()
				&& in_array($hook, $this->contentHooks)
				&& isset($e->args->type)
			) {
				if ($e->args->type !== $listener->getShortName()) {
					continue;
				}
			}

			$e ??= new Event(new class {});

			$listener->{$hook->name}($e);
		}
	}

	public function getAll(string $event = ''): array
	{
		return $event ? $this->eventManager->getListeners($event) : $this->eventManager->getAllListeners();
	}
}
