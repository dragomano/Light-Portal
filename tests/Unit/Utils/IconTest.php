<?php

declare(strict_types=1);

use LightPortal\Utils\Icon;

arch()
    ->expect(Icon::class)
    ->toHaveMethods(['get', 'parse', 'all']);
