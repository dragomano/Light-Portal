<?php declare(strict_types=1);

use Bugo\LightPortal\Renderers\PurePHP;
use Bugo\LightPortal\Renderers\RendererInterface;

arch()
	->expect(PurePHP::class)
	->toImplement(RendererInterface::class);
