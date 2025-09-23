<?php

declare(strict_types=1);

use Bugo\LightPortal\Utils\Request;
use Bugo\LightPortal\Utils\GlobalArray;

arch()
    ->expect(Request::class)
    ->toExtend(GlobalArray::class)
    ->toHaveMethods(['is', 'isNot', 'json', 'url']);
