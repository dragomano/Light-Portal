<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Bugo\LightPortal\Migrations\Upgraders\TranslationsTableUpgrader;
use Laminas\Db\Adapter\Adapter;

describe('TranslationsTableUpgrader', function () {
    beforeEach(function () {
        Config::$language = 'english';

        $this->adapter = mock(PortalAdapter::class);
        $this->sql = mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);
        $this->upgrader = mock(TranslationsTableUpgrader::class, [$this->adapter])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    });

    afterEach(function () {
        Mockery::close();
    });

    it('constructs with adapter and sql', function () {
        expect($this->upgrader)->toBeInstanceOf(TranslationsTableUpgrader::class);
    });

    it('upgrades by adding indexes', function () {
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter
            ->shouldReceive('query')
            ->with(/** @lang text */ 'CREATE INDEX IF NOT EXISTS idx_translations_entity ON smf_lp_translations (type, item_id, lang)', Adapter::QUERY_MODE_EXECUTE)
            ->once();
        $this->adapter
            ->shouldReceive('query')
            ->with(/** @lang text */ 'CREATE INDEX IF NOT EXISTS title_prefix ON smf_lp_translations (title(100))', Adapter::QUERY_MODE_EXECUTE)
            ->once();

        $this->upgrader->upgrade();
    });
});
