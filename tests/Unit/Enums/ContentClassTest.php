<?php

declare(strict_types=1);

use LightPortal\Enums\ContentClass;

arch()
    ->expect(ContentClass::class)
    ->toBeStringBackedEnum();
