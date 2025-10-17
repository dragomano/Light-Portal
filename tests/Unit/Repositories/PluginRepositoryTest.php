<?php

declare(strict_types=1);

use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Repositories\PluginRepositoryInterface;

arch()
    ->expect(PluginRepository::class)
    ->toImplement(PluginRepositoryInterface::class);
