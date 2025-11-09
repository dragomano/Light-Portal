<?php

declare(strict_types=1);

use LightPortal\Hooks\Actions;

arch()
    ->expect(Actions::class)
    ->toBeInvokable();
