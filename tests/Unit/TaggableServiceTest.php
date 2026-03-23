<?php

declare(strict_types=1);

use LightPortal\Container;
use LightPortal\TaggableService;
use Tests\ReflectionAccessor;

it('creates TaggableService with class name and container', function () {
    $container = Container::getInstance();

    $service = new TaggableService('TestService', $container);

    expect($service)->toBeInstanceOf(TaggableService::class);
});

it('adds single argument to service', function () {
    $container = Container::getInstance();
    $mockContainer = mock(Container::class);
    $mockContainer->shouldReceive('registerFactory')->once();
    $mockContainer->shouldReceive('get')->with('DependencyClass')->andReturn(new \stdClass());

    $service = new TaggableService('TestService', $mockContainer);
    $result = $service->addArgument('DependencyClass');

    expect($result)->toBeInstanceOf(TaggableService::class);
});

it('adds multiple arguments to service', function () {
    $mockContainer = mock(Container::class);
    $mockContainer->shouldReceive('registerFactory')->once();

    $service = new TaggableService('TestService', $mockContainer);
    $result = $service->addArguments(['Dep1', 'Dep2']);

    expect($result)->toBeInstanceOf(TaggableService::class);
});

it('adds tag to service', function () {
    $container = Container::getInstance();
    $mockContainer = mock(Container::class);
    $mockContainer->shouldReceive('registerFactory')->zeroOrMoreTimes();

    $service = new TaggableService('TestService', $mockContainer);
    $result = $service->addTag('test_tag');

    expect($result)->toBeInstanceOf(TaggableService::class);
});

it('returns self for method chaining', function () {
    $mockContainer = mock(Container::class);
    $mockContainer->shouldReceive('registerFactory')->twice();

    $service = new TaggableService('TestService', $mockContainer);

    $result = $service
        ->addArgument('Dep1')
        ->addArgument('Dep2');

    expect($result)->toBeInstanceOf(TaggableService::class);
});
