<?php

declare(strict_types=1);

use Bugo\LightPortal\Hooks\ProfileAreas;

arch()
    ->expect(ProfileAreas::class)
    ->toBeInvokable();
