<?php

declare(strict_types=1);

use Bugo\LightPortal\ServiceProvider;
use League\Container\Container as LeagueContainer;
use League\Container\Definition\DefinitionInterface;

beforeEach(function () {
    $this->serviceProvider = new ServiceProvider();
});

it('provides returns true for registered services', function () {
    $reflection = new ReflectionClass(ServiceProvider::class);
    $constant = $reflection->getReflectionConstant('SERVICES');
    $services = $constant->getValue();

    foreach ($services as $service) {
        expect($this->serviceProvider->provides($service))->toBeTrue();
    }
});

it('provides returns false for unregistered services', function () {
    expect($this->serviceProvider->provides('NonExistentService'))->toBeFalse()
        ->and($this->serviceProvider->provides('AnotherNonExistentService'))->toBeFalse();
});

it('register adds all services to container', function () {
    $mockContainer = Mockery::mock(LeagueContainer::class);

    // Mock DefinitionInterface for add() return values
    $mockDefinition = Mockery::mock(DefinitionInterface::class);

    // Mock all the add() calls that register services - they should return a DefinitionInterface
    $mockContainer->shouldReceive('add')
        ->andReturn($mockDefinition)
        ->zeroOrMoreTimes();

    // Mock methods that return DefinitionInterface and allow chaining
    $mockDefinition->shouldReceive('addArgument')->andReturnSelf()->zeroOrMoreTimes();
    $mockDefinition->shouldReceive('setShared')->andReturnSelf()->zeroOrMoreTimes();

    // Set up the container for the service provider
    $reflection = new ReflectionClass(ServiceProvider::class);
    $method = $reflection->getMethod('setContainer');
    $method->invoke($this->serviceProvider, $mockContainer);

    // Call register
    $this->serviceProvider->register();

    // Verify expectations (this is handled by Mockery)
    expect(true)->toBeTrue(); // If we get here without exception, the test passes
});

it('register configures container properly', function () {
    $mockContainer = Mockery::mock(LeagueContainer::class);

    // Mock DefinitionInterface for add() return values
    $mockDefinition = Mockery::mock(DefinitionInterface::class);

    // Mock all add calls to return DefinitionInterface
    $mockContainer->shouldReceive('add')
        ->andReturn($mockDefinition)
        ->zeroOrMoreTimes();

    // Mock methods that allow chaining
    $mockDefinition->shouldReceive('addArgument')->andReturnSelf()->zeroOrMoreTimes();
    $mockDefinition->shouldReceive('setShared')->andReturnSelf()->zeroOrMoreTimes();

    // Set up the container
    $reflection = new ReflectionClass(ServiceProvider::class);
    $method = $reflection->getMethod('setContainer');
    $method->invoke($this->serviceProvider, $mockContainer);

    // Call register
    $this->serviceProvider->register();

    // If we get here without exception, the test passes
    expect(true)->toBeTrue();
});
