<?php

declare(strict_types=1);

use Bugo\LightPortal\Models\CategoryModel;
use Bugo\LightPortal\Models\ModelInterface;

arch()
    ->expect(CategoryModel::class)
    ->toImplement(ModelInterface::class);
