<?php

declare(strict_types=1);

use LightPortal\Models\CategoryModel;
use LightPortal\Models\ModelInterface;

arch()
    ->expect(CategoryModel::class)
    ->toImplement(ModelInterface::class);
