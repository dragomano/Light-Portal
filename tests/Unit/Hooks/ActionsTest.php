<?php declare(strict_types=1);

use Bugo\LightPortal\Hooks\Actions;

arch()
	->expect(Actions::class)
	->toBeInvokable();
