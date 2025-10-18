<?php

declare(strict_types=1);

use LightPortal\Integration;

arch()
    ->expect(Integration::class)
    ->toBeInvokable();
