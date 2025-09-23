<?php

declare(strict_types=1);

use Bugo\LightPortal\Events\EventManager;

arch()
    ->expect(EventManager::class)
    ->toHaveMethods(['addHookListener', 'dispatch', 'getAll']);
