<?php

declare(strict_types=1);

use LightPortal\Enums\PortalHook;

arch()
    ->expect(PortalHook::class)
    ->toBeEnum();
