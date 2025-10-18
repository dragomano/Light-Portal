<?php

declare(strict_types=1);

use Bugo\Compat\Routable;
use LightPortal\Routes\Portal;

arch()
    ->expect(Portal::class)
    ->toImplement(Routable::class);
