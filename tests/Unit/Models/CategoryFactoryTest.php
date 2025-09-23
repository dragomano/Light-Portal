<?php

declare(strict_types=1);

use Bugo\LightPortal\Models\CategoryFactory;
use Bugo\LightPortal\Models\FactoryInterface;

arch()
    ->expect(CategoryFactory::class)
    ->toImplement(FactoryInterface::class);
