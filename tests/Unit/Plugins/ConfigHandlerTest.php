<?php

declare(strict_types=1);

use Bugo\LightPortal\Plugins\ConfigHandler;

arch()
    ->expect(ConfigHandler::class)
    ->toHaveMethod('handle');
