<?php

declare(strict_types=1);

use Bugo\LightPortal\Hooks\LoadTheme;

arch()
    ->expect(LoadTheme::class)
    ->toBeInvokable();
