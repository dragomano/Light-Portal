<?php

declare(strict_types=1);

use Bugo\LightPortal\Hooks\Redirect;

arch()
    ->expect(Redirect::class)
    ->toBeInvokable();
