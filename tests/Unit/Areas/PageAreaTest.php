<?php

declare(strict_types=1);

use Bugo\LightPortal\Areas\PageArea;
use Bugo\LightPortal\Areas\Traits\HasArea;

arch()
    ->expect(PageArea::class)
    ->toUseTrait(HasArea::class);
