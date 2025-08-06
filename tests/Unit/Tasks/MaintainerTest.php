<?php declare(strict_types=1);

use Bugo\Compat\Tasks\BackgroundTask;
use Bugo\LightPortal\Tasks\Maintainer;

arch()
	->expect(Maintainer::class)
	->toImplement(BackgroundTask::class);
