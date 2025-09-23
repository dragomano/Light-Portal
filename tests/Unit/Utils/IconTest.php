<?php

declare(strict_types=1);

use Bugo\LightPortal\Utils\Icon;

arch()
    ->expect(Icon::class)
    ->toHaveMethods(['get', 'parse', 'all']);
