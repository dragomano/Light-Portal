<?php

declare(strict_types=1);

use LightPortal\Hooks\DefaultAction;

arch()
    ->expect(DefaultAction::class)
    ->toBeInvokable();
