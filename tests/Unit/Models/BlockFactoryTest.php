<?php declare(strict_types=1);

use Bugo\LightPortal\Models\BlockFactory;
use Bugo\LightPortal\Models\FactoryInterface;

arch()
	->expect(BlockFactory::class)
	->toImplement(FactoryInterface::class);
