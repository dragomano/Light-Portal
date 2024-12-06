<?php declare(strict_types=1);

use Bugo\LightPortal\Compilers\CompilerInterface;
use Bugo\LightPortal\Compilers\Zero;
use Bugo\LightPortal\Renderers\Blade;
use Bugo\LightPortal\Renderers\RendererInterface;
use Laminas\ConfigAggregator\ConfigAggregator;

return [
	'debug' => false,
	CompilerInterface::class => Zero::class,
	RendererInterface::class => Blade::class,
	ConfigAggregator::ENABLE_CACHE => true,
];
