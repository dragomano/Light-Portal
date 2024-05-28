<?php

declare(strict_types=1);

namespace Bugo\LightPortal;

interface AddonHandlerAwareInterface
{
    public function setAddonHandler(AddonHandler $addonHandler): void;
    public function getAddonHandler(): ?AddonHandler;
}
