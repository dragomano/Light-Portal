<?php declare(strict_types=1);

use Bugo\LightPortal\Compilers\CompilerInterface;
use Bugo\LightPortal\Compilers\Sass;
use Laminas\ConfigAggregator\ConfigAggregator;

return [
	'debug' => true,
	CompilerInterface::class => Sass::class,
	ConfigAggregator::ENABLE_CACHE => false,
];
