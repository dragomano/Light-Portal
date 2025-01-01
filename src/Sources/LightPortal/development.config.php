<?php declare(strict_types=1);

use Bugo\LightPortal\Compilers\CompilerInterface;
use Bugo\LightPortal\Compilers\Zero;
use Bugo\LightPortal\Renderers\Blade;
use Bugo\LightPortal\Renderers\RendererInterface;

return [
	'debug' => true,
	CompilerInterface::class => Zero::class,
	RendererInterface::class => Blade::class,
];
