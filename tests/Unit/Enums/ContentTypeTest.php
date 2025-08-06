<?php declare(strict_types=1);

use Bugo\LightPortal\Enums\ContentType;

arch()
	->expect(ContentType::class)
	->toBeEnum();
