<?php

declare(strict_types=1);

use Bugo\LightPortal\Hooks\RouteParsers;

arch()
    ->expect(RouteParsers::class)
    ->toBeInvokable();
