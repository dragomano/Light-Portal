<?php

declare(strict_types=1);

use LightPortal\Enums\VarType;

arch()
    ->expect(VarType::class)
    ->toBeEnum();
