<?php

declare(strict_types=1);

use LightPortal\Models\ModelInterface;
use LightPortal\Models\TagModel;

arch()
    ->expect(TagModel::class)
    ->toImplement(ModelInterface::class);
