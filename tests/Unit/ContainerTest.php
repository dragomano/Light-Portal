<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;
use LightPortal\Container;
use Tests\ReflectionAccessor;

it('is a singleton', function () {
    $instance1 = Container::getInstance();
    $instance2 = Container::getInstance();

    expect($instance1)->toBe($instance2);
});

it('getInstance returns Container instance', function () {
    $instance = Container::getInstance();

    expect($instance)->toBeInstanceOf(Container::class);
});

it('getInstance returns wrapper over ServiceManager', function () {
    $instance = Container::getInstance();

    $instance->get('LightPortal\Utils\Cache');

    $accessor = new ReflectionAccessor($instance);
    $serviceManager = $accessor->getProperty('container');

    expect($serviceManager)->toBeInstanceOf(ServiceManager::class);
});

it('get returns service from container', function () {
    $instance = Container::getInstance();
    $mockServiceManager = mock(ServiceManager::class);
    $mockServiceManager->shouldReceive('get')->with('test_service')->andReturn('mocked_service');

    $accessor = new ReflectionAccessor($instance);
    $accessor->setProperty('container', $mockServiceManager);

    $result = $instance->get('test_service');

    expect($result)->toBe('mocked_service');
});

it('get handles exceptions gracefully', function () {
    $instance = Container::getInstance();

    $result = $instance->get('nonexistent_service_' . uniqid());

    expect($result)->toBeFalse();
});

it('init initializes container with ServiceProvider', function () {
    $instance = Container::getInstance();

    $instance->get('LightPortal\Utils\Cache');

    $accessor = new ReflectionAccessor($instance);
    $serviceManager = $accessor->getProperty('container');

    expect($serviceManager)->toBeInstanceOf(ServiceManager::class);
});
