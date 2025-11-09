<?php

declare(strict_types=1);

use LightPortal\Lists\ListInterface;
use LightPortal\Lists\PluginList;

arch()
    ->expect(PluginList::class)
    ->toImplement(ListInterface::class);
