<?php declare(strict_types=1);

use Bugo\LightPortal\Utils\DateTime;

arch()
	->expect(DateTime::class)
	->toHaveMethods(['relative', 'get', 'getValueForDate', 'dateCompare', 'getLocalDate']);
