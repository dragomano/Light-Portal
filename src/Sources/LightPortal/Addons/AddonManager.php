<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Addons;

use Bugo\LightPortal\Addons\AddonInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;

use function get_debug_type;
use function sprintf;

final class AddonManager extends AbstractPluginManager
{
    /**
     * Default instance type
     *
     * @inheritDoc
     */
    protected $instanceOf = AddonInterface::class;

    public function __construct($configOrContainer, $config)
    {
        parent::__construct($configOrContainer, $config);
    }

    /**
     * @param mixed $instance
     * @psalm-assert AddonInterface $instance
     */
    public function validate(mixed $instance): void
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s expects only to create instances of %s; %s is invalid',
                static::class,
                (string) $this->instanceOf,
                get_debug_type($instance)
            ));
        }
    }
}
