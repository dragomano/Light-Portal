<?php

declare(strict_types=1);

use Bugo\LightPortal\Utils\Language;

arch()
    ->expect(Language::class)
    ->toHaveMethods(['getFallbackValue', 'getNameFromLocale', 'getCurrent', 'isDefault', 'prepareList']);
