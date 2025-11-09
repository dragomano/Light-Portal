<?php

declare(strict_types=1);

use LightPortal\Hooks\LoadTheme;

arch()
    ->expect(LoadTheme::class)
    ->toBeInvokable();
