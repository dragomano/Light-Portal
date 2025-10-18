<?php

declare(strict_types=1);

use LightPortal\Enums\TitleClass;

arch()
    ->expect(TitleClass::class)
    ->toBeStringBackedEnum();
