<?php

declare(strict_types=1);

use LightPortal\Utils\Notifier;

arch()
    ->expect(Notifier::class)
    ->toHaveMethod('notify');
