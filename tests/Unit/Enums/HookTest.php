<?php

declare(strict_types=1);

use LightPortal\Enums\Hook;

arch()
    ->expect(Hook::class)
    ->toBeEnum();
