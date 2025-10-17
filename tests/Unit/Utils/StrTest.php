<?php

declare(strict_types=1);

use Bugo\LightPortal\Utils\Str;

arch()
    ->expect(Str::class)
    ->toHaveMethods([
        'cleanBbcode',
        'getSnakeName',
        'getCamelName',
        'getTeaser',
        'getImageFromText',
        'decodeHtmlEntities',
        'html',
        'typed',
    ]);
