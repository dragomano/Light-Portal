<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Bugo\LightPortal\Migrations\Upgraders\AbstractTableUpgrader;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Ddl\Column\Column;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\SqlInterface;

describe('AbstractTableUpgrader', function () {
    beforeEach(function () {
        $this->adapter = mock(PortalAdapter::class);
        $this->sql = mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);
        $this->upgrader = new class($this->adapter) extends AbstractTableUpgrader {
            protected string $tableName = 'old_table';

            public function upgrade(): void {}
        };
    });

    it('constructs with adapter and sql', function () {
        expect($this->upgrader)->toBeInstanceOf(AbstractTableUpgrader::class);
    });

    it('defines varchar column correctly', function () {
        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('defineColumn');

        $column = $method->invoke($this->upgrader, 'test_column', [
            'type' => 'varchar',
            'size' => 100,
            'nullable' => true,
            'default' => 'default_value'
        ]);

        expect($column)->toBeInstanceOf(Varchar::class)
            ->and($column->getName())->toBe('test_column');
    });

    it('defines int column correctly', function () {
        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('defineColumn');

        $column = $method->invoke($this->upgrader, 'test_int', [
            'type' => 'int',
            'size' => 10,
            'nullable' => false,
            'default' => 0
        ]);

        expect($column)->toBeInstanceOf(Integer::class)
            ->and($column->getName())->toBe('test_int');
    });

    it('gets full table name with prefix', function () {
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('getFullTableName');

        $fullName = $method->invoke($this->upgrader, 'test_table');

        expect($fullName)->toBe('smf_test_table');
    });

    it('executes sql query', function () {
        $builder = mock(SqlInterface::class);
        $sqlString = 'SELECT 1';

        $this->sql->shouldReceive('buildSqlString')->with($builder)->andReturn($sqlString);
        $this->adapter->shouldReceive('query')->with($sqlString, Adapter::QUERY_MODE_EXECUTE);

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('executeSql');

        $method->invoke($this->upgrader, $builder);

        expect(true)->toBeTrue();
    });

    it('adds index', function () {
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter
            ->shouldReceive('query')
            ->with(/** @lang text */ 'CREATE INDEX IF NOT EXISTS test_index ON smf_old_table (col1, col2)', Adapter::QUERY_MODE_EXECUTE);

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('addIndex');

        $method->invoke($this->upgrader, 'test_index', ['col1', 'col2']);

        expect(true)->toBeTrue();
    });

    it('adds prefix index', function () {
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter
            ->shouldReceive('query')
            ->with(/** @lang text */ 'CREATE INDEX IF NOT EXISTS test_index ON smf_old_table (col1(10))', Adapter::QUERY_MODE_EXECUTE);

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('addPrefixIndex');

        $method->invoke($this->upgrader, 'test_index', 'col1', 10);

        expect(true)->toBeTrue();
    });

    it('renames table', function () {
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter
            ->shouldReceive('query')
            ->with('RENAME TABLE smf_old_table TO smf_new_table', Adapter::QUERY_MODE_EXECUTE);

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('renameTable');

        $method->invoke($this->upgrader, 'new_table');

        expect(true)->toBeTrue();
    });

    it('checks if column exists', function () {
        $metadata = mock();
        $metadata->shouldReceive('getColumnNames')->with('smf_old_table')->andReturn(['col1', 'col2']);

        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');

        // Mock the static method
        Mockery::mock('alias:Laminas\Db\Metadata\Source\Factory')
            ->shouldReceive('createSourceFromAdapter')
            ->andReturn($metadata);

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('columnExists');

        $result = $method->invoke($this->upgrader, 'col1');

        expect($result)->toBeTrue();
    });

    it('checks if column does not exist', function () {
        $metadata = mock();
        $metadata->shouldReceive('getColumnNames')->with('smf_old_table')->andReturn(['col1', 'col2']);

        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');

        Mockery::mock('alias:Laminas\Db\Metadata\Source\Factory')
            ->shouldReceive('createSourceFromAdapter')
            ->andReturn($metadata);

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('columnExists');

        $result = $method->invoke($this->upgrader, 'col3');

        expect($result)->toBeFalse();
    });

    it('checks if table exists', function () {
        $metadata = mock();
        $metadata->shouldReceive('getTableNames')->andReturn(['smf_old_table', 'smf_other']);

        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');

        Mockery::mock('alias:Laminas\Db\Metadata\Source\Factory')
            ->shouldReceive('createSourceFromAdapter')
            ->andReturn($metadata);

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('tableExists');

        $result = $method->invoke($this->upgrader);

        expect($result)->toBeTrue();
    });

    it('checks if table does not exist', function () {
        $metadata = mock();
        $metadata->shouldReceive('getTableNames')->andReturn(['smf_other']);

        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');

        Mockery::mock('alias:Laminas\Db\Metadata\Source\Factory')
            ->shouldReceive('createSourceFromAdapter')
            ->andReturn($metadata);

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('tableExists');

        $result = $method->invoke($this->upgrader);

        expect($result)->toBeFalse();
    });

    it('alters column by adding', function () {
        $metadata = mock();
        $metadata->shouldReceive('getColumnNames')->with('smf_old_table')->andReturn([]); // column does not exist

        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        Mockery::mock('alias:Laminas\Db\Metadata\Source\Factory')
            ->shouldReceive('createSourceFromAdapter')
            ->andReturn($metadata);

        $sqlString = /** @lang text */ 'ALTER TABLE smf_old_table ADD test_column VARCHAR(255) NOT NULL';
        $this->sql->shouldReceive('buildSqlString')->andReturn($sqlString);
        $this->adapter->shouldReceive('query')->with($sqlString, Adapter::QUERY_MODE_EXECUTE);

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('alterColumn');

        $method->invoke($this->upgrader, 'add', 'test_column', ['type' => 'varchar']);

        expect(true)->toBeTrue();
    });

    it('alters column by changing', function () {
        $metadata = mock();
        $metadata
            ->shouldReceive('getColumnNames')
            ->with('smf_old_table')
            ->andReturn([]); // column does not exist

        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        Mockery::mock('alias:Laminas\Db\Metadata\Source\Factory')
            ->shouldReceive('createSourceFromAdapter')
            ->andReturn($metadata);

        $sqlString = /** @lang text */ 'ALTER TABLE smf_old_table CHANGE old_column new_column INT(11) NOT NULL DEFAULT 0';
        $this->sql->shouldReceive('buildSqlString')->andReturn($sqlString);
        $this->adapter->shouldReceive('query')->with($sqlString, Adapter::QUERY_MODE_EXECUTE);

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('alterColumn');

        $method->invoke($this->upgrader, 'change', 'old_column', ['type' => 'int'], 'new_column');

        expect(true)->toBeTrue();
    });

    it('alters column by dropping', function () {
        $metadata = mock();
        $metadata
            ->shouldReceive('getColumnNames')
            ->with('smf_old_table')
            ->andReturn([]); // column does not exist

        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        Mockery::mock('alias:Laminas\Db\Metadata\Source\Factory')
            ->shouldReceive('createSourceFromAdapter')
            ->andReturn($metadata);

        $sqlString = /** @lang text */ 'ALTER TABLE smf_old_table DROP COLUMN test_column';
        $this->sql->shouldReceive('buildSqlString')->andReturn($sqlString);
        $this->adapter->shouldReceive('query')->with($sqlString, Adapter::QUERY_MODE_EXECUTE);

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('alterColumn');

        $method->invoke($this->upgrader, 'drop', 'test_column');

        expect(true)->toBeTrue();
    });

    it('does not alter column if it exists', function () {
        $metadata = mock();
        $metadata
            ->shouldReceive('getColumnNames')
            ->with('smf_old_table')
            ->andReturn(['test_column']); // column exists

        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        Mockery::mock('alias:Laminas\Db\Metadata\Source\Factory')
            ->shouldReceive('createSourceFromAdapter')
            ->andReturn($metadata);

        // Should not call sql or adapter->query since column exists
        $this->sql->shouldNotReceive('buildSqlString');
        $this->adapter->shouldNotReceive('query');

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('alterColumn');

        $method->invoke($this->upgrader, 'add', 'test_column');

        expect(true)->toBeTrue();
    });

    it('defines column with default value', function () {
        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('defineColumn');

        $column = $method->invoke($this->upgrader, 'test_column', [
            'type' => 'varchar',
            'size' => 100,
            'nullable' => true,
            'default' => 'test_default'
        ]);

        expect($column->getDefault())->toBe('test_default');
    });

    it('defines column with text type using default match case', function () {
        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('defineColumn');

        $column = $method->invoke($this->upgrader, 'text_column', [
            'type' => 'text',
            'nullable' => false,
            'default' => null
        ]);

        expect($column)->toBeInstanceOf(Column::class)
            ->and($column->getName())->toBe('text_column')
            ->and($column->getOptions()['type'])->toBe('text');
    });

    it('returns false when getting metadata throws exception', function () {
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');

        Mockery::mock('alias:Laminas\Db\Metadata\Source\Factory')
            ->shouldReceive('createSourceFromAdapter')
            ->andThrow(new Exception('Metadata error'));

        $reflection = new ReflectionClass($this->upgrader);
        $method = $reflection->getMethod('columnExists');

        $result = $method->invoke($this->upgrader, 'col1');

        expect($result)->toBeFalse();
    });
});
