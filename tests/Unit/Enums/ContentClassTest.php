<?php

declare(strict_types=1);

use Bugo\LightPortal\Enums\ContentClass;

arch()
    ->expect(ContentClass::class)
    ->toBeStringBackedEnum();
