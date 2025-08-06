<?php declare(strict_types=1);

use Bugo\LightPortal\Actions\Category;
use Bugo\LightPortal\Actions\PageListInterface;

arch()
	->expect(Category::class)
	->toImplement(PageListInterface::class);
