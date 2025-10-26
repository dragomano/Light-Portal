<?php

declare(strict_types=1);

use LightPortal\Areas\BlockArea;
use LightPortal\Areas\Traits\HasArea;

arch()
    ->expect(BlockArea::class)
    ->toUseTrait(HasArea::class);
