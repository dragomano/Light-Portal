<?php

declare(strict_types=1);

use Bugo\LightPortal\Lists\ListInterface;
use Bugo\LightPortal\Lists\PluginList;

arch()
    ->expect(PluginList::class)
    ->toImplement(ListInterface::class);
