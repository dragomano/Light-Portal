<?php declare(strict_types=1);

use Bugo\LightPortal\Repositories\AbstractRepository;
use Bugo\LightPortal\Repositories\BlockRepository;

arch()
	->expect(BlockRepository::class)
	->toExtend(AbstractRepository::class);
