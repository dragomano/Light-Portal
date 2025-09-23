<?php

declare(strict_types=1);

use Bugo\LightPortal\Utils\Cache;
use Bugo\LightPortal\Utils\CacheInterface;

arch()
    ->expect(Cache::class)
    ->toImplement(CacheInterface::class)
    ->toHaveMethods(['withKey', 'setLifeTime', 'remember', 'setFallback', 'get', 'put', 'forget', 'flush']);
