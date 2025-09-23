<?php

declare(strict_types=1);

use Bugo\LightPortal\Areas\CategoryArea;
use Bugo\LightPortal\Areas\Traits\HasArea;

arch()
    ->expect(CategoryArea::class)
    ->toUseTrait(HasArea::class);
