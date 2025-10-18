<?php

declare(strict_types=1);

use LightPortal\Actions\Page;
use LightPortal\Actions\ActionInterface;

arch()
    ->expect(Page::class)
    ->toImplement(ActionInterface::class);
