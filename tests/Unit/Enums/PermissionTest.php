<?php

declare(strict_types=1);

use Bugo\LightPortal\Enums\Permission;

arch()
    ->expect(Permission::class)
    ->toBeIntBackedEnum();
