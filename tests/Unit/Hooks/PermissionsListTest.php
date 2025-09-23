<?php

declare(strict_types=1);

use Bugo\LightPortal\Hooks\PermissionsList;

arch()
    ->expect(PermissionsList::class)
    ->toBeInvokable();
