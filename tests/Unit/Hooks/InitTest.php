<?php

declare(strict_types=1);

use LightPortal\Hooks\Init;

arch()
    ->expect(Init::class)
    ->toBeInvokable();
