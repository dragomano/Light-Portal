<?php declare(strict_types=1);

use Bugo\LightPortal\Enums\Placement;

arch()
	->expect(Placement::class)
	->toBeEnum();
