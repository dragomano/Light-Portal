<?php declare(strict_types=1);

use Bugo\LightPortal\Lists\ListInterface;
use Bugo\LightPortal\Lists\TagList;

arch()
	->expect(TagList::class)
	->toImplement(ListInterface::class);
