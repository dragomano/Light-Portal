<?php

declare(strict_types=1);

use Bugo\LightPortal\Models\FactoryInterface;
use Bugo\LightPortal\Models\TagFactory;

arch()
    ->expect(TagFactory::class)
    ->toImplement(FactoryInterface::class);
