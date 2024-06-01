<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Addons;

use Laminas\ServiceManager\Exception;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class AddonManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AddonManager
    {
        if (! $container->has('config')) {
            throw new Exception\ServiceNotFoundException('AddonManager requires a Config service.');
        }

        $config = $container->get('config');
        if (empty($config['lp_addons'])) {
            throw new Exception\ServiceNotCreatedException('AddonManager could not be created due to missing addon configuration');
        }
        return new AddonManager($container, $config['lp_addons']);
    }
}
