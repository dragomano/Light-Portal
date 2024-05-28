<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Addons;

interface AddonManagerAwareInterface
{
    public function setAddonManager(AddonManager $addonManager): void;
    public function getAddonManager(): ?AddonManager;
}
