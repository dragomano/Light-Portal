<?php

declare(strict_types=1);

use LightPortal\Hooks\LoadIllegalGuestPermissions;

arch()
    ->expect(LoadIllegalGuestPermissions::class)
    ->toBeInvokable();
