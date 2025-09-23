<?php

declare(strict_types=1);

use Bugo\LightPortal\Enums\PortalSubAction;

arch()
    ->expect(PortalSubAction::class)
    ->toBeEnum();
