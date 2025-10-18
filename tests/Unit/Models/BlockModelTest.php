<?php

declare(strict_types=1);

use LightPortal\Models\BlockModel;
use LightPortal\Models\ModelInterface;

arch()
    ->expect(BlockModel::class)
    ->toImplement(ModelInterface::class);
