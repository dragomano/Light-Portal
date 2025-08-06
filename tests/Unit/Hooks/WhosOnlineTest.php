<?php declare(strict_types=1);

use Bugo\LightPortal\Hooks\WhosOnline;

arch()
	->expect(WhosOnline::class)
	->toBeInvokable();
