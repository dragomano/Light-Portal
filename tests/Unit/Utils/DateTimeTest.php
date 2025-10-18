<?php

declare(strict_types=1);

use LightPortal\Utils\DateTime;

arch()
    ->expect(DateTime::class)
    ->toHaveMethods(['relative', 'get', 'getValueForDate', 'dateCompare', 'getLocalDate']);
