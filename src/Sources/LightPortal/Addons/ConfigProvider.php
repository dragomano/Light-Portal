<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Addons;

use Bugo\LightPortal\Addons\AddonManager;
use Bugo\LightPortal\Addons\AddonManagerFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'lp_addons'    => $this->getAddonsConfig(),
        ];
    }

    /**
     * Configuration for ServiceManager for the AddonManager
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            'aliases'      => [],
            'factories'    => [
                AddonManager::class => AddonManagerFactory::class
            ],
            'delegators'   => [],
        ];
    }

    /**
     * Default configuration to seed the AddonManager
     * @return array
     */
    public function getAddonsConfig(): array
    {
        return [
            'initializers' => [],
            'aliases'      => [
				'userInfo' => Addons\UserInfo\UserInfo::class,
			],
            'factories'    => [
				Addons\UserInfo\UserInfo::class => InvokableFactory::class
			],
        ];
    }
}
