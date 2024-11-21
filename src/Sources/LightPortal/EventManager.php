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

use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Plugin;
use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager as DoctrineEventManager;

use function array_map;
use function array_filter;
use function in_array;
use function method_exists;

class EventManager
{
	protected DoctrineEventManager $eventManager;

	private static self $instance;

	private array $contentHooks = [
		PortalHook::prepareBlockParams,
		PortalHook::validateBlockParams,
		PortalHook::prepareBlockFields,
		PortalHook::parseContent,
		PortalHook::prepareContent
	];

	public static function getInstance(): self
	{
		if (empty(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function addListeners(array $hooks, Plugin $listener): void
	{
		$hooks = array_map(fn($item) => $item->name, $hooks);
		$hooks = array_filter($hooks, fn($item) => method_exists($listener, $item));

		$this->eventManager->addEventListener($hooks, $listener);
	}

	public function dispatch(PortalHook $hook, EventArgs|null $eventArgs = null): void
	{
		foreach ($this->getAll($hook->name) as $listener) {
			if (
				$listener->type !== 'block_options'
				&& in_array($hook, $this->contentHooks)
				&& isset($eventArgs->args->type)
			) {
				if ($eventArgs->args->type !== $listener->getShortName()) {
					continue;
				}
			}

			$eventArgs ??= EventArgs::getEmptyInstance();

			$listener->{$hook->name}($eventArgs);
		}
	}

	public function getAll(string $event = ''): array
	{
		return $event ? $this->eventManager->getListeners($event) : $this->eventManager->getAllListeners();
	}

	private function __construct()
	{
		$this->eventManager = new DoctrineEventManager();
	}
}
