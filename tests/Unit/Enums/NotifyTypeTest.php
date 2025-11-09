<?php

declare(strict_types=1);

use LightPortal\Enums\NotifyType;

arch()
    ->expect(NotifyType::class)
    ->toBeEnum();
