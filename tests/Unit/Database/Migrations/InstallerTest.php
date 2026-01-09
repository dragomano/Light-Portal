<?php

declare(strict_types=1);

use Bugo\Compat\Cache\CacheApi;
use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use LightPortal\Database\Migrations\Installer;
use LightPortal\Database\Operations\PortalDelete;
use LightPortal\Database\Operations\PortalInsert;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\Operations\PortalUpdate;
use LightPortal\Database\PortalAdapterFactory;
use LightPortal\Database\PortalAdapterInterface;
use LightPortal\Database\PortalResultInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Utils\PostInterface;
use Tests\ReflectionAccessor;

describe('Installer', function () {
    beforeEach(function () {
        $this->adapter = mock(PortalAdapterInterface::class);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');

        $this->platform = mock(PlatformInterface::class);
        $this->platform->shouldReceive('getName')->andReturn('MySQL');
        $this->platform->shouldReceive('quoteIdentifier')->andReturnUsing(fn($identifier) => $identifier);
        $this->platform->shouldReceive('quoteIdentifierChain')->andReturnUsing(fn($identifiers) => $identifiers);

        $this->adapter->shouldReceive('getPlatform')->andReturn($this->platform);
        $this->adapter->shouldReceive('getTitle')->andReturn('MySQL')->byDefault();
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);
        $this->adapter->shouldReceive('query')->andReturnUsing(function ($sql) {});

        $this->adapterFactoryMock = mock('alias:' . PortalAdapterFactory::class);
        $this->adapterFactoryMock->shouldReceive('create')->andReturn($this->adapter);

        $this->portalSqlMock = mock(PortalSqlInterface::class);
        $this->portalSqlMock->shouldReceive('getPrefix')->andReturn('smf_');
        $this->portalSqlMock->shouldReceive('getAdapter')->andReturn($this->adapter);
        $this->portalSqlMock->shouldReceive('buildSqlString')->andReturn('mocked sql');
        $this->portalSqlMock->shouldReceive('tableExists')->andReturn(false);
        $this->portalSqlMock->shouldReceive('columnExists')->andReturn(false);

        $selectMock = mock(PortalSelect::class)->shouldAllowMockingProtectedMethods();
        $selectMock->shouldReceive('columns')->andReturnSelf();
        $selectMock->shouldReceive('where')->andReturnSelf();

        $this->portalSqlMock->shouldReceive('select')->andReturn($selectMock);

        $insertMock = mock(PortalInsert::class)->shouldAllowMockingProtectedMethods();
        $insertMock->shouldReceive('columns')->andReturnSelf();
        $insertMock->shouldReceive('values')->andReturnSelf();

        $this->portalSqlMock->shouldReceive('insert')->andReturn($insertMock);

        $deleteMock = mock(PortalDelete::class)->shouldAllowMockingProtectedMethods();
        $reflection = new ReflectionAccessor($deleteMock);
        $reflection->setProperty('where', mock(['like' => null]));

        $this->portalSqlMock->shouldReceive('delete')->andReturn($deleteMock);

        $this->portalSqlMock
            ->shouldReceive('execute')
            ->andReturn(mock(PortalResultInterface::class, ['current' => ['count' => 0]]));

        Mockery::spy(Config::class)->shouldReceive('updateModSettings')->andReturn(null);
        Mockery::spy(CacheApi::class)->shouldReceive('clean');
        Mockery::spy(Utils::class)->shouldReceive('makeWritable')->andReturn(null);

        Utils::$context = ['right_to_left' => false];

        $this->installer = mock(Installer::class)->makePartial();
        $this->installer->shouldAllowMockingProtectedMethods();

        $reflection = new ReflectionAccessor($this->installer);
        $reflection->setProperty('sql', $this->portalSqlMock);
    });

    dataset('table modes', ['install', 'uninstall']);

    it('installs the portal successfully', function () {
        $this->installer->shouldReceive('processTables')->with('install');
        $this->installer->shouldReceive('cleanBackgroundTasks');
        $this->installer->shouldReceive('setDefaultSettings');
        $this->installer->shouldReceive('setDirectoryPermissions');

        $result = $this->installer->install();

        expect($result)->toBeTrue();
    });

    it('uninstalls the portal successfully', function () {
        $this->installer->shouldReceive('cleanBackgroundTasks');
        $this->installer->shouldReceive('updateSettings');

        $post = mock(PostInterface::class);
        $post->shouldReceive('hasNot')->with('do_db_changes')->andReturn(true);
        $this->installer->shouldReceive('post')->andReturn($post);

        $result = $this->installer->uninstall();

        expect($result)->toBeTrue();
    });

    it('upgrades the portal successfully', function () {
        $this->installer->shouldReceive('processUpgradeTasks');

        $result = $this->installer->upgrade();

        expect($result)->toBeTrue();
    });

    it('returns correct creators list', function () {
        $sql = mock(PortalSqlInterface::class);
        $sql->shouldReceive('getAdapter')->andReturn($this->adapter);

        $reflection = new ReflectionAccessor(new Installer($sql));
        $creators = $reflection->callMethod('getCreators');

        expect($creators)->toBeArray();
    });

    it('returns correct upgraders list', function () {
        $sql = mock(PortalSqlInterface::class);
        $sql->shouldReceive('getAdapter')->andReturn($this->adapter);

        $reflection = new ReflectionAccessor(new Installer($sql));
        $upgraders = $reflection->callMethod('getUpgraders');

        expect($upgraders)->toBeArray();
    });

    it('processes tables for mode', function ($mode) {
        $reflection = new ReflectionAccessor($this->installer);
        $reflection->callMethod('processTables', [$mode]);

        expect(true)->toBeTrue();
    })->with('table modes');

    it('processes upgrade tasks', function () {
        $reflection = new ReflectionAccessor($this->installer);
        $reflection->callMethod('processUpgradeTasks');

        expect(true)->toBeTrue();
    });

    it('cleans background tasks', function () {
        $deleteMock = mock(PortalDelete::class);
        $deleteMock
            ->shouldReceive('where')
            ->with(['task_file LIKE ?' => '%$sourcedir/LightPortal%'])
            ->andReturnSelf();

        $this->portalSqlMock->shouldReceive('delete')->with('background_tasks')->andReturn($deleteMock);
        $this->portalSqlMock->shouldReceive('execute')->with($deleteMock);

        $reflection = new ReflectionAccessor($this->installer);
        $reflection->callMethod('cleanBackgroundTasks');

        expect(true)->toBeTrue();
    });

    it('sets default settings', function () {
        $sql = mock(PortalSqlInterface::class);
        $sql->shouldReceive('getAdapter')->andReturn($this->adapter);

        $reflection = new ReflectionAccessor(new Installer($sql));
        $reflection->callMethod('setDefaultSettings');

        expect(true)->toBeTrue();
    });

    it('sets directory permissions', function () {
        $sql = mock(PortalSqlInterface::class);
        $sql->shouldReceive('getAdapter')->andReturn($this->adapter);

        $reflection = new ReflectionAccessor(new Installer($sql));
        $reflection->callMethod('setDirectoryPermissions');

        expect(true)->toBeTrue();
    });

    it('updates settings', function () {
        $updateMock = mock(PortalUpdate::class);
        $updateMock->shouldReceive('set')->andReturnSelf()->once();
        $updateMock->shouldReceive('where')->andReturnSelf()->once();

        $this->portalSqlMock->shouldReceive('update')->with('settings')->andReturn($updateMock);
        $this->portalSqlMock->shouldReceive('execute')->with($updateMock);

        $reflection = new ReflectionAccessor($this->installer);
        $reflection->callMethod('updateSettings');
    });

    it('uninstalls with database changes', function () {
        $this->installer->shouldReceive('cleanBackgroundTasks');
        $this->installer->shouldReceive('updateSettings');
        $this->installer->shouldReceive('processTables')->with('uninstall');
        $this->installer->shouldReceive('removePortalSettings');
        $this->installer->shouldReceive('removePortalPermissions');
        $this->installer->shouldReceive('updateSettings');

        $post = mock(PostInterface::class);
        $post->shouldReceive('hasNot')->with('do_db_changes')->andReturn(false);
        $this->installer->shouldReceive('post')->andReturn($post);

        $result = $this->installer->uninstall();

        expect($result)->toBeTrue();
    });
});
