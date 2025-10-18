<?php

declare(strict_types=1);

use LightPortal\Models\FactoryInterface;
use LightPortal\Models\TagFactory;

arch()
    ->expect(TagFactory::class)
    ->toImplement(FactoryInterface::class);
