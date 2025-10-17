<?php

declare(strict_types=1);

use Bugo\LightPortal\Utils\Notifier;

arch()
    ->expect(Notifier::class)
    ->toHaveMethod('notify');
