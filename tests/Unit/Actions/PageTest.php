<?php

declare(strict_types=1);

use Bugo\LightPortal\Actions\Page;
use Bugo\LightPortal\Actions\ActionInterface;

arch()
    ->expect(Page::class)
    ->toImplement(ActionInterface::class);
