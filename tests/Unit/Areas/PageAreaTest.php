<?php

declare(strict_types=1);

use LightPortal\Areas\PageArea;
use LightPortal\Areas\Traits\HasArea;

arch()
    ->expect(PageArea::class)
    ->toUseTrait(HasArea::class);
