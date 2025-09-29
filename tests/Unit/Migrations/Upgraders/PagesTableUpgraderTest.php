<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Bugo\LightPortal\Migrations\Operations\PortalSelect;
use Bugo\LightPortal\Migrations\Upgraders\PagesTableUpgrader;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;

describe('PagesTableUpgrader', function () {
    beforeEach(function () {
        $this->adapter = mock(PortalAdapter::class);
        $this->sql = mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);
        $this->upgrader = mock(PagesTableUpgrader::class, [$this->adapter])
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
        expect($this->upgrader)->toBeInstanceOf(PagesTableUpgrader::class);
    });

    it('upgrades by adding index on created_at', function () {
        $this->upgrader->shouldReceive('migrateData')->once();

        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter
            ->shouldReceive('query')
            ->with(/** @lang text */ 'CREATE INDEX IF NOT EXISTS idx_pages_created_at ON smf_lp_pages (created_at)', Adapter::QUERY_MODE_EXECUTE)->once();

        $this->upgrader->upgrade();
    });

    it('defines columns correctly', function ($columnName, $params, $expectedClass) {
        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('defineColumn');

        $column = $method->invoke($this->upgrader, $columnName, $params);

        expect($column)->toBeInstanceOf($expectedClass)
            ->and($column->getName())->toBe($columnName);
    })->with('column definitions');

    it('migrates data by transferring content and description to translations and dropping columns', function () {
        $mockSelect = mock(PortalSelect::class);
        $mockSelect->shouldReceive('columns')->andReturnSelf();

        $mockResult = mock(Iterator::class);
        $mockResult->shouldReceive('rewind')->once();
        $mockResult->shouldReceive('valid')->andReturn(true, false);
        $mockResult->shouldReceive('current')->andReturn([
            'page_id' => 1,
            'content' => 'test content',
            'description' => 'test description'
        ]);
        $mockResult->shouldReceive('next')->once();

        $mockStatement = mock();
        $mockStatement->shouldReceive('execute')->andReturn($mockResult);

        $this->sql->shouldReceive('select')->with('lp_pages')->andReturn($mockSelect);
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($mockSelect)->andReturn($mockStatement);

        $this->upgrader->shouldReceive('migrateRowToTranslations')
            ->with(1, 'page', 'test content', 'test description')
            ->once();

        $this->upgrader->shouldReceive('dropColumn')->with('content')->once();
        $this->upgrader->shouldReceive('dropColumn')->with('description')->once();

        // Since migrateData is protected, we need to use reflection to call it
        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('migrateData');
        $method->invoke($this->upgrader);
    });
});
