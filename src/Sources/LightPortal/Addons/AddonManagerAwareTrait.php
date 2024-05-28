<?php

declare(strict_types=1);

namespace Bugo\LightPortal\Addons;

use Bugo\LightPortal\Addons\AddonManager;

trait AddonManagerAwareTrait
{
    protected AddonManager $addonManager;

    public function setAddonManager(AddonManager $addonManager): void
    {
        $this->addonManager = $addonManager;
    }

    public function getAddonManager(): ?AddonManager
    {
        return $this->addonManager;
    }
}
