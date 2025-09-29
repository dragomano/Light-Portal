<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\LightPortal\Migrations\Operations\PortalSelect;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Bugo\LightPortal\Migrations\Upgraders\BlocksTableUpgrader;
use Laminas\Db\Sql\Expression;

describe('BlocksTableUpgrader', function () {
    beforeEach(function () {
        Config::$language = 'english';

        $this->adapter = mock(PortalAdapter::class);
        $this->sql = mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);
        $this->upgrader = mock(BlocksTableUpgrader::class, [$this->adapter])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    });

    afterEach(function () {
        Mockery::close();
    });

    it('constructs with adapter and sql', function () {
        expect($this->upgrader)->toBeInstanceOf(BlocksTableUpgrader::class);
    });

    it('upgrades by migrating data and dropping columns', function () {
        $select = mock(PortalSelect::class);
        $this->sql->shouldReceive('select')->with('lp_blocks')->andReturn($select);
        $select->shouldReceive('columns')->with([
            'block_id',
            'content' => new Expression("COALESCE(content, '')"),
            'description' => new Expression("COALESCE(note, '')"),
        ])->andReturnSelf();

        $result = mock(Iterator::class);
        $result->shouldReceive('rewind')->once();
        $result->shouldReceive('valid')->andReturn(true, false);
        $result->shouldReceive('current')->andReturn([
            'block_id' => 1,
            'content' => 'test content',
            'description' => 'test desc'
        ]);
        $result->shouldReceive('next')->once();

        $statement = mock();
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($select)->andReturn($statement);
        $statement->shouldReceive('execute')->andReturn($result);

        $this->upgrader->shouldReceive('migrateRowToTranslations')->with(1, 'block', 'test content', 'test desc')->once();
        $this->upgrader->shouldReceive('dropColumn')->with('content')->once();
        $this->upgrader->shouldReceive('dropColumn')->with('note')->once();

        $this->upgrader->upgrade();
    });
});
