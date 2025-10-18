<?php

declare(strict_types=1);

use LightPortal\Repositories\AbstractRepository;
use LightPortal\Repositories\CategoryRepository;

arch()
    ->expect(CategoryRepository::class)
    ->toExtend(AbstractRepository::class);
