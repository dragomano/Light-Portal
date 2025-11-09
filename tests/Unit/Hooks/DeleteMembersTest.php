<?php

declare(strict_types=1);

use LightPortal\Hooks\DeleteMembers;

arch()
    ->expect(DeleteMembers::class)
    ->toBeInvokable();
