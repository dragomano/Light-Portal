<?php

declare(strict_types=1);

use LightPortal\Events\EventManager;

arch()
    ->expect(EventManager::class)
    ->toHaveMethods(['addHookListener', 'dispatch', 'getAll']);
