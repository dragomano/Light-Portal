<?php

declare(strict_types=1);

use Bugo\Compat\Tasks\BackgroundTask;
use Bugo\LightPortal\Tasks\Notifier;

arch()
    ->expect(Notifier::class)
    ->toImplement(BackgroundTask::class);
