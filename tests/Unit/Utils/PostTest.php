<?php declare(strict_types=1);

use Bugo\LightPortal\Utils\Post;
use Bugo\LightPortal\Utils\GlobalArray;

arch()
	->expect(Post::class)
	->toExtend(GlobalArray::class);
