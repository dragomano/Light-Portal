<?php

declare(strict_types=1);

use LightPortal\Utils\Language;

arch()
    ->expect(Language::class)
    ->toHaveMethods(['getFallbackValue', 'getNameFromLocale', 'getCurrent', 'isDefault', 'prepareList']);
