<?php

declare(strict_types=1);

use LightPortal\Enums\EntryType;

arch()
    ->expect(EntryType::class)
    ->toBeEnum();
