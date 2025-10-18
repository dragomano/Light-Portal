<?php

declare(strict_types=1);

use LightPortal\Hooks\ProfileAreas;

arch()
    ->expect(ProfileAreas::class)
    ->toBeInvokable();
