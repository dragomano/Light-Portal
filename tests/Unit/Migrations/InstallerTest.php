<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\Installer;
use Bugo\LightPortal\Migrations\Operations\PortalDelete;
use Bugo\LightPortal\Migrations\Operations\PortalUpdate;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Bugo\LightPortal\Utils\PostInterface;

describe('Installer', function () {
    beforeEach(function () {
        $this->adapter = Mockery::mock(PortalAdapter::class);
        $this->adapter
            ->shouldReceive('getPlatform')
            ->andReturn(Mockery::mock(['getName' => 'MySQL', 'quoteIdentifierChain' => fn($x) => $x]));
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql = Mockery::mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);

        Mockery::spy('Bugo\Compat\Config')->shouldReceive('updateModSettings')->andReturn(null);
        Mockery::spy('Bugo\Compat\Cache\CacheApi')->shouldReceive('clean');
        Mockery::spy('Bugo\Compat\Utils')->shouldReceive('makeWritable')->andReturn(null);

        $this->installer = Mockery::mock(Installer::class, [$this->adapter, $this->sql])->makePartial();
        $this->installer->shouldAllowMockingProtectedMethods();
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
        $reflection = new ReflectionMethod(Installer::class, 'getCreators');

        $creators = $reflection->invoke($this->installer);

        expect($creators)->toBeArray();
    });

    it('returns correct upgraders list', function () {
        $reflection = new ReflectionMethod(Installer::class, 'getUpgraders');

        $upgraders = $reflection->invoke($this->installer);

        expect($upgraders)->toBeArray();
    });

    it('processes tables for mode', function ($mode) {
        $reflection = new ReflectionMethod(Installer::class, 'processTables');

        expect(fn() => $reflection->invoke($this->installer, $mode))->not->toThrow(Exception::class);
    })->with('table modes');

    it('processes upgrade tasks', function () {
        $reflection = new ReflectionMethod(Installer::class, 'processUpgradeTasks');

        expect(fn() => $reflection->invoke($this->installer))->not->toThrow(Exception::class);
    });

    it('cleans background tasks', function () {
        $reflection = new ReflectionMethod(Installer::class, 'cleanBackgroundTasks');

        $deleteMock = Mockery::mock(PortalDelete::class);
        $deleteMock
            ->shouldReceive('where')
            ->with(['task_file LIKE ?' => '%$sourcedir/LightPortal%'])
            ->andReturnSelf();

        $this->sql->shouldReceive('delete')->with('background_tasks')->andReturn($deleteMock);
        $statementMock = Mockery::mock();
        $statementMock->shouldReceive('execute')->once();
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($deleteMock)->andReturn($statementMock);

        $reflection->invoke($this->installer);
    });

    it('sets default settings', function () {
        $reflection = new ReflectionMethod(Installer::class, 'setDefaultSettings');

        $reflection->invoke($this->installer);

        expect(true)->toBeTrue();
    });

    it('sets directory permissions', function () {
        $reflection = new ReflectionMethod(Installer::class, 'setDirectoryPermissions');

        $reflection->invoke($this->installer);

        expect(true)->toBeTrue();
    });

    it('updates settings', function () {
        $reflection = new ReflectionMethod(Installer::class, 'updateSettings');

        $updateMock = Mockery::mock(PortalUpdate::class);
        $updateMock->shouldReceive('set')->with(['value' => (string) time()])->andReturnSelf()->once();
        $updateMock
            ->shouldReceive('where')
            ->with(['variable' => 'settings_updated'])
            ->andReturnSelf()
            ->once();

        $this->sql->shouldReceive('update')->with('settings')->andReturn($updateMock);
        $statementMock = Mockery::mock();
        $statementMock->shouldReceive('execute')->once();
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($updateMock)->andReturn($statementMock);

        $reflection->invoke($this->installer);
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
