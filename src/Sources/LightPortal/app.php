<?php

declare(strict_types=1);

namespace Bugo\LightPortal;

use Bugo\LightPortal\Events\Event as EventType;
use Bugo\LightPortal\Integration;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;

/**
 * app.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

if (! defined('SMF'))
	die('We gotta get out of here!');

final class App implements EventManagerAwareInterface
{
	use EventManagerAwareTrait;

	public function __construct(
		private AddonHandler $addonHandler,
		private Integration $integration
	) {
	}

	public function run()
	{
		$this->integration->setAddonHandler($this->addonHandler);
		$event = new Event(EventType::SmfHook->value, $this->integration, ['param_one' => 'value_one']);
		$this->getEventManager()->triggerEvent($event);
	}
}
