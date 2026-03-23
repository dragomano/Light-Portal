<?php

declare(strict_types=1);

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use LightPortal\Database\PortalAdapterInterface;
use LightPortal\Database\PortalSql;

describe('PortalSql', function () {
    beforeEach(function () {
        $platform = mock(PlatformInterface::class);
        $platform->shouldReceive('getName')->andReturn('SQLite');

        $this->adapter = mock(PortalAdapterInterface::class);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter->shouldReceive('getPlatform')->andReturn($platform);
        $this->adapter->shouldReceive('getTitle')->andReturn('SQLite');

        $driver = mock(DriverInterface::class);
        $driver->shouldReceive('getDatabasePlatformName')->andReturn('SQLite');
        $this->adapter->shouldReceive('getDriver')->andReturn($driver);

        $this->sql = new PortalSql($this->adapter);
    });

    it('returns prefix from getPrefix method', function () {
        expect($this->sql->getPrefix())->toBe('smf_');
    });

    it('returns adapter from getAdapter method', function () {
        expect($this->sql->getAdapter())->toBeInstanceOf(PortalAdapterInterface::class);
    });
});
