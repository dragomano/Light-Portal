<?php

declare(strict_types=1);

use Bugo\LightPortal\Enums\AlertAction;

arch()
    ->expect(AlertAction::class)
    ->toBeEnum();
