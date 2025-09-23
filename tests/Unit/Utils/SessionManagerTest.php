<?php

declare(strict_types=1);

use Bugo\LightPortal\Utils\SessionManager;

arch()
    ->expect(SessionManager::class)
    ->toBeInvokable();
