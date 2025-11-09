<?php

declare(strict_types=1);

use LightPortal\Utils\Request;
use LightPortal\Utils\GlobalArray;

arch()
    ->expect(Request::class)
    ->toExtend(GlobalArray::class)
    ->toHaveMethods(['is', 'isNot', 'json', 'url']);
