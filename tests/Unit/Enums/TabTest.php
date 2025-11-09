<?php

declare(strict_types=1);

use LightPortal\Enums\Tab;

arch()
    ->expect(Tab::class)
    ->toBeEnum();
