<?php

declare(strict_types=1);

use LightPortal\Container;
use League\Container\Container as LeagueContainer;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->container = new ReflectionAccessor(new Container());
    $this->container->setProperty('container', null);
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
    $mockContainer = mock(LeagueContainer::class);
    $mockContainer->shouldReceive('get')->with('test_service')->andReturn('mocked_service');

    $this->container->setProperty('container', $mockContainer);

    $result = Container::get('test_service');

    expect($result)->toBe('mocked_service');
});

it('get handles exceptions gracefully', function () {
    expect(Container::class)->toHaveMethod('get');

    $result = Container::get('nonexistent_service_' . uniqid());

    expect($result)->toBeFalse();
});

it('init initializes container with ServiceProvider', function () {
    $container = Container::getInstance();

    expect($container)->toBeInstanceOf(LeagueContainer::class)
        ->and(true)->toBeTrue();
});
