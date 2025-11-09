<?php

declare(strict_types=1);

use LightPortal\Utils\Setting;

arch()
    ->expect(Setting::class)
    ->toHaveMethod('get');
