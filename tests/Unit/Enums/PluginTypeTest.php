<?php

declare(strict_types=1);

use LightPortal\Enums\PluginType;

arch()
    ->expect(PluginType::class)
    ->toBeEnum();
