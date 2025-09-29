<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Bugo\LightPortal\Migrations\Upgraders\CommentsTableUpgrader;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;

describe('CommentsTableUpgrader', function () {
    beforeEach(function () {
        $this->adapter = mock(PortalAdapter::class);
        $this->sql = mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);
        $this->upgrader = mock(CommentsTableUpgrader::class, [$this->adapter])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    });

    afterEach(function () {
        Mockery::close();
    });

    dataset('column definitions', [
        ['test_column', ['type' => 'varchar', 'size' => 100, 'nullable' => true, 'default' => 'default_value'], Varchar::class],
        ['test_int', ['type' => 'int', 'size' => 10, 'nullable' => false, 'default' => 0], Integer::class],
    ]);

    it('constructs with adapter and sql', function () {
        expect($this->upgrader)->toBeInstanceOf(CommentsTableUpgrader::class);
    });

    it('upgrades by adding index on created_at', function () {
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter
            ->shouldReceive('query')
            ->with(/** @lang text */ 'CREATE INDEX IF NOT EXISTS idx_comments_created_at ON smf_lp_comments (created_at)', Adapter::QUERY_MODE_EXECUTE)->once();

        $this->upgrader->upgrade();
    });

    it('defines columns correctly', function ($columnName, $params, $expectedClass) {
        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('defineColumn');

        $column = $method->invoke($this->upgrader, $columnName, $params);

        expect($column)->toBeInstanceOf($expectedClass)
            ->and($column->getName())->toBe($columnName);
    })->with('column definitions');
});
