<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Events\Listeners;

use Bugo\LightPortal\Events\Event;
use Bugo\LightPortal\Filters\SnakeNameFilter;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;

final class SmfHookListener extends AbstractListenerAggregate
{

	public function __construct(
		private SnakeNameFilter $snakeNameFilter,
	) {

	}

	public function attach(EventManagerInterface $events, $priority = 1)
	{
		$this->listeners[] = $events->attach(
			Event::SmfHook->value,
			[$this, 'onSmfHook'],
			$priority
		);
	}

	public function onSmfHook(EventInterface $event)
	{
		$integration = $event->getTarget();
		$integration->init();
	}
}
