<?php

declare(strict_types=1);

use LightPortal\Hooks\Integration;

arch()
    ->expect(Integration::class)
    ->toBeInvokable();
