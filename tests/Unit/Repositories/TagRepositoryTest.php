<?php declare(strict_types=1);

use Bugo\LightPortal\Repositories\AbstractRepository;
use Bugo\LightPortal\Repositories\TagRepository;

arch()
	->expect(TagRepository::class)
	->toExtend(AbstractRepository::class);
