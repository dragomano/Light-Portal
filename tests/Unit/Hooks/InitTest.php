<?php declare(strict_types=1);

use Bugo\LightPortal\Hooks\Init;

arch()
	->expect(Init::class)
	->toBeInvokable();
