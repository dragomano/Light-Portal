<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\Creators\PagesTableCreator;
use Bugo\LightPortal\Migrations\Operations\PortalSelect;
use Bugo\LightPortal\Migrations\Operations\PortalInsert;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;
use Laminas\Db\Sql\Expression;

describe('PagesTableCreator', function () {
    beforeEach(function () {
        $this->adapter = Mockery::mock(PortalAdapter::class);
        $this->adapter
            ->shouldReceive('getPlatform')
            ->andReturn(Mockery::mock(['getName' => 'MySQL', 'quoteIdentifierChain' => fn($x) => $x]));
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql = Mockery::mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);

        $this->creator = new PagesTableCreator($this->adapter);

        // Mock the static MetadataFactory
        $this->metadataFactory = Mockery::mock('alias:' . MetadataFactory::class);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(PagesTableCreator::class);
    });

    it('returns correct table name', function () {
        $reflection = new ReflectionProperty(PagesTableCreator::class, 'tableName');

        $result = $reflection->getValue($this->creator);

        expect($result)->toBe('lp_pages');
    });

    it('returns correct full table name', function () {
        $reflection = new ReflectionMethod(PagesTableCreator::class, 'getFullTableName');

        $result = $reflection->invoke($this->creator);

        expect($result)->toBe('smf_lp_pages');
    });

    it('defines correct columns', function () {
        $this->sql
            ->shouldReceive('buildSqlString')
            ->andReturn(
                'CREATE TABLE smf_lp_pages (
                    page_id INT AUTO_INCREMENT PRIMARY KEY,
                    category_id INT UNSIGNED NOT NULL,
                    author_id MEDIUMINT NOT NULL,
                    slug VARCHAR(255) NOT NULL UNIQUE,
                    type VARCHAR(10) NOT NULL DEFAULT \'bbc\',
                    entry_type VARCHAR(10) NOT NULL DEFAULT \'default\',
                    permissions TINYINT NOT NULL,
                    status TINYINT NOT NULL DEFAULT 1,
                    num_views INT UNSIGNED NOT NULL,
                    num_comments INT UNSIGNED NOT NULL,
                    created_at INT UNSIGNED NOT NULL,
                    updated_at INT UNSIGNED NOT NULL,
                    deleted_at INT UNSIGNED NOT NULL,
                    last_comment_id INT UNSIGNED NOT NULL
                )'
            );

        $result = $this->creator->getSql();

        expect($result)->toContain('page_id')
            ->and($result)->toContain('category_id')
            ->and($result)->toContain('author_id')
            ->and($result)->toContain('slug')
            ->and($result)->toContain('type')
            ->and($result)->toContain('entry_type')
            ->and($result)->toContain('permissions')
            ->and($result)->toContain('status')
            ->and($result)->toContain('num_views')
            ->and($result)->toContain('num_comments')
            ->and($result)->toContain('created_at')
            ->and($result)->toContain('updated_at')
            ->and($result)->toContain('deleted_at')
            ->and($result)->toContain('last_comment_id');
    });

    it('inserts default data', function () {
        $select = Mockery::mock(PortalSelect::class);
        $select->shouldReceive('where')->with(['page_id' => 1])->andReturnSelf();
        $select->shouldReceive('columns')->with(['count' => new Expression('COUNT(*)')], false)->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_pages')->andReturn($select);
        $statement = Mockery::mock();
        $statement->shouldReceive('execute')->andReturn(Mockery::mock(['current' => ['count' => 0]]));
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($select)->andReturn($statement);

        $insert = Mockery::mock(PortalInsert::class);
        $insert->shouldReceive('columns')->with(['page_id', 'author_id', 'slug', 'type', 'permissions', 'created_at'])->andReturnSelf();
        $insert->shouldReceive('values')->with([1, 1, 'home', 'html', 3, time()])->andReturnSelf();

        $this->sql->shouldReceive('insert')->with('lp_pages')->andReturn($insert);
        $insertStatement = Mockery::mock();
        $insertStatement->shouldReceive('execute')->once();
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($insert)->andReturn($insertStatement);

        $this->creator->insertDefaultData();
    });

    it('does not insert default data if exists', function () {
        $select = Mockery::mock(PortalSelect::class);
        $select->shouldReceive('where')->with(['page_id' => 1])->andReturnSelf();
        $select->shouldReceive('columns')->with(['count' => new Expression('COUNT(*)')], false)->andReturnSelf();

        $this->sql->shouldReceive('select')->with('lp_pages')->andReturn($select);
        $statement = Mockery::mock();
        $statement->shouldReceive('execute')->andReturn(Mockery::mock(['current' => ['count' => 1]]));
        $this->sql->shouldReceive('prepareStatementForSqlObject')->with($select)->andReturn($statement);

        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});
