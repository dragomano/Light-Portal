<?php

declare(strict_types=1);

use Bugo\LightPortal\Areas\TagArea;
use Bugo\LightPortal\Areas\Traits\HasArea;

arch()
    ->expect(TagArea::class)
    ->toUseTrait(HasArea::class);
