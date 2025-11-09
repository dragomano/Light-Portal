<?php

declare(strict_types=1);

use LightPortal\Enums\Action;

arch()
    ->expect(Action::class)
    ->toBeStringBackedEnum();
