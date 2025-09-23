<?php

declare(strict_types=1);

use Bugo\LightPortal\Enums\Hook;

arch()
    ->expect(Hook::class)
    ->toBeEnum();
