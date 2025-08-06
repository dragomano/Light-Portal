<?php declare(strict_types=1);

use Bugo\LightPortal\Utils\File;
use Bugo\LightPortal\Utils\GlobalArray;

arch()
	->expect(File::class)
	->toExtend(GlobalArray::class)
	->toHaveMethod('free');
