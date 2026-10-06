<?php

declare(strict_types=1);

use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventManager;
use LightPortal\Events\EventManagerFactory;
use LightPortal\Events\EventManagerProxy;

beforeEach(function () {
    $this->manager = mock(EventManager::class);
    $this->factory = mock(EventManagerFactory::class);
});

it('dispatches hooks through the factory manager', function () {
    $this->factory->shouldReceive('__invoke')->once()->with([])->andReturn($this->manager);
    $this->manager->shouldReceive('dispatch')->once()->with(PortalHook::init, ['param' => 1]);

    (new EventManagerProxy($this->factory))->dispatch(PortalHook::init, ['param' => 1]);
});

it('returns all registered listeners', function () {
    $listeners = ['listener1', 'listener2'];

    $this->factory->shouldReceive('__invoke')->once()->with([])->andReturn($this->manager);
    $this->manager->shouldReceive('getAll')->once()->with('')->andReturn($listeners);

    expect((new EventManagerProxy($this->factory))->getAll())->toBe($listeners);
});

it('creates a new proxy with the given plugins', function () {
    $plugins = ['HelloPortal'];

    $this->factory->shouldReceive('__invoke')->once()->with($plugins)->andReturn($this->manager);
    $this->manager->shouldReceive('getAll')->once()->with('')->andReturn([]);

    $proxy = new EventManagerProxy($this->factory);

    expect($proxy->withPlugins($plugins))->not->toBe($proxy)
        ->and($proxy->withPlugins($plugins)->getAll())->toBe([]);
});
