<?php declare(strict_types=1);

use Bugo\LightPortal\Hooks\ProfilePopup;

arch()
	->expect(ProfilePopup::class)
	->toBeInvokable();
