<?php

declare(strict_types=1);

use LightPortal\Hooks\CurrentAction;

arch()
    ->expect(CurrentAction::class)
    ->toBeInvokable();
