<?php

declare(strict_types=1);

use Bugo\LightPortal\Database\Migrations\PortalTable;
use Bugo\LightPortal\Database\Migrations\Creators\AbstractTableCreator;
use Bugo\LightPortal\Database\Operations\PortalSelect;
use Bugo\LightPortal\Database\Operations\PortalInsert;
use Bugo\LightPortal\Database\PortalAdapterInterface;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\SqlInterface;
use Tests\ReflectionAccessor;

describe('AbstractTableCreator', function () {
    beforeEach(function () {
        $this->adapter = Mockery::mock(PortalAdapterInterface::class);
        $this->adapter
            ->shouldReceive('getPlatform')
            ->andReturn(Mockery::mock(['getName' => 'MySQL', 'quoteIdentifierChain' => fn($x) => $x]));
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);

        $this->sql = Mockery::mock('alias:AbstractTableCreatorSql', PortalSqlInterface::class);
        $this->sql->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql->shouldReceive('getAdapter')->andReturn($this->adapter);

        $this->testClass = new class($this->sql) extends AbstractTableCreator
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

        $this->creator = new (get_class($this->testClass))($this->sql);

        $this->metadataFactory = Mockery::mock('alias:' . MetadataFactory::class);
    });

    afterEach(function () {
        Mockery::close();
    });

    dataset('insert scenarios', [
        [['id' => 1], 0, true],
        [['id' => 1], 1, false],
    ]);

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(AbstractTableCreator::class)
            ->and($this->creator)->toBeInstanceOf($this->testClass::class);
    });

    it('returns correct full table name', function () {
        $accessor = new ReflectionAccessor($this->testClass);

        $result = $accessor->callProtectedMethod('getFullTableName', [$this->creator]);

        expect($result)->toBe('smf_test_table');
    });

    it('executes sql', function () {
        $builder = Mockery::mock(SqlInterface::class);
        $this->sql->shouldReceive('buildSqlString')->with($builder)->andReturn('SELECT 1');
        $this->adapter->shouldReceive('query')->with('SELECT 1', Adapter::QUERY_MODE_EXECUTE)->once();

        $accessor = new ReflectionAccessor($this->creator);
        $accessor->callProtectedMethod('executeSql', [$builder]);
    });

    it('creates table when it does not exist', function () {
        $this->sql->shouldReceive('tableExists')->with('test_table')->andReturn(false);
        $this->sql->shouldReceive('buildSqlString')->andReturn('CREATE TABLE smf_test_table (id VARCHAR(10), name VARCHAR(50))');
        $this->adapter->shouldReceive('query')->andReturn(null);

        $this->creator->createTable();

        expect(true)->toBeTrue();
    });

    it('does not create table when it exists', function () {
        $this->sql->shouldReceive('tableExists')->with('test_table')->andReturn(true);

        $this->creator->createTable();

        expect(true)->toBeTrue();
    });

    it('returns correct sql string', function () {
        $this->sql->shouldReceive('buildSqlString')->andReturn('CREATE TABLE smf_test_table (id VARCHAR(10), name VARCHAR(50))');

        $result = $this->creator->getSql();

        expect($result)->toBe('CREATE TABLE smf_test_table (id VARCHAR(10), name VARCHAR(50))');
    });

    it('handles dropping table based on existence', function ($expected) {
        $this->sql->shouldReceive('tableExists')->with('test_table')->andReturn($expected);

        if ($expected) {
            $this->sql->shouldReceive('buildSqlString')->andReturn(/** @lang text */ 'DROP TABLE smf_test_table');
            $this->adapter->shouldReceive('query')->andReturn(null);
        }

        $this->creator->dropTable();

        expect(true)->toBeTrue();
    })->with([true, false]);

    it('handles inserting default data based on existence', function ($where, $count, $shouldInsert) {
        $select = Mockery::mock(PortalSelect::class);
        $select->shouldReceive('where')->with($where)->andReturnSelf();
        $select->shouldReceive('columns')
            ->with(['count' => new Expression('COUNT(*)')], false)
            ->andReturnSelf();

        $this->sql->shouldReceive('select')->with('test_table')->andReturn($select);
        $resultMock = Mockery::mock(ResultInterface::class);
        $resultMock->shouldReceive('current')->andReturn(['count' => $count]);
        $this->sql->shouldReceive('execute')->with($select)->andReturn($resultMock);

        if ($shouldInsert) {
            $insert = Mockery::mock(PortalInsert::class);
            $insert->shouldReceive('columns')->with(['id', 'name'])->andReturnSelf();
            $insert->shouldReceive('values')->with([1, 'test'])->andReturnSelf();

            $this->sql->shouldReceive('insert')->with('test_table')->andReturn($insert);
            $this->sql->shouldReceive('execute')->with($insert);
        } else {
            $this->sql->shouldNotReceive('insert');
        }

        $accessor = new ReflectionAccessor($this->creator);
        $accessor->callProtectedMethod('insertDefaultIfNotExists', [$where, ['id', 'name'], [1, 'test']]);

        expect(true)->toBeTrue();
    })->with('insert scenarios');

    it('inserts default data method is empty', function () {
        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});
