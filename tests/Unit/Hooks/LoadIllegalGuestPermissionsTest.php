<?php

declare(strict_types=1);

use Bugo\LightPortal\Hooks\LoadIllegalGuestPermissions;

arch()
    ->expect(LoadIllegalGuestPermissions::class)
    ->toBeInvokable();
