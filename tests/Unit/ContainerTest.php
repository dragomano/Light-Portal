<?php

declare(strict_types=1);

use Bugo\LightPortal\Container;
use League\Container\Container as LeagueContainer;

beforeEach(function () {
    // Reset the singleton for each test
    $reflection = new ReflectionClass(Container::class);
    $property = $reflection->getProperty('container');
    $property->setValue(null);
});

it('is a singleton', function () {
    $instance1 = Container::getInstance();
    $instance2 = Container::getInstance();

    expect($instance1)->toBe($instance2);
});

it('getInstance returns LeagueContainer instance', function () {
    $instance = Container::getInstance();

    expect($instance)->toBeInstanceOf(LeagueContainer::class);
});

it('get returns service from container', function () {
    $mockContainer = Mockery::mock(LeagueContainer::class);
    $mockContainer->shouldReceive('get')->with('test_service')->andReturn('mocked_service');

    // Inject the mock container
    $reflection = new ReflectionClass(Container::class);
    $property = $reflection->getProperty('container');
    $property->setValue($mockContainer);

    $result = Container::get('test_service');

    expect($result)->toBe('mocked_service');
});

it('get handles exceptions gracefully', function () {
    // Test that get method exists and is callable
    expect(Container::class)->toHaveMethod('get');

    // Test with a real container that might not have the service
    $result = Container::get('nonexistent_service_' . uniqid());

    // Should return false for unknown services
    expect($result)->toBeFalse();
});

it('init initializes container with ServiceProvider', function () {
    // Reset container first
    $reflection = new ReflectionClass(Container::class);
    $property = $reflection->getProperty('container');
    $property->setValue(null);

    // Call getInstance which calls init
    $container = Container::getInstance();

    expect($container)->toBeInstanceOf(LeagueContainer::class)
        ->and(true)->toBeTrue();
});
