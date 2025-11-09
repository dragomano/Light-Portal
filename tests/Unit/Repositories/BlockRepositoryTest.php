<?php

declare(strict_types=1);

use LightPortal\Repositories\AbstractRepository;
use LightPortal\Repositories\BlockRepository;

arch()
    ->expect(BlockRepository::class)
    ->toExtend(AbstractRepository::class);
