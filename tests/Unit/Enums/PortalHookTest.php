<?php

declare(strict_types=1);

use Bugo\LightPortal\Enums\PortalHook;

arch()
    ->expect(PortalHook::class)
    ->toBeEnum();
