<?php declare(strict_types=1);

use Bugo\LightPortal\Hooks\DeleteMembers;

arch()
	->expect(DeleteMembers::class)
	->toBeInvokable();
