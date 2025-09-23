<?php

declare(strict_types=1);

use Bugo\LightPortal\Integration;

arch()
    ->expect(Integration::class)
    ->toBeInvokable();
