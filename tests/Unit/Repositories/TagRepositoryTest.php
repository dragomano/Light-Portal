<?php

declare(strict_types=1);

use LightPortal\Repositories\AbstractRepository;
use LightPortal\Repositories\TagRepository;

arch()
    ->expect(TagRepository::class)
    ->toExtend(AbstractRepository::class);
