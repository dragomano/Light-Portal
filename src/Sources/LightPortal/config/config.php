<?php

declare(strict_types=1);

use Bugo\LightPortal\Addons;
use Bugo\LightPortal\ConfigProvider;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$cacheConfig = [
	'config_cache_path' => 'data/cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
	// Laminas component providers

	// Application Level providers
	ConfigProvider::class,
	Addons\ConfigProvider::class,

	// Load application config in a pre-defined order in such a way that local settings
	// overwrite global settings. (Loaded as first to last):
	//   - `global.php`
	//   - `*.global.php`
	//   - `local.php`
	//   - `*.local.php`
	new PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),

	new PhpFileProvider(realpath(__DIR__ . '/../') . '/addons/*/{{,*.}config,{,*.}dev}.php'),

	// Load development config if it exists
	new PhpFileProvider(realpath(__DIR__) . '/development.config.php'),
]);

return $aggregator->getMergedConfig();
