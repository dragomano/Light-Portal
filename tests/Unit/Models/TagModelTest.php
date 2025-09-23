<?php

declare(strict_types=1);

use Bugo\LightPortal\Models\ModelInterface;
use Bugo\LightPortal\Models\TagModel;

arch()
    ->expect(TagModel::class)
    ->toImplement(ModelInterface::class);
