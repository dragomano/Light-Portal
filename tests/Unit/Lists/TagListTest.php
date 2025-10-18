<?php

declare(strict_types=1);

use LightPortal\Lists\ListInterface;
use LightPortal\Lists\TagList;

arch()
    ->expect(TagList::class)
    ->toImplement(ListInterface::class);
