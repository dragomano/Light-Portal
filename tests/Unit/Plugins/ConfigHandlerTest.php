<?php

declare(strict_types=1);

use LightPortal\Plugins\ConfigHandler;

arch()
    ->expect(ConfigHandler::class)
    ->toHaveMethod('handle');
