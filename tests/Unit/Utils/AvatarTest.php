<?php

declare(strict_types=1);

use LightPortal\Utils\Avatar;

arch()
    ->expect(Avatar::class)
    ->toHaveMethods(['get', 'getWithItems']);
