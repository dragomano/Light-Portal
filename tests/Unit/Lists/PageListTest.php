<?php

declare(strict_types=1);

use LightPortal\Lists\ListInterface;
use LightPortal\Lists\PageList;

arch()
    ->expect(PageList::class)
    ->toImplement(ListInterface::class);
