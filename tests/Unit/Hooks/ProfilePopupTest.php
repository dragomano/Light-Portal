<?php

declare(strict_types=1);

use LightPortal\Hooks\ProfilePopup;

arch()
    ->expect(ProfilePopup::class)
    ->toBeInvokable();
