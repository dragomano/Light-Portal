<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Events\Listeners;

use Bugo\LightPortal\Filters\SnakeNameFilter;
use Laminas\Filter\FilterPluginManager;
use Psr\Container\ContainerInterface;

final class SmfHookListenerFactory
{
	public function __invoke(ContainerInterface $container): SmfHookListener
	{
		$filterManager = $container->get(FilterPluginManager::class);
		$snakeNameFilter = $filterManager->get(SnakeNameFilter::class);
		return new SmfHookListener(
			$snakeNameFilter
		);
	}
}
