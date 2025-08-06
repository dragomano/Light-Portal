<?php declare(strict_types=1);

use Bugo\LightPortal\Utils\GlobalArray;

arch()
	->expect(GlobalArray::class)
	->toHaveMethods(['get', 'put', 'all', 'only', 'except', 'has', 'hasNot', 'isEmpty', 'isNotEmpty']);
