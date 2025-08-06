<?php declare(strict_types=1);

use Bugo\LightPortal\Hooks\DefaultAction;

arch()
	->expect(DefaultAction::class)
	->toBeInvokable();
