<?php declare(strict_types=1);

use Bugo\LightPortal\Utils\Notify;

arch()
	->expect(Notify::class)
	->toHaveMethod('send');
