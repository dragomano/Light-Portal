<?php declare(strict_types=1);

use Bugo\LightPortal\Enums\Action;

arch()
	->expect(Action::class)
	->toBeStringBackedEnum();
