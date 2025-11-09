<?php

declare(strict_types=1);

use LightPortal\Enums\AlertAction;

arch()
    ->expect(AlertAction::class)
    ->toBeEnum();
