<?php

declare(strict_types=1);

namespace Bugo\LightPortal;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;

final class ConfigProvider
{
	public function __invoke(): array
	{
		return [
			'dependencies' => $this->getDependencies(),
			'filters'      => $this->getFilterConfig(),
		];
	}

	public function getDependencies(): array
	{
		return [
			'aliases'   => [
				'EventManager' => EventManager::class,
				EventManagerInterface::class => EventManager::class,
			],
			'factories' => [
				AddonHandler::class           => Service\AddonHandlerFactory::class,
				App::class                    => Service\AppFactory::class,
				Actions\Block::class          => InvokableFactory::class,
				Actions\BoardIndex::class     => InvokableFactory::class,
				Actions\BoardIndexNext::class => InvokableFactory::class,
				Actions\Category::class       => InvokableFactory::class,
				Actions\FrontPage::class      => InvokableFactory::class,
				Actions\Page::class           => InvokableFactory::class,
				Actions\Tag::class            => InvokableFactory::class,
				EventManager::class           => Service\EventManagerFactory::class,
				Events\Listeners\SmfHookListener::class => Events\Listeners\SmfHookListenerFactory::class,
				Integration::class                       => Service\IntegrationFactory::class,
				Repositories\PluginRepository::class     => InvokableFactory::class,
			],
		];
	}

	public function getFilterConfig(): array
	{
		return [
			'aliases'   => [
				'snakeName' => Filters\SnakeNameFilter::class,
			],
			'factories' => [
				Filters\SnakeNameFilter::class => Filters\SnakeNameFilterFactory::class,
			],
		];
	}
}
