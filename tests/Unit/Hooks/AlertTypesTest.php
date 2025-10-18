<?php

declare(strict_types=1);

use LightPortal\Hooks\AlertTypes;

arch()
    ->expect(AlertTypes::class)
    ->toBeInvokable();
