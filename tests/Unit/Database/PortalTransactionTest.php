<?php

declare(strict_types=1);

use Bugo\LightPortal\Database\PortalAdapterInterface;
use Bugo\LightPortal\Database\PortalTransaction;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Tests\ReflectionAccessor;

describe('PortalTransaction', function () {
    it('sets connection from adapter in constructor', function () {
        $connection = Mockery::mock(ConnectionInterface::class);
        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('getConnection')->andReturn($connection);
        $adapter = Mockery::mock(PortalAdapterInterface::class);
        $adapter->shouldReceive('getDriver')->andReturn($driver);

        $transaction = new ReflectionAccessor(new PortalTransaction($adapter));
        $actualConnection = $transaction->getProtectedProperty('connection');

        expect($actualConnection)->toBe($connection);
    });

    it('begins transaction and returns connection', function () {
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('beginTransaction')->andReturn($connection);
        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('getConnection')->andReturn($connection);
        $adapter = Mockery::mock(PortalAdapterInterface::class);
        $adapter->shouldReceive('getDriver')->andReturn($driver);

        $transaction = new PortalTransaction($adapter);

        $result = $transaction->begin();

        expect($result)->toBe($connection);
    });

    it('rolls back transaction and returns connection', function () {
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('rollback')->andReturn($connection);
        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('getConnection')->andReturn($connection);
        $adapter = Mockery::mock(PortalAdapterInterface::class);
        $adapter->shouldReceive('getDriver')->andReturn($driver);

        $transaction = new PortalTransaction($adapter);

        $result = $transaction->rollback();

        expect($result)->toBe($connection);
    });

    it('commits transaction and returns connection', function () {
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('commit')->andReturn($connection);
        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('getConnection')->andReturn($connection);
        $adapter = Mockery::mock(PortalAdapterInterface::class);
        $adapter->shouldReceive('getDriver')->andReturn($driver);

        $transaction = new PortalTransaction($adapter);

        $result = $transaction->commit();

        expect($result)->toBe($connection);
    });
});
