<?php

declare(strict_types=1);

use Bugo\LightPortal\Hooks\PreCssOutput;

arch()
    ->expect(PreCssOutput::class)
    ->toBeInvokable();
