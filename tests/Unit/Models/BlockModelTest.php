<?php declare(strict_types=1);

use Bugo\LightPortal\Models\BlockModel;
use Bugo\LightPortal\Models\ModelInterface;

arch()
	->expect(BlockModel::class)
	->toImplement(ModelInterface::class);
