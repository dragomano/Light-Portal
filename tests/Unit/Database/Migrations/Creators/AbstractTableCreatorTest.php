<?php

declare(strict_types=1);

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\SqlInterface;
use LightPortal\Database\Migrations\Creators\AbstractTableCreator;
use LightPortal\Database\Migrations\PortalTable;
use LightPortal\Database\Operations\PortalInsert;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\PortalAdapterInterface;
use LightPortal\Database\PortalResultInterface;
use LightPortal\Database\PortalSqlInterface;
use Tests\ReflectionAccessor;

describe('AbstractTableCreator', function () {
    beforeEach(function () {
        $this->adapter = mock(PortalAdapterInterface::class);
        $this->adapter
            ->shouldReceive('getPlatform')
            ->andReturnUsing(function () {
                $platformMock = mock(PlatformInterface::class);
                $platformMock->shouldReceive('getName')->andReturn('MySQL');
                $platformMock->shouldReceive('quoteIdentifier')->andReturnUsing(fn($x) => $x);
                $platformMock->shouldReceive('quoteIdentifierChain')->andReturnUsing(fn($x) => $x);

                return $platformMock;
            });
        $this->adapter->shouldReceive('getTitle')->andReturn('MySQL')->byDefault();
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);

        $this->sql = mock('alias:AbstractTableCreatorSql', PortalSqlInterface::class);
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

            protected function getDefaultData(): array
            {
                return [];
            }
        };

        $this->creator = new (get_class($this->testClass))($this->sql);
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

        $result = $accessor->callMethod('getFullTableName', [$this->creator]);

        expect($result)->toBe('smf_test_table');
    });

    it('executes sql', function () {
        $builder = mock(SqlInterface::class);
        $this->sql->shouldReceive('buildSqlString')->with($builder)->andReturn('SELECT 1');
        $this->adapter->shouldReceive('query')->with('SELECT 1', Adapter::QUERY_MODE_EXECUTE)->once();

        $accessor = new ReflectionAccessor($this->creator);
        $accessor->callMethod('executeSql', [$builder]);
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
        $select = mock(PortalSelect::class);
        $select->shouldReceive('where')->with($where)->andReturnSelf();
        $select->shouldReceive('columns')
            ->with(['count' => new Expression('COUNT(*)')], false)
            ->andReturnSelf();

        $this->sql->shouldReceive('select')->with('test_table')->andReturn($select);
        $resultMock = mock(PortalResultInterface::class);
        $resultMock->shouldReceive('current')->andReturn(['count' => $count]);
        $this->sql->shouldReceive('execute')->with($select)->andReturn($resultMock);

        if ($shouldInsert) {
            $insert = mock(PortalInsert::class);
            $insert->shouldReceive('columns')->with(['id', 'name'])->andReturnSelf();
            $insert->shouldReceive('values')->with([1, 'test'])->andReturnSelf();

            $this->sql->shouldReceive('insert')->with('test_table')->andReturn($insert);
            $this->sql->shouldReceive('execute')->with($insert);
        } else {
            $this->sql->shouldNotReceive('insert');
        }

        $accessor = new ReflectionAccessor($this->creator);
        $accessor->callMethod('insertDefaultIfNotExists', [$where, ['id', 'name'], [1, 'test']]);

        expect(true)->toBeTrue();
    })->with('insert scenarios');

    it('inserts default data method is empty', function () {
        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});
