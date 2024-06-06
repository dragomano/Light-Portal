<?php

use Bugo\LightPortal\Compilers\CompilerInterface;
use Bugo\LightPortal\Compilers\Zero;
use Laminas\ConfigAggregator\ConfigAggregator;

return [
	'debug' => false,
	CompilerInterface::class => Zero::class,
	ConfigAggregator::ENABLE_CACHE => true,
];
