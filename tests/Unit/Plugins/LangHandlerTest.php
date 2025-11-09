<?php

declare(strict_types=1);

use LightPortal\Plugins\LangHandler;

arch()
    ->expect(LangHandler::class)
    ->toHaveMethod('handle');
