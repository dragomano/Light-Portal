<?php

declare(strict_types=1);

use Bugo\LightPortal\Repositories\AbstractRepository;
use Bugo\LightPortal\Repositories\PageRepository;

arch()
    ->expect(PageRepository::class)
    ->toExtend(AbstractRepository::class);
