<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Laminas\Db\Extra\Adapter\ExtendedProfiler;
use LightPortal\Database\PortalAdapter;
use LightPortal\Database\PortalAdapterFactory;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $reflection = new ReflectionAccessor(PortalAdapterFactory::class);
    $reflection->setProperty('instance', null);

    Config::$db_type   = 'sqlite';
    Config::$db_server = 'localhost';
    Config::$db_name   = ':memory:';
    Config::$db_prefix = '';
    Config::$db_user   = '';
    Config::$db_passwd = '';
});

afterEach(function () {
    $reflection = new ReflectionAccessor(PortalAdapterFactory::class);
    $reflection->setProperty('instance', null);
});

describe('PortalAdapterFactory', function () {
    it('creates adapter with sqlite driver', function () {
        $adapter = PortalAdapterFactory::create();

        expect($adapter)->toBeInstanceOf(PortalAdapter::class);

        $params = $adapter->getConfig();

        expect($params['driver'])->toBe('Pdo_Sqlite')
            ->and($params['database'])->toBe(':memory:')
            ->and($params['hostname'])->toBe('localhost')
            ->and($params['profiler'])->toBeInstanceOf(ExtendedProfiler::class);
    });

    it('creates adapter with mysql driver by default', function () {
        Config::$db_type   = 'mysql';
        Config::$db_server = 'localhost';
        Config::$db_name   = 'test_db';
        Config::$db_prefix = '`test_db`.';
        Config::$db_user   = 'user';
        Config::$db_passwd = 'pass';

        $adapter = PortalAdapterFactory::create();
        $params  = $adapter->getConfig();

        expect($params['driver'])->toBe('Pdo_Mysql')
            ->and($params['database'])->toBe('test_db')
            ->and($params['hostname'])->toBe('localhost')
            ->and($params['prefix'])->toBe('')
            ->and($params['username'])->toBe('user');
    });

    it('maps postgresql to the pgsql driver', function () {
        Config::$db_type = 'postgresql';

        $reflection = new ReflectionAccessor(PortalAdapterFactory::class);

        expect($reflection->callMethod('getDriver'))->toBe('Pdo_Pgsql');

        Config::$db_type = 'sqlite';

        expect($reflection->callMethod('getDriver'))->toBe('Pdo_Sqlite');

        Config::$db_type = 'mysql';

        expect($reflection->callMethod('getDriver'))->toBe('Pdo_Mysql');
    });

    it('builds prefix correctly when prefix contains db name', function () {
        Config::$db_type   = 'mysql';
        Config::$db_name   = 'smf_db';
        Config::$db_prefix = '`smf_db`.smf_';

        $reflection = new ReflectionAccessor(PortalAdapterFactory::class);
        $reflection->setProperty('instance', null);

        $adapter = PortalAdapterFactory::create();

        expect($adapter->getConfig()['prefix'])->toBe('smf_');
    });

    it('reuses the same adapter instance for subsequent calls without config', function () {
        $first  = PortalAdapterFactory::create();
        $second = PortalAdapterFactory::create();

        expect($second)->toBe($first);
    });

    it('creates a new adapter when custom config is passed', function () {
        $first = PortalAdapterFactory::create();

        $custom = PortalAdapterFactory::create(['database' => '/tmp/custom.db']);

        expect($custom)->not->toBe($first)
            ->and($custom)->toBeInstanceOf(PortalAdapter::class)
            ->and($custom->getConfig()['database'])->toBe('/tmp/custom.db')
            ->and(PortalAdapterFactory::create())->toBe($first);
    });
});
