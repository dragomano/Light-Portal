<?php

declare(strict_types=1);

use LightPortal\Enums\Status;

arch()
    ->expect(Status::class)
    ->toBeIntBackedEnum();
