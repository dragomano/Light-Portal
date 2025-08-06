<?php declare(strict_types=1);

use Bugo\LightPortal\Hooks\MenuButtons;

arch()
	->expect(MenuButtons::class)
	->toBeInvokable();
