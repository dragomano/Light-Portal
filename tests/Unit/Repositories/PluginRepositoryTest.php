<?php

declare(strict_types=1);

use LightPortal\Repositories\PluginRepository;
use LightPortal\Repositories\PluginRepositoryInterface;

arch()
    ->expect(PluginRepository::class)
    ->toImplement(PluginRepositoryInterface::class);
