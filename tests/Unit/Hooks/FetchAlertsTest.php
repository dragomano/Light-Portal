<?php

declare(strict_types=1);

use Bugo\LightPortal\Hooks\FetchAlerts;

arch()
    ->expect(FetchAlerts::class)
    ->toBeInvokable();
