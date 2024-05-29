<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Service;

use Bugo\LightPortal\AddonHandler;
use Bugo\LightPortal\Addons\AddonManager;
use Bugo\LightPortal\Repositories\PluginRepository;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Psr\Container\ContainerInterface;

final class AddonHandlerFactory
{
    public function __invoke(ContainerInterface $container): AddonHandler
    {
        $pluginRepo = $container->get(PluginRepository::class);
        $settings = $pluginRepo->getSettings();
        if (empty($settings)) {
            throw new ServiceNotCreatedException('Plugin Settings could not be found.');
        }
        $handler = new AddonHandler($settings);
		$handler->setAddonManager($container->get(AddonManager::class));
		return $handler;
    }
}
