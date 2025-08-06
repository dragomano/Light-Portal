<?php declare(strict_types=1);

use Bugo\LightPortal\Plugins\AssetHandler;

arch()
	->expect(AssetHandler::class)
	->toHaveMethods(['prepare', 'handle', 'minify']);
