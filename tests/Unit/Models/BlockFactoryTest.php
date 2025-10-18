<?php

declare(strict_types=1);

use LightPortal\Models\BlockFactory;
use LightPortal\Models\FactoryInterface;

arch()
    ->expect(BlockFactory::class)
    ->toImplement(FactoryInterface::class);
