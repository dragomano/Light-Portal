<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\Creators\PluginsTableCreator;
use Bugo\LightPortal\Migrations\Operations\PortalSelect;
use Bugo\LightPortal\Migrations\Operations\PortalInsert;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;
use Laminas\Db\Sql\Expression;

describe('PluginsTableCreator', function () {
    beforeEach(function () {
        $this->adapter = Mockery::mock(PortalAdapter::class);
        $this->adapter
            ->shouldReceive('getPlatform')
            ->andReturn(Mockery::mock(['getName' => 'MySQL', 'quoteIdentifierChain' => fn($x) => $x]));
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql = Mockery::mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);

        $this->creator = new PluginsTableCreator($this->adapter);

        // Mock the static MetadataFactory
        $this->metadataFactory = Mockery::mock('alias:' . MetadataFactory::class);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(PluginsTableCreator::class);
    });

    it('returns correct table name', function () {
        $reflection = new ReflectionProperty(PluginsTableCreator::class, 'tableName');

        $result = $reflection->getValue($this->creator);

        expect($result)->toBe('lp_plugins');
    });

    it('returns correct full table name', function () {
        $reflection = new ReflectionMethod(PluginsTableCreator::class, 'getFullTableName');

        $result = $reflection->invoke($this->creator);

        expect($result)->toBe('smf_lp_plugins');
    });

    it('defines correct columns', function () {
        $this->sql
            ->shouldReceive('buildSqlString')
            ->andReturn(
                'CREATE TABLE smf_lp_plugins (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    config VARCHAR(100) NOT NULL,
                    value TEXT NULL,
                    UNIQUE KEY name_config (name, config)
                )'
            );

        $result = $this->creator->getSql();

        expect($result)->toContain('id')
            ->and($result)->toContain('name')
            ->and($result)->toContain('config')
            ->and($result)->toContain('value')
            ->and($result)->toContain('UNIQUE KEY');
        });

    it('inserts default data', function () {
        $select = Mockery::mock(PortalSelect::class);
        $select
            ->shouldReceive('where')
            ->with(['name' => 'hello_portal', 'config' => 'keyboard_navigation'])
            ->andReturnSelf();
        $select
            ->shouldReceive('columns')
            ->with(['count' => new Expression('COUNT(*)')], false)
            ->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_plugins')->andReturn($select);
        $statement = Mockery::mock();
        $statement->shouldReceive('execute')->andReturn(Mockery::mock(['current' => ['count' => 0]]));
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($select)->andReturn($statement);

        $insert = Mockery::mock(PortalInsert::class);
        $insert->shouldReceive('columns')->with(['name', 'config', 'value'])->andReturnSelf();
        $insert->shouldReceive('values')->with(['hello_portal', 'keyboard_navigation', '1'])->andReturnSelf();
        $insert->shouldReceive('values')->with(['hello_portal', 'show_buttons', '1'])->andReturnSelf();
        $insert->shouldReceive('values')->with(['hello_portal', 'show_progress', '1'])->andReturnSelf();
        $insert->shouldReceive('values')->with(['hello_portal', 'theme', 'flattener'])->andReturnSelf();

        $this->sql->shouldReceive('insert')->with('lp_plugins')->andReturn($insert);
        $insertStatement = Mockery::mock();
        $insertStatement->shouldReceive('execute')->times(4);
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($insert)->andReturn($insertStatement);

        $this->creator->insertDefaultData();
    });

    it('does not insert default data if exists', function () {
        $select = Mockery::mock(PortalSelect::class);
        $select
            ->shouldReceive('where')
            ->with(['name' => 'hello_portal', 'config' => 'keyboard_navigation'])
            ->andReturnSelf();
        $select
            ->shouldReceive('columns')
            ->with(['count' => new Expression('COUNT(*)')], false)
            ->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_plugins')->andReturn($select);
        $statement = Mockery::mock();
        $statement->shouldReceive('execute')->andReturn(Mockery::mock(['current' => ['count' => 1]]));
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($select)->andReturn($statement);

        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});
