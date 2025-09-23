<?php

declare(strict_types=1);

use Bugo\LightPortal\Models\FactoryInterface;
use Bugo\LightPortal\Models\PageFactory;

arch()
    ->expect(PageFactory::class)
    ->toImplement(FactoryInterface::class);
