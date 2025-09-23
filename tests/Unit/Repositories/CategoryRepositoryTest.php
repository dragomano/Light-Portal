<?php

declare(strict_types=1);

use Bugo\LightPortal\Repositories\AbstractRepository;
use Bugo\LightPortal\Repositories\CategoryRepository;

arch()
    ->expect(CategoryRepository::class)
    ->toExtend(AbstractRepository::class);
