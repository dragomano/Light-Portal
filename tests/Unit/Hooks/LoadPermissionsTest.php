<?php declare(strict_types=1);

use Bugo\LightPortal\Hooks\LoadPermissions;

arch()
	->expect(LoadPermissions::class)
	->toBeInvokable();
