<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;

describe('PortalAdapter', function () {
    it('returns the correct prefix from connection parameters', function () {
        $adapter = new PortalAdapter([
            'driver' => 'Pdo_Mysql',
            'database' => 'test_db',
            'username' => 'test_user',
            'password' => 'test_pass',
            'hostname' => 'localhost',
            'prefix' => 'smf_',
        ]);

        expect($adapter->getPrefix())->toBe('smf_');
    });

    it('returns empty string when prefix is not set', function () {
        $adapter = new PortalAdapter([
            'driver' => 'Pdo_Mysql',
            'database' => 'test_db',
            'username' => 'test_user',
            'password' => 'test_pass',
            'hostname' => 'localhost',
        ]);

        expect($adapter->getPrefix())->toBe('');
    });

    it('returns PortalSql instance from getSql method', function () {
        $adapter = new PortalAdapter([
            'driver' => 'Pdo_Mysql',
            'database' => 'test_db',
            'username' => 'test_user',
            'password' => 'test_pass',
            'hostname' => 'localhost',
            'prefix' => 'smf_',
        ]);

        $sql = $adapter->getSqlBuilder();

        expect($sql)->toBeInstanceOf(PortalSql::class);
    });
});
