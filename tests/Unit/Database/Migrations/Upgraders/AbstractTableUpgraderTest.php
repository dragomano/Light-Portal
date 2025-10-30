<?php

declare(strict_types=1);

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Ddl\Column\Column;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\SqlInterface;
use LightPortal\Database\Migrations\Columns\MediumText;
use LightPortal\Database\Migrations\Upgraders\AbstractTableUpgrader;
use LightPortal\Database\Operations\PortalInsert;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\Operations\PortalUpdate;
use LightPortal\Database\PortalAdapterInterface;
use LightPortal\Database\PortalResultInterface;
use LightPortal\Database\PortalSql;
use Tests\ReflectionAccessor;

describe('AbstractTableUpgrader', function () {
    beforeEach(function () {
        $this->adapter = mock(PortalAdapterInterface::class);
        $this->adapter->shouldReceive('getDriver')->andReturn(mock());
        $this->adapter->shouldReceive('getTitle')->andReturn('MySQL');

        $this->sql = mock(PortalSql::class);
        $this->sql->shouldReceive('getAdapter')->andReturn($this->adapter);

        $this->upgrader = new class($this->sql) extends AbstractTableUpgrader {
            protected string $tableName = 'old_table';

            public function upgrade(): void {}
        };
    });

    it('defines varchar column correctly', function () {
        $upgrader = new ReflectionAccessor($this->upgrader);

        $column = $upgrader->callProtectedMethod('defineColumn', [
            'test_column',
            [
                'type'     => 'varchar',
                'size'     => 100,
                'nullable' => true,
                'default'  => 'default_value',
            ]
        ]);

        expect($column)->toBeInstanceOf(Varchar::class)
            ->and($column->getName())->toBe('test_column');
    });

    it('defines int column correctly', function () {
        $upgrader = new ReflectionAccessor($this->upgrader);

        $column = $upgrader->callProtectedMethod('defineColumn', [
            'test_int',
            [
                'type'     => 'int',
                'size'     => 10,
                'nullable' => false,
                'default'  => 0,
            ]
        ]);

        expect($column)->toBeInstanceOf(Integer::class)
            ->and($column->getName())->toBe('test_int');
    });

    it('gets full table name with prefix', function () {
        $this->sql->shouldReceive('getPrefix')->andReturn('smf_');

        $upgrader = new ReflectionAccessor($this->upgrader);

        $fullName = $upgrader->callProtectedMethod('getFullTableName', ['test_table']);

        expect($fullName)->toBe('smf_test_table');
    });

    it('executes sql query', function () {
        $builder = mock(SqlInterface::class);
        $sqlString = 'SELECT 1';

        $this->sql->shouldReceive('buildSqlString')->with($builder)->andReturn($sqlString);
        $this->adapter->shouldReceive('query')->with($sqlString, Adapter::QUERY_MODE_EXECUTE);

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod('executeSql', [$builder]);

        expect(true)->toBeTrue();
    });

    it('adds index', function () {
        $platform = mock();
        $platform->shouldReceive('quoteIdentifier')->andReturn('`test_index`');

        $this->adapter->shouldReceive('getPlatform')->andReturn($platform);
        $this->adapter->shouldReceive('getTitle')->andReturn('mysql');
        $this->sql->shouldReceive('getPrefix')->andReturn('smf_');

        $resultMock = mock();
        $resultMock->shouldReceive('toArray')->andReturn([]);

        $this->adapter
            ->shouldReceive('query')
            ->with("SHOW INDEX FROM smf_old_table WHERE Key_name = 'test_index'", Adapter::QUERY_MODE_EXECUTE)
            ->andReturn($resultMock)
            ->shouldReceive('query')
            ->with(/** @lang text */ 'CREATE INDEX `test_index` ON smf_old_table (col1, col2)', Adapter::QUERY_MODE_EXECUTE);

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod('addIndex', ['test_index', ['col1', 'col2']]);

        expect(true)->toBeTrue();
    });

    it('adds prefix index', function () {
        $platform = mock();
        $platform->shouldReceive('quoteIdentifier')
            ->with('test_index')
            ->andReturn('`test_index`');
        $this->adapter->shouldReceive('getPlatform')->andReturn($platform);
        $this->adapter->shouldReceive('getTitle')->andReturn('mysql');

        $this->sql->shouldReceive('getPrefix')->andReturn('smf_');

        $resultMock = mock();
        $resultMock->shouldReceive('toArray')->andReturn([]);

        $this->adapter->shouldReceive('query')
            ->with(Mockery::on(function ($sql) {
                return str_contains($sql, 'SHOW INDEX FROM') || str_contains($sql, 'CREATE INDEX');
            }), Adapter::QUERY_MODE_EXECUTE)
            ->andReturnUsing(function ($sql) use ($resultMock) {
                if (str_contains($sql, 'SHOW INDEX FROM')) {
                    return $resultMock;
                }

                return null;
            });

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod('addPrefixIndex', ['test_index', 'col1', 10]);

        expect(true)->toBeTrue();
    });

    it('renames table', function () {
        $this->sql->shouldReceive('getPrefix')->andReturn('smf_');
        $this->adapter
            ->shouldReceive('query')
            ->with(/** @lang text */ 'ALTER TABLE smf_old_table RENAME TO smf_new_table', Adapter::QUERY_MODE_EXECUTE);

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod('renameTable', ['new_table']);

        expect(true)->toBeTrue();
    });

    it('alters column by adding', function () {
        $this->sql->shouldReceive('columnExists')->with('old_table', 'test_column')->andReturn(false);
        $this->sql->shouldReceive('getPrefix')->andReturn('smf_');

        $sqlString = /** @lang text */ 'ALTER TABLE smf_old_table ADD test_column VARCHAR(255) NOT NULL';
        $this->sql->shouldReceive('buildSqlString')->andReturn($sqlString);
        $this->adapter->shouldReceive('query')->with($sqlString, Adapter::QUERY_MODE_EXECUTE);

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod('alterColumn', ['add', 'test_column', ['type' => 'varchar']]);

        expect(true)->toBeTrue();
    });

    it('alters column by changing', function () {
        $this->sql->shouldReceive('columnExists')->with('old_table', 'old_column')->andReturn(false);
        $this->sql->shouldReceive('getPrefix')->andReturn('smf_');

        $sqlString = /** @lang text */ 'ALTER TABLE smf_old_table CHANGE old_column new_column INT(11) NOT NULL DEFAULT 0';
        $this->sql->shouldReceive('buildSqlString')->andReturn($sqlString);
        $this->adapter->shouldReceive('query')->with($sqlString, Adapter::QUERY_MODE_EXECUTE);

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod('alterColumn', ['change', 'old_column', ['type' => 'int'], 'new_column']);

        expect(true)->toBeTrue();
    });

    it('alters column by dropping', function () {
        $this->sql->shouldReceive('columnExists')->with('old_table', 'test_column')->andReturn(false);
        $this->sql->shouldReceive('getPrefix')->andReturn('smf_');

        $sqlString = /** @lang text */ 'ALTER TABLE smf_old_table DROP COLUMN test_column';
        $this->sql->shouldReceive('buildSqlString')->andReturn($sqlString);
        $this->adapter->shouldReceive('query')->with($sqlString, Adapter::QUERY_MODE_EXECUTE);

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod('alterColumn', ['drop', 'test_column']);

        expect(true)->toBeTrue();
    });

    it('does not alter column if it exists', function () {
        $this->sql->shouldReceive('columnExists')->with('old_table', 'test_column')->andReturn(true);

        $this->sql->shouldNotReceive('buildSqlString');
        $this->adapter->shouldNotReceive('query');

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod('alterColumn', ['add', 'test_column']);

        expect(true)->toBeTrue();
    });

    it('defines column with default value', function () {
        $upgrader = new ReflectionAccessor($this->upgrader);

        $column = $upgrader->callProtectedMethod('defineColumn', [
            'test_column',
            [
                'type'     => 'varchar',
                'size'     => 100,
                'nullable' => true,
                'default'  => 'test_default',
            ]
        ]);

        expect($column->getDefault())->toBe('test_default');
    });

    it('defines column with text type using default match case', function () {
        $upgrader = new ReflectionAccessor($this->upgrader);

        $column = $upgrader->callProtectedMethod('defineColumn', [
            'text_column',
            [
                'type'     => 'text',
                'nullable' => false,
                'default'  => null,
            ]
        ]);

        expect($column)->toBeInstanceOf(Column::class)
            ->and($column->getName())->toBe('text_column')
            ->and($column->getOptions()['type'])->toBe('text');
    });

    it('drops column in sqlite with default value containing parentheses', function () {
        $this->adapter->shouldReceive('getTitle')->andReturn('SQLite');
        $this->sql->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql->shouldReceive('getAdapter')->andReturn($this->adapter);

        $columnsData = [
            ['name' => 'id', 'type' => 'INTEGER', 'notnull' => 1, 'dflt_value' => null, 'pk' => 1],
            ['name' => 'name', 'type' => 'VARCHAR(255)', 'notnull' => 1, 'dflt_value' => '1', 'pk' => 0],
            ['name' => 'status', 'type' => 'INTEGER', 'notnull' => 0, 'dflt_value' => null, 'pk' => 0],
        ];

        $this->adapter->shouldReceive('query')
            ->with('PRAGMA table_info(smf_old_table)', Adapter::QUERY_MODE_EXECUTE)
            ->andReturn(new ArrayIterator($columnsData));

        // Allow any queries with proper structure
        $this->adapter->shouldReceive('query')
            ->with(Mockery::on(function ($sql) {
                if (str_contains($sql, 'CREATE TABLE')) {
                    return str_contains($sql, "id INTEGER NOT NULL PRIMARY KEY") &&
                        str_contains($sql, "status INTEGER");
                }

                if (str_contains($sql, 'INSERT INTO')) {
                    return str_contains($sql, /** @lang text */ "SELECT id, status FROM smf_old_table");
                }

                if (str_contains($sql, 'DROP TABLE')) {
                    return str_contains($sql, /** @lang text */ 'DROP TABLE smf_old_table');
                }

                if (str_contains($sql, 'ALTER TABLE')) {
                    return str_contains($sql, 'RENAME TO old_table');
                }

                return false;
            }), Adapter::QUERY_MODE_EXECUTE);

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod('dropColumnSqlite', ['name']);

        expect(true)->toBeTrue();
    });

    it('migrates rows to translations using update when record exists', function () {
        $this->adapter->shouldReceive('getTitle')->andReturn('MySQL');

        $rows = [
            ['page_id' => 1, 'content' => 'Test content 1', 'description' => 'Test description 1'],
        ];

        $resultMock = mock(PortalResultInterface::class);
        $resultMock->shouldReceive('rewind');
        $resultMock->shouldReceive('valid')->andReturn(true, false);
        $resultMock->shouldReceive('current')->andReturn($rows[0]);
        $resultMock->shouldReceive('next')->once();

        $selectMock = mock(PortalSelect::class);
        $selectMock
            ->shouldReceive('where')
            ->with(['item_id' => 1, 'type' => 'page', 'lang' => 'english'])
            ->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_translations')->andReturn($selectMock);

        $selectResultMock = mock(PortalResultInterface::class);
        $selectResultMock->shouldReceive('count')->andReturn(1);

        $this->sql->shouldReceive('execute')->with($selectMock)->andReturn($selectResultMock);

        $updateMock = mock(PortalUpdate::class);
        $updateMock
            ->shouldReceive('set')
            ->with(['content' => 'Test content 1', 'description' => 'Test description 1'])
            ->andReturnSelf();
        $updateMock
            ->shouldReceive('where')
            ->with(['item_id' => 1, 'type' => 'page', 'lang' => 'english'])
            ->andReturnSelf();

        $this->sql->shouldReceive('update')->with('lp_translations')->andReturn($updateMock);
        $this->sql->shouldReceive('execute')->with($updateMock);

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod('migrateRowsToTranslations', ['page_id', 'page', $resultMock]);

        expect(true)->toBeTrue();
    });

    it('migrates rows to translations using insert when record does not exist', function () {
        $this->adapter->shouldReceive('getTitle')->andReturn('MySQL');

        $rows = [
            ['page_id' => 1, 'title' => '', 'content' => 'Test content 1', 'description' => 'Test description 1'],
        ];

        $resultMock = mock(PortalResultInterface::class);
        $resultMock->shouldReceive('rewind');
        $resultMock->shouldReceive('valid')->andReturn(true, false);
        $resultMock->shouldReceive('current')->andReturn($rows[0]);
        $resultMock->shouldReceive('next')->once();

        $selectMock = mock(PortalSelect::class);
        $selectMock
            ->shouldReceive('where')
            ->with(['item_id' => 1, 'type' => 'page', 'lang' => 'english'])
            ->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_translations')->andReturn($selectMock);

        $selectResultMock = mock(PortalResultInterface::class);
        $selectResultMock->shouldReceive('count')->andReturn(0);

        $this->sql->shouldReceive('execute')->with($selectMock)->andReturn($selectResultMock);

        $insertMock = mock(PortalInsert::class);
        $insertMock->shouldReceive('values')->with([
            'item_id'     => 1,
            'type'        => 'page',
            'lang'        => 'english',
            'title'       => '',
            'content'     => 'Test content 1',
            'description' => 'Test description 1',
        ])->andReturnSelf();

        $this->sql->shouldReceive('insert')->with('lp_translations')->andReturn($insertMock);
        $this->sql->shouldReceive('execute')->with($insertMock);

        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod('migrateRowsToTranslations', ['page_id', 'page', $resultMock]);

        expect(true)->toBeTrue();
    });

    dataset('column definitions', [
        ['test_column', ['type' => 'varchar', 'size' => 100, 'nullable' => true, 'default' => 'default_value'], Varchar::class],
        ['test_int', ['type' => 'int', 'size' => 10, 'nullable' => false, 'default' => 0], Integer::class],
        ['test_content', ['type' => 'mediumtext', 'nullable' => true], MediumText::class],
    ]);

    it('defines columns correctly', function ($columnName, $params, $expectedClass) {
        $upgrader = new ReflectionAccessor($this->upgrader);

        $column = $upgrader->callProtectedMethod('defineColumn', [$columnName, $params]);

        expect($column)->toBeInstanceOf($expectedClass)
            ->and($column->getName())->toBe($columnName);
    })->with('column definitions');
});
