<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Service;

use Bugo\LightPortal\AddonHandler;
use Bugo\LightPortal\App;
use Bugo\LightPortal\Events\Listeners\HitchSmfListener;
use Bugo\LightPortal\Integration;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Psr\Container\ContainerInterface;

final class AppFactory
{
	public function __invoke(ContainerInterface $container): App
	{
		$eventManager = $container->get(EventManagerInterface::class);
		$eventManager->setIdentifiers([App::class]);
		/** @var HitchSmfListener */
		$listener = $container->get(HitchSmfListener::class);
		$listener->attach($eventManager, 10000); // attach listener and insure this listener runs first
		$app = new App(
			$container->get(AddonHandler::class),
			$container->get(Integration::class)
		);
		$app->setEventManager($eventManager);
		return $app;
	}
}
