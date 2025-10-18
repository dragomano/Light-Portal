<?php

declare(strict_types=1);

use LightPortal\Actions\BoardIndex;
use LightPortal\Actions\ActionInterface;

arch()
    ->expect(BoardIndex::class)
    ->toImplement(ActionInterface::class);
