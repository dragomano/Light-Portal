<?php

declare(strict_types=1);

use Bugo\LightPortal\Enums\Tab;

arch()
    ->expect(Tab::class)
    ->toBeEnum();
