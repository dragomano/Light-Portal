<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalAdapterFactory;
use Bugo\Compat\Config;

describe('PortalAdapterFactory', function () {
    beforeEach(function () {
        Config::$db_type = 'mysql';
        Config::$db_server = 'localhost';
        Config::$db_name = 'test_db';
        Config::$db_prefix = '`test_db`.';
        Config::$db_user = 'user';
        Config::$db_passwd = 'pass';
    });

    it('creates adapter with mysql driver by default', function () {
        $adapter = PortalAdapterFactory::create();

        expect($adapter)->toBeInstanceOf(PortalAdapter::class)
            ->and($adapter->getDriver()->getConnection()->getConnectionParameters()['driver'])->toBe('Pdo_Mysql')
            ->and($adapter->getDriver()->getConnection()->getConnectionParameters()['hostname'])->toBe('localhost')
            ->and($adapter->getDriver()->getConnection()->getConnectionParameters()['database'])->toBe('test_db')
            ->and($adapter->getDriver()->getConnection()->getConnectionParameters()['username'])->toBe('user')
            ->and($adapter->getDriver()->getConnection()->getConnectionParameters()['password'])->toBe('pass')
            ->and($adapter->getDriver()->getConnection()->getConnectionParameters()['prefix'])->toBe('');
    });

    it('creates adapter with postgresql driver', function () {
        Config::$db_type = 'postgresql';

        $adapter = PortalAdapterFactory::create();

        expect($adapter)->toBeInstanceOf(PortalAdapter::class)
            ->and($adapter->getDriver()->getConnection()->getConnectionParameters()['driver'])->toBe('Pdo_Pgsql');
    });

    it('creates adapter with sqlite driver', function () {
        Config::$db_type = 'sqlite';

        $adapter = PortalAdapterFactory::create();

        expect($adapter)->toBeInstanceOf(PortalAdapter::class)
            ->and($adapter->getDriver()->getConnection()->getConnectionParameters()['driver'])->toBe('Pdo_Sqlite');
    });

    it('builds prefix correctly when prefix contains db name', function () {
        Config::$db_name = 'smf_db';
        Config::$db_prefix = '`smf_db`.smf_';

        $adapter = PortalAdapterFactory::create();

        expect($adapter->getDriver()->getConnection()->getConnectionParameters()['prefix'])->toBe('smf_');
    });

    it('merges options correctly', function () {
        $options = [
            'hostname' => 'custom_host',
            'custom_option' => 'value'
        ];

        $adapter = PortalAdapterFactory::create($options);

        $params = $adapter->getDriver()->getConnection()->getConnectionParameters();
        expect($params['hostname'])->toBe('custom_host')
            ->and($params['custom_option'])->toBe('value')
            ->and($params['database'])->toBe('test_db');
    });
});
