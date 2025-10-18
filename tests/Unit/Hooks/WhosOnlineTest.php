<?php

declare(strict_types=1);

use LightPortal\Hooks\WhosOnline;

arch()
    ->expect(WhosOnline::class)
    ->toBeInvokable();
