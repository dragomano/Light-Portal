<?php

declare(strict_types=1);

use Bugo\LightPortal\Hooks\CurrentAction;

arch()
    ->expect(CurrentAction::class)
    ->toBeInvokable();
