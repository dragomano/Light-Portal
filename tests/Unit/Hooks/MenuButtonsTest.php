<?php

declare(strict_types=1);

use LightPortal\Hooks\MenuButtons;

arch()
    ->expect(MenuButtons::class)
    ->toBeInvokable();
