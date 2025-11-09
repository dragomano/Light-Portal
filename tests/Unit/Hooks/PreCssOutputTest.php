<?php

declare(strict_types=1);

use LightPortal\Hooks\PreCssOutput;

arch()
    ->expect(PreCssOutput::class)
    ->toBeInvokable();
