<?php

declare(strict_types=1);

use LightPortal\Models\ModelInterface;
use LightPortal\Models\PageModel;

arch()
    ->expect(PageModel::class)
    ->toImplement(ModelInterface::class);
