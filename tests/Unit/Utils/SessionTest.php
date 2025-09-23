<?php

declare(strict_types=1);

use Bugo\LightPortal\Utils\Session;
use Bugo\LightPortal\Utils\GlobalArray;

arch()
    ->expect(Session::class)
    ->toExtend(GlobalArray::class)
    ->toHaveMethods(['withKey', 'free']);
