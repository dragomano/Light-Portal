<?php declare(strict_types=1);

use Bugo\LightPortal\Models\ModelInterface;
use Bugo\LightPortal\Models\PageModel;

arch()
	->expect(PageModel::class)
	->toImplement(ModelInterface::class);
