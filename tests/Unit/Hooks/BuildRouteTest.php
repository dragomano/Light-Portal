<?php

declare(strict_types=1);

use LightPortal\Hooks\BuildRoute;

arch()
    ->expect(BuildRoute::class)
    ->toBeInvokable();
