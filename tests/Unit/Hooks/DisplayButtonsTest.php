<?php

declare(strict_types=1);

use LightPortal\Hooks\DisplayButtons;

arch()
    ->expect(DisplayButtons::class)
    ->toBeInvokable();
