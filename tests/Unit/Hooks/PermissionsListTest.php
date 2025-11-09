<?php

declare(strict_types=1);

use LightPortal\Hooks\PermissionsList;

arch()
    ->expect(PermissionsList::class)
    ->toBeInvokable();
