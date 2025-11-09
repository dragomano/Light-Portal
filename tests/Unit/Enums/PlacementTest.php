<?php

declare(strict_types=1);

use LightPortal\Enums\Placement;

arch()
    ->expect(Placement::class)
    ->toBeEnum();
