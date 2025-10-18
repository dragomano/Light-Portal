<?php

declare(strict_types=1);

use LightPortal\Utils\Session;
use LightPortal\Utils\GlobalArray;

arch()
    ->expect(Session::class)
    ->toExtend(GlobalArray::class)
    ->toHaveMethods(['withKey', 'free']);
