<?php

declare(strict_types=1);

use LightPortal\Enums\ContentType;

arch()
    ->expect(ContentType::class)
    ->toBeEnum();
