<?php

declare(strict_types=1);

use LightPortal\Hooks\RouteParsers;

arch()
    ->expect(RouteParsers::class)
    ->toBeInvokable();
