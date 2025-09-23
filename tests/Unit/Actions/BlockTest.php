<?php

declare(strict_types=1);

use Bugo\LightPortal\Actions\Block;
use Bugo\LightPortal\Actions\ActionInterface;

arch()
    ->expect(Block::class)
    ->toImplement(ActionInterface::class);
