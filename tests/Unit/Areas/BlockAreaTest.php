<?php

declare(strict_types=1);

use LightPortal\Areas\BlockArea;
use LightPortal\Areas\Traits\HasArea;
use LightPortal\Events\HasEvents;

arch()
    ->expect(BlockArea::class)
    ->toUseTraits([HasArea::class, HasEvents::class]);
