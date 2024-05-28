<?php

declare(strict_types=1);

use Bugo\LightPortal\AddonHandler;

/**
 * Implemenation of AddonHandlerAwareInterface
 */
trait AddonHandlerAwareTrait
{
    protected AddonHandler $addonHandler;

    public function setAddonHandler(AddonHandler $addonHandler): void
    {
        $this->addonHandler = $addonHandler;
    }

    public function getAddonHandler(): ?AddonHandler
    {
        return $this->addonHandler;
    }
}
