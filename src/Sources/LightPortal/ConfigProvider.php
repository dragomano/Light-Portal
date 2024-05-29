<?php

declare(strict_types=1);

namespace Bugo\LightPortal;

use Laminas\EventManager\EventManager;
use Laminas\ServiceManager\Factory\InvokableFactory;

final class ConfigProvider
{
	public function __invoke(): array
	{
		return [
			'dependencies' => $this->getDependencies()
		];
	}

	public function getDependencies(): array
	{
		return [
			'aliases'   => [
				'EventManager' => EventManager::class,
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
				Integration::class            => Service\IntegrationFactory::class,
				Repositories\PluginRepository::class => InvokableFactory::class,
			],
		];
	}
}
