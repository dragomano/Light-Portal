<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Service;

use Bugo\LightPortal\AddonHandler;
use Bugo\LightPortal\App;
use Bugo\LightPortal\Integration;
use Laminas\EventManager\EventManager;
use Psr\Container\ContainerInterface;

final class AppFactory
{
	public function __invoke(ContainerInterface $container): App
	{
		return new App(
			$container->get(EventManager::class),
			$container->get(AddonHandler::class),
			$container->get(Integration::class)
		);
	}
}
