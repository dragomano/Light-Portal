<?php declare(strict_types=1);

use Bugo\LightPortal\Compilers\Sass;
use Laminas\ConfigAggregator\ConfigAggregator;

return [
	'debug' => true,
	'compiler' => Sass::class,
	ConfigAggregator::ENABLE_CACHE => false,
];
