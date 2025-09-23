<?php

declare(strict_types=1);

use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\Lists\ListInterface;

arch()
    ->expect(CategoryList::class)
    ->toImplement(ListInterface::class);
