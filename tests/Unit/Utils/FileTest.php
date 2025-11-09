<?php

declare(strict_types=1);

use LightPortal\Utils\File;
use LightPortal\Utils\GlobalArray;

arch()
    ->expect(File::class)
    ->toExtend(GlobalArray::class)
    ->toHaveMethod('free');
