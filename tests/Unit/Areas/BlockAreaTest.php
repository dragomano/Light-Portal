<?php declare(strict_types=1);

use Bugo\LightPortal\Areas\BlockArea;
use Bugo\LightPortal\Areas\Traits\HasArea;
use Bugo\LightPortal\Events\HasEvents;

arch()
	->expect(BlockArea::class)
	->toUseTraits([HasArea::class, HasEvents::class]);
