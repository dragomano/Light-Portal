<?php

declare(strict_types=1);

use LightPortal\Database\PortalSql;
use LightPortal\Repositories\PluginRepository;
use LightPortal\Repositories\PluginRepositoryInterface;
use LightPortal\Utils\CacheInterface;
use Tests\AppMockRegistry;
use Tests\PortalTable;
use Tests\TestAdapterFactory;

arch()
    ->expect(PluginRepository::class)
    ->toImplement(PluginRepositoryInterface::class);

beforeEach(function () {
    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::PLUGINS->value)->execute();

    $this->sql = new PortalSql($adapter);

    $this->repository = new PluginRepository($this->sql);
});

it('adds plugin settings and clears cache', function () {
    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('forget')->with('plugin_settings')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $this->repository->addSettings([
        ['name' => 'PluginA', 'config' => 'enabled', 'value' => '1'],
        ['name' => 'PluginB', 'config' => 'enabled', 'value' => '0'],
    ]);

    $count = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_plugins')
        ->execute()
        ->current()['count'];

    expect($count)->toBe(2);
});

it('returns early when adding empty settings', function () {
    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('forget')->never();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $this->repository->addSettings();

    $count = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_plugins')
        ->execute()
        ->current()['count'];

    expect($count)->toBe(0);
});

it('returns settings from cache callback', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_plugins (name, config, value)
        VALUES
            ('PluginA', 'enabled', '1'),
            ('PluginA', 'color', 'blue'),
            ('PluginB', 'enabled', '0')
    ")->execute();

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('remember')
        ->once()
        ->with('plugin_settings', Mockery::type('callable'), 3 * 24 * 60 * 60)
        ->andReturnUsing(fn($key, $callback) => $callback());
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $settings = $this->repository->getSettings();

    expect($settings)->toBeArray()
        ->and($settings['PluginA']['enabled'])->toBe('1')
        ->and($settings['PluginA']['color'])->toBe('blue')
        ->and($settings['PluginB']['enabled'])->toBe('0');
});

it('changes plugin settings and clears cache', function () {
    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('forget')->with('plugin_settings')->twice();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $this->repository->changeSettings('PluginC', [
        'enabled' => '1',
        'mode' => 'auto',
    ]);

    $rows = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT name, config, value FROM lp_plugins WHERE name = ?', ['PluginC'])
        ->toArray();

    expect($rows)->toHaveCount(2)
        ->and($rows[0]['name'])->toBe('PluginC');
});

it('returns early when changing settings with empty data', function () {
    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('forget')->never();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $this->repository->changeSettings('PluginZ');

    $count = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_plugins')
        ->execute()
        ->current()['count'];

    expect($count)->toBe(0);
});
