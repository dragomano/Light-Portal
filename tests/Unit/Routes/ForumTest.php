<?php declare(strict_types=1);

use Bugo\Compat\Routable;
use Bugo\LightPortal\Routes\Forum;

arch()
	->expect(Forum::class)
	->toImplement(Routable::class);
