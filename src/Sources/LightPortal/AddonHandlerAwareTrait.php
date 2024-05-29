<?php

declare(strict_types=1);

namespace Bugo\LightPortal;

use Bugo\LightPortal\AddonHandler;

/**
 * Implemenation of AddonHandlerAwareInterface
 */
trait AddonHandlerAwareTrait
{
    protected static AddonHandler $addonHandler;

    public function setAddonHandler(AddonHandler $addonHandler): void
    {
        static::$addonHandler = $addonHandler;
    }

    public function getAddonHandler(): ?AddonHandler
    {
        return static::$addonHandler;
    }
}
