<?php declare(strict_types=1);

use Bugo\LightPortal\Compilers\CompilerInterface;
use Bugo\LightPortal\Compilers\Sass;
use Bugo\LightPortal\Renderers\Blade;
use Bugo\LightPortal\Renderers\RendererInterface;

return [
	CompilerInterface::class => Sass::class,
	RendererInterface::class => Blade::class,
];
