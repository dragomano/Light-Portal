<?php

declare(strict_types=1);

use Bugo\LightPortal\Database\PortalAdapter;
use Bugo\LightPortal\Database\PortalAdapterFactory;
use Bugo\LightPortal\Database\PortalProfiler;
use Bugo\Compat\Config;
use Laminas\Db\Adapter\Platform\PlatformInterface;

describe('PortalAdapterFactory', function () {
    beforeEach(function () {
        $this->originalDbType   = Config::$db_type ?? 'mysql';
        $this->originalDbServer = Config::$db_server ?? 'localhost';
        $this->originalDbName   = Config::$db_name ?? 'test_db';
        $this->originalDbPrefix = Config::$db_prefix ?? '';
        $this->originalDbUser   = Config::$db_user ?? 'user';
        $this->originalDbPasswd = Config::$db_passwd ?? 'pass';
        $this->originalDbPort   = Config::$db_port ?? 3306;

        $this->profilerMock = Mockery::mock(PortalProfiler::class);
        $this->platformMock = Mockery::mock(PlatformInterface::class);

        $this->portalAdapterFactoryMock = Mockery::mock('alias:Bugo\LightPortal\Database\PortalAdapterFactory');
        $this->portalAdapterFactoryMock->shouldReceive('getPlatform')->andReturn($this->platformMock);
    });

    afterEach(function () {
        if (isset($this->originalDbType)) Config::$db_type = $this->originalDbType;
        if (isset($this->originalDbServer)) Config::$db_server = $this->originalDbServer;
        if (isset($this->originalDbName)) Config::$db_name = $this->originalDbName;
        if (isset($this->originalDbPrefix)) Config::$db_prefix = $this->originalDbPrefix;
        if (isset($this->originalDbUser)) Config::$db_user = $this->originalDbUser;
        if (isset($this->originalDbPasswd)) Config::$db_passwd = $this->originalDbPasswd;
        if (isset($this->originalDbPort)) Config::$db_port = $this->originalDbPort;
    });

    it('creates adapter with mysql driver by default', function () {
        Config::$db_type = 'mysql';
        Config::$db_server = 'localhost';
        Config::$db_name = 'test_db';
        Config::$db_prefix = '`test_db`.';
        Config::$db_user = 'user';
        Config::$db_passwd = 'pass';
        Config::$db_port = 3306;

        $adapterMock = Mockery::mock(PortalAdapter::class);
        $adapterMock->shouldReceive('getConfig')->andReturn([
            'driver'   => 'Pdo_Mysql',
            'database' => 'test_db',
            'hostname' => 'localhost',
            'prefix'   => '',
            'username' => 'user',
            'password' => 'pass',
            'profiler' => $this->profilerMock,
        ]);

        $this->portalAdapterFactoryMock->shouldReceive('create')->andReturn($adapterMock);

        $adapter = PortalAdapterFactory::create();
        expect($adapter)->toBeInstanceOf(PortalAdapter::class);

        $params = $adapter->getConfig();
        expect($params['driver'])->toBe('Pdo_Mysql')
            ->and($params['database'])->toBe('test_db')
            ->and($params['hostname'])->toBe('localhost');
    });

    it('creates adapter with postgresql driver', function () {
        Config::$db_type   = 'postgresql';
        Config::$db_server = 'pg_host';
        Config::$db_name   = 'pg_db';
        Config::$db_user   = 'pg_user';
        Config::$db_passwd = 'pg_pass';

        $adapterMock = Mockery::mock(PortalAdapter::class);
        $adapterMock->shouldReceive('getConfig')->andReturn([
            'driver'   => 'Pdo_Pgsql',
            'database' => 'pg_db',
            'hostname' => 'pg_host',
            'prefix'   => '',
            'username' => 'pg_user',
            'password' => 'pg_pass',
            'profiler' => $this->profilerMock,
        ]);

        $this->portalAdapterFactoryMock->shouldReceive('create')->andReturn($adapterMock);

        $adapter = PortalAdapterFactory::create();
        $params = $adapter->getConfig();

        expect($params['driver'])->toBe('Pdo_Pgsql')
            ->and($params['database'])->toBe('pg_db')
            ->and($params['hostname'])->toBe('pg_host');
    });

    it('creates adapter with sqlite driver', function () {
        Config::$db_type   = 'sqlite';
        Config::$db_name   = 'sqlite_file.db';
        Config::$db_prefix = '';
        Config::$db_user   = '';
        Config::$db_passwd = '';

        $adapterMock = Mockery::mock(PortalAdapter::class);
        $adapterMock->shouldReceive('getConfig')->andReturn([
            'driver'   => 'Pdo_Sqlite',
            'database' => 'sqlite_file.db',
            'prefix'   => '',
            'username' => '',
            'password' => '',
            'profiler' => $this->profilerMock,
        ]);

        $this->portalAdapterFactoryMock->shouldReceive('create')->andReturn($adapterMock);

        $adapter = PortalAdapterFactory::create();
        $params = $adapter->getConfig();

        expect($params['driver'])->toBe('Pdo_Sqlite')
            ->and($params['database'])->toBe('sqlite_file.db');
    });

    it('builds prefix correctly when prefix contains db name', function () {
        Config::$db_type   = 'mysql';
        Config::$db_name   = 'smf_db';
        Config::$db_prefix = '`smf_db`.smf_';

        $adapterMock = Mockery::mock(PortalAdapter::class);
        $adapterMock->shouldReceive('getConfig')->andReturn([
            'driver'   => 'Pdo_Mysql',
            'database' => 'smf_db',
            'prefix'   => 'smf_',
            'hostname' => 'localhost',
            'username' => 'user',
            'password' => 'pass',
            'profiler' => $this->profilerMock,
        ]);

        $this->portalAdapterFactoryMock->shouldReceive('create')->andReturn($adapterMock);

        $adapter = PortalAdapterFactory::create();
        $params = $adapter->getConfig();

        expect($params['prefix'])->toBe('smf_');
    });

    it('merges options correctly', function () {
        Config::$db_type = 'mysql';
        Config::$db_server = 'localhost';
        Config::$db_name = 'test_db';
        Config::$db_prefix = '`test_db`.';
        Config::$db_user = 'user';
        Config::$db_passwd = 'pass';
        Config::$db_port = 3306;

        $options = [
            'hostname'      => 'custom_host',
            'custom_option' => 'value',
        ];

        $adapterMock = Mockery::mock(PortalAdapter::class);
        $adapterMock->shouldReceive('getConfig')->andReturn([
            'driver'        => 'Pdo_Mysql',
            'database'      => 'test_db',
            'hostname'      => 'custom_host',
            'prefix'        => '',
            'username'      => 'user',
            'password'      => 'pass',
            'custom_option' => 'value',
            'profiler'      => $this->profilerMock,
        ]);

        $this->portalAdapterFactoryMock->shouldReceive('create')->with($options)->andReturn($adapterMock);

        $adapter = PortalAdapterFactory::create($options);
        $params = $adapter->getConfig();

        expect($params['hostname'])->toBe('custom_host')
            ->and($params['custom_option'])->toBe('value')
            ->and($params['database'])->toBe('test_db');
    });
});
