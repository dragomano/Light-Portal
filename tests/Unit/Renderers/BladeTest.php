<?php declare(strict_types=1);

use Bugo\LightPortal\Renderers\Blade;
use Bugo\LightPortal\Renderers\RendererInterface;

arch()
	->expect(Blade::class)
	->toImplement(RendererInterface::class);
