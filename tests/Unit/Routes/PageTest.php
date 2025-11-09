<?php

declare(strict_types=1);

use Bugo\Compat\Routable;
use LightPortal\Routes\Page;

arch()
    ->expect(Page::class)
    ->toImplement(Routable::class);
