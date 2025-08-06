<?php declare(strict_types=1);

use Bugo\LightPortal\Enums\Status;

arch()
	->expect(Status::class)
	->toBeIntBackedEnum();
