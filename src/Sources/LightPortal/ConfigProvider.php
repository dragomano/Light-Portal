<?php

declare(strict_types=1);

namespace Bugo\LightPortal;

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
            'aliases'   => [],
            'factories' => [
                AddonHandler::class           => Service\AddonHandlerFactory::class,
                Actions\Block::class          => InvokableFactory::class,
                Actions\BoardIndex::class     => InvokableFactory::class,
                Actions\BoardIndexNext::class => InvokableFactory::class,
                Actions\Category::class       => InvokableFactory::class,
                Actions\FrontPage::class      => InvokableFactory::class,
                Actions\Page::class           => InvokableFactory::class,
                Actions\Tag::class            => InvokableFactory::class,
                Repositories\PluginRepository::class => InvokableFactory::class,
            ],
        ];
    }
}
