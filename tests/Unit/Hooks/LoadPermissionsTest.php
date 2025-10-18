<?php

declare(strict_types=1);

use LightPortal\Hooks\LoadPermissions;

arch()
    ->expect(LoadPermissions::class)
    ->toBeInvokable();
