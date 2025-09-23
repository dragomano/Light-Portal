<?php

declare(strict_types=1);

use Bugo\LightPortal\Hooks\DisplayButtons;

arch()
    ->expect(DisplayButtons::class)
    ->toBeInvokable();
