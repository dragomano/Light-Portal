<?php

declare(strict_types=1);

use Bugo\LightPortal\Enums\PluginType;

arch()
    ->expect(PluginType::class)
    ->toBeEnum();
