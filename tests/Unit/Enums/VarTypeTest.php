<?php

declare(strict_types=1);

use Bugo\LightPortal\Enums\VarType;

arch()
    ->expect(VarType::class)
    ->toBeEnum();
