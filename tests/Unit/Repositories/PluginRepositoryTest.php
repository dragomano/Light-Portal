<?php declare(strict_types=1);

use Bugo\LightPortal\Repositories\PluginRepository;

arch()
	->expect(PluginRepository::class)
	->toHaveMethods([
		'addSettings', 'getSettings', 'changeSettings', 'removeSettings',
	]);
