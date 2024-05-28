<?php

declare(strict_types=1);

use Bugo\LightPortal\Addons;
use Bugo\LightPortal\ConfigProvider;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$aggregator = new ConfigAggregator([
    ConfigProvider::class,
    Addons\ConfigProvider::class,
]);

return $aggregator->getMergedConfig();