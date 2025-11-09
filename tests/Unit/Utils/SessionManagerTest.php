<?php

declare(strict_types=1);

use LightPortal\Utils\SessionManager;

arch()
    ->expect(SessionManager::class)
    ->toBeInvokable();
