<?php

declare(strict_types=1);

use LightPortal\Areas\TagArea;
use LightPortal\Areas\Traits\HasArea;

arch()
    ->expect(TagArea::class)
    ->toUseTrait(HasArea::class);
