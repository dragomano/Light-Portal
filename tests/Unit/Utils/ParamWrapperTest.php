<?php declare(strict_types=1);

use Bugo\LightPortal\Utils\ParamWrapper;

arch()
	->expect(ParamWrapper::class)
	->toImplement(ArrayAccess::class);
