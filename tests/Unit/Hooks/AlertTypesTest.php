<?php declare(strict_types=1);

use Bugo\LightPortal\Hooks\AlertTypes;

arch()
	->expect(AlertTypes::class)
	->toBeInvokable();
