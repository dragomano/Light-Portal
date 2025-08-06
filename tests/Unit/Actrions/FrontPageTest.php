<?php declare(strict_types=1);

use Bugo\LightPortal\Actions\FrontPage;
use Bugo\LightPortal\Actions\ActionInterface;

arch()
	->expect(FrontPage::class)
	->toImplement(ActionInterface::class);
