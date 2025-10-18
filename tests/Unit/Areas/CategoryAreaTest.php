<?php

declare(strict_types=1);

use LightPortal\Areas\CategoryArea;
use LightPortal\Areas\Traits\HasArea;

arch()
    ->expect(CategoryArea::class)
    ->toUseTrait(HasArea::class);
