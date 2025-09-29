<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\LightPortal\Migrations\Creators\TranslationsTableCreator;
use Bugo\LightPortal\Migrations\Operations\PortalSelect;
use Bugo\LightPortal\Migrations\Operations\PortalInsert;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;
use Laminas\Db\Sql\Expression;

describe('TranslationsTableCreator', function () {
    beforeEach(function () {
        Config::$language = 'english';
        Config::$mbname = 'Test Forum';

        $this->adapter = Mockery::mock(PortalAdapter::class);
        $this->adapter
            ->shouldReceive('getPlatform')
            ->andReturn(Mockery::mock(['getName' => 'MySQL', 'quoteIdentifierChain' => fn($x) => $x]));
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql = Mockery::mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);

        $this->creator = new TranslationsTableCreator($this->adapter);

        // Mock the static MetadataFactory
        $this->metadataFactory = Mockery::mock('alias:' . MetadataFactory::class);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(TranslationsTableCreator::class);
    });

    it('returns correct table name', function () {
        $reflection = new ReflectionProperty(TranslationsTableCreator::class, 'tableName');

        $result = $reflection->getValue($this->creator);

        expect($result)->toBe('lp_translations');
    });

    it('returns correct full table name', function () {
        $reflection = new ReflectionMethod(TranslationsTableCreator::class, 'getFullTableName');

        $result = $reflection->invoke($this->creator);

        expect($result)->toBe('smf_lp_translations');
    });

    it('defines correct columns', function () {
        $this->sql
            ->shouldReceive('buildSqlString')
            ->andReturn(
                'CREATE TABLE smf_lp_translations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    item_id INT UNSIGNED NOT NULL,
                    type VARCHAR(30) DEFAULT \'block\',
                    lang VARCHAR(20) NOT NULL,
                    title VARCHAR(255),
                    content TEXT,
                    description VARCHAR(510),
                    UNIQUE KEY item_id_type_lang (item_id, type, lang)
                )'
            );

        $result = $this->creator->getSql();

        expect($result)->toContain('id')
            ->and($result)->toContain('item_id')
            ->and($result)->toContain('type')
            ->and($result)->toContain('lang')
            ->and($result)->toContain('title')
            ->and($result)->toContain('content')
            ->and($result)->toContain('description')
            ->and($result)->toContain('AUTO_INCREMENT')
            ->and($result)->toContain('UNIQUE KEY');
    });

    it('inserts default data', function () {
        $select = Mockery::mock(PortalSelect::class);
        $select
            ->shouldReceive('where')
            ->with(['item_id' => 1, 'type' => 'page', 'lang' => 'english'])
            ->andReturnSelf();
        $select
            ->shouldReceive('columns')
            ->with(['count' => new Expression('COUNT(*)')], false)
            ->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_translations')->andReturn($select);
        $statement = Mockery::mock();
        $statement->shouldReceive('execute')->andReturn(Mockery::mock(['current' => ['count' => 0]]));
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($select)->andReturn($statement);

        $insert = Mockery::mock(PortalInsert::class);
        $insert->shouldReceive('columns')
            ->with(['item_id', 'type', 'lang', 'title', 'content'])
            ->andReturnSelf();
        $insert
            ->shouldReceive('values')
            ->with([1, 'page', 'english', 'Test Forum', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'])
            ->andReturnSelf();

        $this->sql->shouldReceive('insert')->with('lp_translations')->andReturn($insert);
        $insertStatement = Mockery::mock();
        $insertStatement->shouldReceive('execute')->once();
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($insert)->andReturn($insertStatement);

        $this->creator->insertDefaultData();
    });

    it('does not insert default data if exists', function () {
        $select = Mockery::mock(PortalSelect::class);
        $select
            ->shouldReceive('where')
            ->with(['item_id' => 1, 'type' => 'page', 'lang' => 'english'])
            ->andReturnSelf();
        $select
            ->shouldReceive('columns')
            ->with(['count' => new Expression('COUNT(*)')], false)
            ->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_translations')->andReturn($select);
        $statement = Mockery::mock();
        $statement->shouldReceive('execute')->andReturn(Mockery::mock(['current' => ['count' => 1]]));
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($select)->andReturn($statement);

        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});
