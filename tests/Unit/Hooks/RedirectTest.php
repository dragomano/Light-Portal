<?php

declare(strict_types=1);

use LightPortal\Hooks\Redirect;

arch()
    ->expect(Redirect::class)
    ->toBeInvokable();
