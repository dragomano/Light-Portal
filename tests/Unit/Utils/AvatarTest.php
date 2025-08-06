<?php declare(strict_types=1);

use Bugo\LightPortal\Utils\Avatar;

arch()
	->expect(Avatar::class)
	->toHaveMethods(['get', 'getWithItems']);
