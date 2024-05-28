<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Addons;

use Laminas\ServiceManager\Initializer\InitializerInterface;
use Psr\Container\ContainerInterface;

final class AddonInitializer implements InitializerInterface
{

    public function __invoke(ContainerInterface $container, $instance)
    { }

}
