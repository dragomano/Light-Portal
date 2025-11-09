<?php

declare(strict_types=1);

use LightPortal\Enums\PortalSubAction;

arch()
    ->expect(PortalSubAction::class)
    ->toBeEnum();
