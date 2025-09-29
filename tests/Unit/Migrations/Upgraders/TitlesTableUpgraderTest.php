<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\LightPortal\Migrations\Columns\MediumText;
use Bugo\LightPortal\Migrations\Operations\PortalInsert;
use Bugo\LightPortal\Migrations\Operations\PortalSelect;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Bugo\LightPortal\Migrations\Upgraders\TitlesTableUpgrader;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\SqlInterface;

describe('TitlesTableUpgrader', function () {
    beforeEach(function () {
        Config::$language = 'english';

        $this->adapter = mock(PortalAdapter::class);
        $this->sql = mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);
        $this->upgrader = mock(TitlesTableUpgrader::class, [$this->adapter])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    });

    afterEach(function () {
        Mockery::close();
    });

    dataset('column definitions', [
        ['content', ['type' => 'mediumtext', 'nullable' => true], MediumText::class],
        ['test_column', ['type' => 'varchar', 'size' => 100, 'nullable' => true, 'default' => 'default_value'], Varchar::class],
        ['test_int_column', ['type' => 'int', 'size' => 11, 'nullable' => false, 'default' => 0], Integer::class],
    ]);

    dataset('migrate row scenarios', [
        [0, true],
        [1, false],
    ]);

    it('constructs with adapter and sql', function () {
        expect($this->upgrader)->toBeInstanceOf(TitlesTableUpgrader::class);
    });

    it('upgrades by adding columns, migrating data, and renaming table', function () {
        $this->upgrader->shouldReceive('tableExists')->andReturn(true);
        $this->upgrader->shouldReceive('addColumn')->twice();
        $this->upgrader->shouldReceive('changeColumn')->once();
        $this->upgrader->shouldReceive('renameTable')->once();

        $this->upgrader->updateTable();
    });

    it('skips upgrade if table does not exist', function () {
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->upgrader->shouldReceive('tableExists')->andReturn(false);

        $this->upgrader->updateTable();

        expect(true)->toBeTrue();
    });

    it('defines columns correctly', function ($columnName, $params, $expectedClass) {
        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('defineColumn');

        $column = $method->invoke($this->upgrader, $columnName, $params);

        expect($column)->toBeInstanceOf($expectedClass)
            ->and($column->getName())->toBe($columnName);
    })->with('column definitions');

    it('gets full table name with prefix', function () {
        $this->adapter->shouldReceive('getPrefix')->andReturn('test_prefix_');

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('getFullTableName');

        $fullName = $method->invoke($this->upgrader, 'table_name');

        expect($fullName)->toBe('test_prefix_table_name');
    });

    it('executes sql query', function () {
        $builder = mock(SqlInterface::class);
        $sqlString = 'SELECT 1';

        $this->sql->shouldReceive('buildSqlString')->with($builder)->andReturn($sqlString);
        $this->adapter->shouldReceive('query')->with($sqlString, Adapter::QUERY_MODE_EXECUTE)->once();

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('executeSql');

        $method->invoke($this->upgrader, $builder);

        expect(true)->toBeTrue();
    });

    it('adds column', function () {
        $this->sql->shouldReceive('buildSqlString')->andReturn('some sql');
        $this->adapter->shouldReceive('query');
        $this->upgrader->shouldReceive('columnExists')->with('new_column')->andReturn(false);
        $this->upgrader->shouldReceive('alterColumn')->with('add', 'new_column', [])->once();

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('addColumn');

        $method->invoke($this->upgrader, 'new_column', []);
    });

    it('changes column', function () {
        $this->sql->shouldReceive('buildSqlString')->andReturn('some sql');
        $this->adapter->shouldReceive('query');
        $this->upgrader->shouldReceive('columnExists')->with('old_name')->andReturn(false);
        $this->upgrader->shouldReceive('alterColumn')->with('change', 'old_name', [], 'new_name')->once();

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('changeColumn');

        $method->invoke($this->upgrader, 'old_name', 'new_name', []);
    });

    it('drops column', function () {
        $this->sql->shouldReceive('buildSqlString')->andReturn('some sql');
        $this->adapter->shouldReceive('query');
        $this->upgrader->shouldReceive('columnExists')->with('column_name')->andReturn(false);
        $this->upgrader->shouldReceive('alterColumn')->with('drop', 'column_name')->once();

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('dropColumn');

        $method->invoke($this->upgrader, 'column_name');
    });

    it('adds index', function () {
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter
            ->shouldReceive('query')
            ->with(/** @lang text */ 'CREATE INDEX IF NOT EXISTS idx_test ON smf_lp_titles (col1, col2)', Adapter::QUERY_MODE_EXECUTE)
            ->once();

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('addIndex');

        $method->invoke($this->upgrader, 'idx_test', ['col1', 'col2']);

        expect(true)->toBeTrue();
    });

    it('adds prefix index', function () {
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter
            ->shouldReceive('query')
            ->with(/** @lang text */ 'CREATE INDEX IF NOT EXISTS idx_prefix ON smf_lp_titles (column(100))', Adapter::QUERY_MODE_EXECUTE)
            ->once();

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('addPrefixIndex');

        $method->invoke($this->upgrader, 'idx_prefix', 'column', 100);

        expect(true)->toBeTrue();
    });

    it('renames table', function () {
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter
            ->shouldReceive('query')
            ->with('RENAME TABLE smf_lp_titles TO smf_lp_translations', Adapter::QUERY_MODE_EXECUTE)
            ->once();

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('renameTable');

        $method->invoke($this->upgrader, 'lp_translations');

        expect(true)->toBeTrue();
    });





    it('handles migrating row to translations based on existence', function ($count, $shouldInsert) {
        $select = mock(PortalSelect::class);
        $this->sql->shouldReceive('select')->with('lp_translations')->andReturn($select);
        $select->shouldReceive('where')->andReturnSelf();
        $select->shouldReceive('columns')->andReturnSelf();

        $statement = mock();
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($select)->andReturn($statement);
        $result = mock();
        $statement->shouldReceive('execute')->andReturn($result);
        $row = ['count' => $count];

        $result->shouldReceive('current')->andReturn($row);

        if ($shouldInsert) {
            $this->sql->shouldReceive('buildSqlString')->andReturn('some sql');
            $this->adapter->shouldReceive('query');
            $insert = mock(PortalInsert::class);
            $this->sql->shouldReceive('insert')->with('lp_translations')->andReturn($insert);
            $insert->shouldReceive('values')->andReturnSelf();
            $this->upgrader->shouldReceive('executeSql')->with($insert)->once();
        } else {
            $this->sql->shouldNotReceive('insert');
        }

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('migrateRowToTranslations');

        $method->invoke($this->upgrader, 1, 'page', 'test content', 'test desc');
    })->with('migrate row scenarios');
});
