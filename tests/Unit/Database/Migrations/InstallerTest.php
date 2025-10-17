<?php

declare(strict_types=1);

use Bugo\Compat\Cache\CacheApi;
use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Database\Migrations\Installer;
use Bugo\LightPortal\Database\Operations\PortalDelete;
use Bugo\LightPortal\Database\Operations\PortalInsert;
use Bugo\LightPortal\Database\Operations\PortalSelect;
use Bugo\LightPortal\Database\Operations\PortalUpdate;
use Bugo\LightPortal\Database\PortalAdapterFactory;
use Bugo\LightPortal\Database\PortalAdapterInterface;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\Utils\PostInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Tests\ReflectionAccessor;

describe('Installer', function () {
    beforeEach(function () {
        $this->adapter = Mockery::mock(PortalAdapterInterface::class);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter
            ->shouldReceive('getPlatform')
            ->andReturn(Mockery::mock(['getName' => 'MySQL', 'quoteIdentifierChain' => fn($x) => $x]));
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);
        $this->adapter->shouldReceive('query')->andReturnUsing(function ($sql) {});

        $this->adapterFactoryMock = Mockery::mock('alias:' . PortalAdapterFactory::class);
        $this->adapterFactoryMock->shouldReceive('create')->andReturn($this->adapter);

        $this->portalSqlMock = Mockery::mock(PortalSqlInterface::class);
        $this->portalSqlMock->shouldReceive('getPrefix')->andReturn('smf_');
        $this->portalSqlMock->shouldReceive('getAdapter')->andReturn($this->adapter);
        $this->portalSqlMock->shouldReceive('buildSqlString')->andReturn('mocked sql');
        $this->portalSqlMock->shouldReceive('tableExists')->andReturn(false);
        $this->portalSqlMock->shouldReceive('columnExists')->andReturn(false);
        $selectMock = Mockery::mock(PortalSelect::class)->shouldAllowMockingProtectedMethods();
        $selectMock->shouldReceive('columns')->andReturnSelf();
        $selectMock->shouldReceive('where')->andReturnSelf();
        $this->portalSqlMock->shouldReceive('select')->andReturn($selectMock);

        $insertMock = Mockery::mock(PortalInsert::class)->shouldAllowMockingProtectedMethods();
        $insertMock->shouldReceive('columns')->andReturnSelf();
        $insertMock->shouldReceive('values')->andReturnSelf();
        $this->portalSqlMock->shouldReceive('insert')->andReturn($insertMock);

        $deleteMock = Mockery::mock(PortalDelete::class)->shouldAllowMockingProtectedMethods();
        $reflection = new ReflectionAccessor($deleteMock);
        $reflection->setProtectedProperty('where', Mockery::mock(['like' => null]));
        $this->portalSqlMock->shouldReceive('delete')->andReturn($deleteMock);

        $this->portalSqlMock
            ->shouldReceive('execute')
            ->andReturn(Mockery::mock(ResultInterface::class, ['current' => ['count' => 0]]));

        Mockery::spy(Config::class)->shouldReceive('updateModSettings')->andReturn(null);
        Mockery::spy(CacheApi::class)->shouldReceive('clean');
        Mockery::spy(Utils::class)->shouldReceive('makeWritable')->andReturn(null);

        Utils::$context = ['right_to_left' => false];

        $this->installer = Mockery::mock(Installer::class)->makePartial();
        $this->installer->shouldAllowMockingProtectedMethods();

        $reflection = new ReflectionAccessor($this->installer);
        $reflection->setProtectedProperty('sql', $this->portalSqlMock);
    });

    afterEach(function () {
        Mockery::close();
    });

    dataset('table modes', ['install', 'uninstall']);

    it('constructs with null parameters', function () {
        $installer = new Installer();

        expect($installer)->toBeInstanceOf(Installer::class);
    });

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

        $post = Mockery::mock(PostInterface::class);
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
        $reflection = new ReflectionAccessor(new Installer());
        $creators = $reflection->callProtectedMethod('getCreators');

        expect($creators)->toBeArray();
    });

    it('returns correct upgraders list', function () {
        $reflection = new ReflectionAccessor(new Installer());
        $upgraders = $reflection->callProtectedMethod('getUpgraders');

        expect($upgraders)->toBeArray();
    });

    it('processes tables for mode', function ($mode) {
        $reflection = new ReflectionAccessor($this->installer);
        $reflection->callProtectedMethod('processTables', [$mode]);

        expect(true)->toBeTrue();
    })->with('table modes');

    it('processes upgrade tasks', function () {
        $reflection = new ReflectionAccessor($this->installer);
        $reflection->callProtectedMethod('processUpgradeTasks');

        expect(true)->toBeTrue();
    });

    it('cleans background tasks', function () {
        $deleteMock = Mockery::mock(PortalDelete::class);
        $deleteMock
            ->shouldReceive('where')
            ->with(['task_file LIKE ?' => '%$sourcedir/LightPortal%'])
            ->andReturnSelf();

        $this->portalSqlMock->shouldReceive('delete')->with('background_tasks')->andReturn($deleteMock);
        $this->portalSqlMock->shouldReceive('execute')->with($deleteMock);

        $reflection = new ReflectionAccessor($this->installer);
        $reflection->callProtectedMethod('cleanBackgroundTasks');

        expect(true)->toBeTrue();
    });

    it('sets default settings', function () {
        $reflection = new ReflectionAccessor(new Installer());
        $reflection->callProtectedMethod('setDefaultSettings');

        expect(true)->toBeTrue();
    });

    it('sets directory permissions', function () {
        $reflection = new ReflectionAccessor(new Installer());
        $reflection->callProtectedMethod('setDirectoryPermissions');

        expect(true)->toBeTrue();
    });

    it('updates settings', function () {
        $updateMock = Mockery::mock(PortalUpdate::class);
        $updateMock->shouldReceive('set')->andReturnSelf()->once();
        $updateMock->shouldReceive('where')->andReturnSelf()->once();

        $this->portalSqlMock->shouldReceive('update')->with('settings')->andReturn($updateMock);
        $this->portalSqlMock->shouldReceive('execute')->with($updateMock);

        $reflection = new ReflectionAccessor($this->installer);
        $reflection->callProtectedMethod('updateSettings');
    });

    it('uninstalls with database changes', function () {
        $this->installer->shouldReceive('cleanBackgroundTasks');
        $this->installer->shouldReceive('updateSettings');
        $this->installer->shouldReceive('processTables')->with('uninstall');
        $this->installer->shouldReceive('removePortalSettings');
        $this->installer->shouldReceive('removePortalPermissions');
        $this->installer->shouldReceive('updateSettings');

        $post = Mockery::mock(PostInterface::class);
        $post->shouldReceive('hasNot')->with('do_db_changes')->andReturn(false);
        $this->installer->shouldReceive('post')->andReturn($post);

        $result = $this->installer->uninstall();

        expect($result)->toBeTrue();
    });
});
