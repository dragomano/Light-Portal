<?php

declare(strict_types=1);

use LightPortal\Models\CategoryFactory;
use LightPortal\Models\FactoryInterface;

arch()
    ->expect(CategoryFactory::class)
    ->toImplement(FactoryInterface::class);
