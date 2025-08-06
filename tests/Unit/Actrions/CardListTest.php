<?php declare(strict_types=1);

use Bugo\LightPortal\Actions\CardList;
use Bugo\LightPortal\Actions\CardListInterface;

arch()
	->expect(CardList::class)
	->toImplement(CardListInterface::class);
