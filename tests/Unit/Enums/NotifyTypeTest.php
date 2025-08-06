<?php declare(strict_types=1);

use Bugo\LightPortal\Enums\NotifyType;

arch()
	->expect(NotifyType::class)
	->toBeEnum();
