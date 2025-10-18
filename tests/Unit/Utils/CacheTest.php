<?php

declare(strict_types=1);

use LightPortal\Utils\Cache;
use LightPortal\Utils\CacheInterface;

arch()
    ->expect(Cache::class)
    ->toImplement(CacheInterface::class)
    ->toHaveMethods(['withKey', 'setLifeTime', 'remember', 'setFallback', 'get', 'put', 'forget', 'flush']);
