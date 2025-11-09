<?php

declare(strict_types=1);

use LightPortal\Hooks\FetchAlerts;

arch()
    ->expect(FetchAlerts::class)
    ->toBeInvokable();
