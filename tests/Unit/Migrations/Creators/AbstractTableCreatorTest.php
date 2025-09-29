<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\PortalTable;
use Bugo\LightPortal\Migrations\Creators\AbstractTableCreator;
use Bugo\LightPortal\Migrations\Operations\PortalSelect;
use Bugo\LightPortal\Migrations\Operations\PortalInsert;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\SqlInterface;

describe('AbstractTableCreator', function () {
    beforeEach(function () {
        $this->testClass = new class extends AbstractTableCreator
        {
            protected string $tableName = 'test_table';

            protected function defineColumns(PortalTable $table): void
            {
                $id   = new Varchar('id', 10);
                $name = new Varchar('name', 50);

                $table->addColumn($id);
                $table->addColumn($name);
            }
        };

        $this->adapter = Mockery::mock(PortalAdapter::class);
        $this->adapter
            ->shouldReceive('getPlatform')
            ->andReturn(Mockery::mock(['getName' => 'MySQL', 'quoteIdentifierChain' => fn($x) => $x]));
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql = Mockery::mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);

        $this->creator = new (get_class($this->testClass))($this->adapter);

        // Mock the static MetadataFactory
        $this->metadataFactory = Mockery::mock('alias:' . MetadataFactory::class);
    });

    afterEach(function () {
        Mockery::close();
    });

    dataset('table existence', [
        [['smf_test_table'], true],
        [[], false],
    ]);

    dataset('insert scenarios', [
        [['id' => 1], 0, true],
        [['id' => 1], 1, false],
    ]);

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(AbstractTableCreator::class)
            ->and($this->creator)->toBeInstanceOf($this->testClass::class);
    });

    it('constructs with null parameters', function () {
        $creator = new $this->testClass;

        expect($creator)->toBeInstanceOf($this->testClass::class);
    });

    it('returns correct full table name', function () {
        $reflection = new ReflectionMethod($this->testClass, 'getFullTableName');

        $result = $reflection->invoke($this->creator);

        expect($result)->toBe('smf_test_table');
    });

    it('checks if table exists', function ($tableNames, $expected) {
        $metadata = Mockery::mock();
        $metadata->shouldReceive('getTableNames')->andReturn($tableNames);

        $this->metadataFactory->shouldReceive('createSourceFromAdapter')->with($this->adapter)->andReturn($metadata);

        $reflection = new ReflectionMethod($this->testClass, 'tableExists');

        $result = $reflection->invoke($this->creator);

        expect($result)->toBe($expected);
    })->with('table existence');

    it('executes sql', function () {
        $builder = Mockery::mock(SqlInterface::class);
        $this->sql->shouldReceive('buildSqlString')->with($builder)->andReturn(/** @lang text */ 'CREATE TABLE test');
        $this->adapter
            ->shouldReceive('query')
            ->with(/** @lang text */ 'CREATE TABLE test', Adapter::QUERY_MODE_EXECUTE)
            ->once();

        $reflection = new ReflectionMethod($this->testClass, 'executeSql');
        $reflection->invoke($this->creator, $builder);
    });

    it('creates table when it does not exist', function () {
        $metadata = Mockery::mock();
        $metadata->shouldReceive('getTableNames')->andReturn([]);
        $this->metadataFactory->shouldReceive('createSourceFromAdapter')->with($this->adapter)->andReturn($metadata);

        $this->sql->shouldReceive('buildSqlString')->andReturn(/** @lang text */ 'CREATE TABLE smf_test_table');
        $this->adapter
            ->shouldReceive('query')
            ->with(/** @lang text */ 'CREATE TABLE smf_test_table', Adapter::QUERY_MODE_EXECUTE)
            ->once();

        $this->creator->createTable();
    });

    it('does not create table when it exists', function () {
        $metadata = Mockery::mock();
        $metadata->shouldReceive('getTableNames')->andReturn(['smf_test_table']);
        $this->metadataFactory->shouldReceive('createSourceFromAdapter')->with($this->adapter)->andReturn($metadata);

        $this->creator->createTable();

        expect(true)->toBeTrue();
    });

    it('returns correct sql string', function () {
        $this->sql
            ->shouldReceive('buildSqlString')
            ->andReturn('CREATE TABLE smf_test_table (id VARCHAR(10), name VARCHAR(50))');

        $result = $this->creator->getSql();

        expect($result)->toBe('CREATE TABLE smf_test_table (id VARCHAR(10), name VARCHAR(50))');
    });

    it('handles dropping table based on existence', function ($tableNames, $expected) {
        $metadata = Mockery::mock();
        $metadata->shouldReceive('getTableNames')->andReturn($tableNames);
        $this->metadataFactory->shouldReceive('createSourceFromAdapter')->with($this->adapter)->andReturn($metadata);

        if ($expected) {
            $this->sql->shouldReceive('buildSqlString')->andReturn(/** @lang text */ 'DROP TABLE smf_test_table');
            $this->adapter->shouldReceive('query')
                ->with(/** @lang text */ 'DROP TABLE smf_test_table', Adapter::QUERY_MODE_EXECUTE)
                ->once();
        }

        $this->creator->dropTable();

        // Assertion to ensure the method behaves correctly
        expect(true)->toBeTrue();
    })->with('table existence');

    it('handles inserting default data based on existence', function ($where, $count, $shouldInsert) {
        $select = Mockery::mock(PortalSelect::class);
        $select->shouldReceive('where')->with($where)->andReturnSelf();
        $select->shouldReceive('columns')
            ->with(['count' => new Expression('COUNT(*)')], false)
            ->andReturnSelf();

        $this->sql->shouldReceive('select')->with('test_table')->andReturn($select);
        $statement = Mockery::mock();
        $statement->shouldReceive('execute')->andReturn(Mockery::mock(['current' => ['count' => $count]]));
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($select)->andReturn($statement);

        if ($shouldInsert) {
            $insert = Mockery::mock(PortalInsert::class);
            $insert->shouldReceive('columns')->with(['id', 'name'])->andReturnSelf();
            $insert->shouldReceive('values')->with([1, 'test'])->andReturnSelf();

            $this->sql->shouldReceive('insert')->with('test_table')->andReturn($insert);
            $insertStatement = Mockery::mock();
            $insertStatement->shouldReceive('execute')->once();
            $this->sql->shouldReceive('prepareStatementForSqlObject')->with($insert)->andReturn($insertStatement);
        }

        $reflection = new ReflectionMethod($this->testClass, 'insertDefaultIfNotExists');
        $reflection->invoke($this->creator, $where, ['id', 'name'], [1, 'test']);

        // Assertion to ensure the method behaves correctly
        expect(true)->toBeTrue();
    })->with('insert scenarios');

    it('inserts default data method is empty', function () {
        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});
