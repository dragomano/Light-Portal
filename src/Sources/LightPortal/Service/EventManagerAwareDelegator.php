<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Service;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\EventManagerAwareInterface;
use Psr\Container\ContainerInterface;

final class EventManagerAwareDelegator
{
	public function __invoke(
		ContainerInterface $container,
        string $serviceName,
        callable $callback
	) {
		$service = $callback(); // calls the original factory
		if (! $service instanceof EventManagerAwareInterface) {
			return $service; // just in case this has been tied to an instance that is not EventManagerAware
		}
		/** @var EventManager */
		$eventManager = $container->get(EventManagerInterface::class); // call it by its interface alias
		$eventManager->setIdentifiers([$service::class]);
		$service->setEventManager($eventManager);
	}
}
