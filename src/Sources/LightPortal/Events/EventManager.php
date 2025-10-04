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

namespace Bugo\LightPortal\Events;

use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\PluginInterface;
use Doctrine\Common\EventManager as DoctrineEventManager;

class EventManager
{
	private readonly DoctrineEventManager $eventManager;

	private array $contentHooks = [
		PortalHook::prepareBlockParams,
		PortalHook::validateBlockParams,
		PortalHook::prepareBlockFields,
		PortalHook::parseContent,
		PortalHook::prepareContent,
	];

	private array $layerHooks = [
		PortalHook::addLayerAbove,
		PortalHook::addLayerBelow,
	];

	public function __construct()
	{
		$this->eventManager = new DoctrineEventManager();
	}

	public function addHookListener(PortalHook $hook, PluginInterface $listener): void
	{
		$this->eventManager->addEventListener($hook->name, $listener);
	}

	public function dispatch(PortalHook $hook, array $params = []): void
	{
		$args = $hook->createArgs($params);

		/* @var PluginInterface $listener */
		foreach ($this->getAll($hook->name) as $listener) {
			if (
				$listener->type !== PluginType::BLOCK_OPTIONS->name()
				&& in_array($hook, $this->contentHooks)
				&& isset($args->type)
			) {
				if ($args->type !== $listener->getSnakeName()) {
					continue;
				}
			}

			if (in_array($hook, $this->layerHooks) && ! $listener->isEnabled()) {
				continue;
			}

			$event = new Event($args);

			$listener->{$hook->name}($event);
		}
	}

	public function getAll(string $event = ''): array
	{
		return $event ? $this->eventManager->getListeners($event) : $this->eventManager->getAllListeners();
	}
}
