<?php declare(strict_types=1);

use Bugo\LightPortal\Actions\BoardIndex;
use Bugo\LightPortal\Actions\ActionInterface;

arch()
	->expect(BoardIndex::class)
	->toImplement(ActionInterface::class);
