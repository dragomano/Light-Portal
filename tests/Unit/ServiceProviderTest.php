<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;
use LightPortal\ServiceProvider;

it('getConfig returns valid service manager configuration', function () {
    $config = ServiceProvider::getConfig();

    expect($config)->toBeArray()
        ->toHaveKeys(['invokables', 'factories', 'shared', 'tags']);
});

it('getConfig contains invokables', function () {
    $config = ServiceProvider::getConfig();

    expect($config['invokables'])->toBeArray()
        ->not->toBeEmpty();
});

it('getConfig contains factories', function () {
    $config = ServiceProvider::getConfig();

    expect($config['factories'])->toBeArray()
        ->not->toBeEmpty();
});

it('getConfig contains shared settings', function () {
    $config = ServiceProvider::getConfig();

    expect($config['shared'])->toBeArray();
});

it('getConfig contains tags', function () {
    $config = ServiceProvider::getConfig();

    expect($config['tags'])->toBeArray();
});

it('service manager can be created with config', function () {
    $config = ServiceProvider::getConfig();

    $serviceManager = new ServiceManager($config);

    expect($serviceManager)->toBeInstanceOf(ServiceManager::class);
});
