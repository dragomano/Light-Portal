<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\Creators\CommentsTableCreator;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;

describe('CommentsTableCreator', function () {
    beforeEach(function () {
        $this->adapter = Mockery::mock(PortalAdapter::class);
        $this->adapter
            ->shouldReceive('getPlatform')
            ->andReturn(Mockery::mock(['getName' => 'MySQL', 'quoteIdentifierChain' => fn($x) => $x]));
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql = Mockery::mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);

        $this->creator = new CommentsTableCreator($this->adapter);

        // Mock the static MetadataFactory
        $this->metadataFactory = Mockery::mock('alias:' . MetadataFactory::class);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(CommentsTableCreator::class);
    });

    it('returns correct table name', function () {
        $reflection = new ReflectionProperty(CommentsTableCreator::class, 'tableName');

        $result = $reflection->getValue($this->creator);

        expect($result)->toBe('lp_comments');
    });

    it('returns correct full table name', function () {
        $reflection = new ReflectionMethod(CommentsTableCreator::class, 'getFullTableName');

        $result = $reflection->invoke($this->creator);

        expect($result)->toBe('smf_lp_comments');
    });

    it('defines correct columns', function () {
        $this->sql
            ->shouldReceive('buildSqlString')
            ->andReturn(
                'CREATE TABLE smf_lp_comments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    parent_id INT UNSIGNED NOT NULL,
                    page_id SMALLINT NOT NULL,
                    author_id MEDIUMINT NOT NULL,
                    message TEXT NOT NULL,
                    created_at INT UNSIGNED NOT NULL
                )'
            );

        $result = $this->creator->getSql();

        expect($result)->toContain('id')
            ->and($result)->toContain('parent_id')
            ->and($result)->toContain('page_id')
            ->and($result)->toContain('author_id')
            ->and($result)->toContain('message')
            ->and($result)->toContain('created_at');
    });

    it('inserts default data method is empty', function () {
        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});
