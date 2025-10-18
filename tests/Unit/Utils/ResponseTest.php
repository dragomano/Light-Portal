<?php

declare(strict_types=1);

use LightPortal\Utils\Response;

arch()
    ->expect(Response::class)
    ->toHaveMethods(['json', 'exit', 'redirect']);
