<?php

declare(strict_types=1);

use Bugo\LightPortal\Enums\TitleClass;

arch()
    ->expect(TitleClass::class)
    ->toBeStringBackedEnum();
