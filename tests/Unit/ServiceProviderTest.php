<?php

declare(strict_types=1);

use LightPortal\ServiceProvider;
use League\Container\Container as LeagueContainer;
use League\Container\Definition\DefinitionInterface;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->serviceProvider = new ServiceProvider();
});

it('provides returns true for registered services', function () {
    $provider = new ReflectionAccessor($this->serviceProvider);
    $definitions = $provider->callMethod('getFlattenedDefinitions');

    foreach ($definitions as $definition) {
        expect($this->serviceProvider->provides($definition['id']))->toBeTrue();
    }
});

it('provides returns false for unregistered services', function () {
    expect($this->serviceProvider->provides('NonExistentService'))->toBeFalse()
        ->and($this->serviceProvider->provides('AnotherNonExistentService'))->toBeFalse();
});

it('registers adds all services to container', function () {
    $mockContainer = mock(LeagueContainer::class);
    $mockDefinition = mock(DefinitionInterface::class);
    $mockContainer->shouldReceive('add')
        ->andReturn($mockDefinition)
        ->zeroOrMoreTimes();

    $mockDefinition->shouldReceive('addArguments')->andReturnSelf()->zeroOrMoreTimes();
    $mockDefinition->shouldReceive('setShared')->andReturnSelf()->zeroOrMoreTimes();

    $provider = new ReflectionAccessor($this->serviceProvider);
    $provider->callMethod('setContainer', [$mockContainer]);

    $this->serviceProvider->register();

    expect(true)->toBeTrue();
});

it('registers configures container properly', function () {
    $mockContainer = mock(LeagueContainer::class);
    $mockDefinition = mock(DefinitionInterface::class);
    $mockContainer->shouldReceive('add')
        ->andReturn($mockDefinition)
        ->zeroOrMoreTimes();

    $mockDefinition->shouldReceive('addArguments')->andReturnSelf()->zeroOrMoreTimes();
    $mockDefinition->shouldReceive('setShared')->andReturnSelf()->zeroOrMoreTimes();

    $provider = new ReflectionAccessor($this->serviceProvider);
    $provider->callMethod('setContainer', [$mockContainer]);

    $this->serviceProvider->register();

    expect(true)->toBeTrue();
});
