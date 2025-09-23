<?php

declare(strict_types=1);

use Bugo\LightPortal\Enums\EntryType;

arch()
    ->expect(EntryType::class)
    ->toBeEnum();
