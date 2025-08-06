<?php declare(strict_types=1);

use Bugo\LightPortal\Hooks\BuildRoute;

arch()
	->expect(BuildRoute::class)
	->toBeInvokable();
