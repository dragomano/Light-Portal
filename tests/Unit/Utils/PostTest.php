<?php

declare(strict_types=1);

use LightPortal\Utils\Post;
use LightPortal\Utils\GlobalArray;

arch()
    ->expect(Post::class)
    ->toExtend(GlobalArray::class);
