<?php

use Bugo\LightPortal\Compilers\Zero;
use Laminas\ConfigAggregator\ConfigAggregator;

return [
	'debug' => false,
	'compiler' => Zero::class,
	ConfigAggregator::ENABLE_CACHE => true,
];
