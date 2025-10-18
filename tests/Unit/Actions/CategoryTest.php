<?php

declare(strict_types=1);

use LightPortal\Actions\Category;
use LightPortal\Actions\PageListInterface;

arch()
    ->expect(Category::class)
    ->toImplement(PageListInterface::class);
