<?php

declare(strict_types=1);

use LightPortal\Models\FactoryInterface;
use LightPortal\Models\PageFactory;

arch()
    ->expect(PageFactory::class)
    ->toImplement(FactoryInterface::class);
