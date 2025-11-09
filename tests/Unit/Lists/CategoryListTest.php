<?php

declare(strict_types=1);

use LightPortal\Lists\CategoryList;
use LightPortal\Lists\ListInterface;

arch()
    ->expect(CategoryList::class)
    ->toImplement(ListInterface::class);
