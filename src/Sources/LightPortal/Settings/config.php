<?php declare(strict_types=1);

use Bugo\LightPortal\ConfigProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$cacheConfig = [
	'config_cache_path' => 'data/cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
	ConfigProvider::class,

	new PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),

	new PhpFileProvider(realpath(__DIR__) . '/development.config.php'),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
