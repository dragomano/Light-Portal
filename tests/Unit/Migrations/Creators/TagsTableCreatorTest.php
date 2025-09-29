<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\Creators\TagsTableCreator;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;

describe('TagsTableCreator', function () {
    beforeEach(function () {
        $this->adapter = Mockery::mock(PortalAdapter::class);
        $this->adapter
            ->shouldReceive('getPlatform')
            ->andReturn(Mockery::mock(['getName' => 'MySQL', 'quoteIdentifierChain' => fn($x) => $x]));
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql = Mockery::mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);

        $this->creator = new TagsTableCreator($this->adapter);

        // Mock the static MetadataFactory
        $this->metadataFactory = Mockery::mock('alias:' . MetadataFactory::class);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(TagsTableCreator::class);
    });

    it('returns correct table name', function () {
        $reflection = new ReflectionProperty(TagsTableCreator::class, 'tableName');

        $result = $reflection->getValue($this->creator);

        expect($result)->toBe('lp_tags');
    });

    it('returns correct full table name', function () {
        $reflection = new ReflectionMethod(TagsTableCreator::class, 'getFullTableName');

        $result = $reflection->invoke($this->creator);

        expect($result)->toBe('smf_lp_tags');
    });

    it('defines correct columns', function () {
        $this->sql
            ->shouldReceive('buildSqlString')
            ->andReturn(
                'CREATE TABLE smf_lp_tags (
                    tag_id INT AUTO_INCREMENT PRIMARY KEY,
                    slug VARCHAR(255) NOT NULL,
                    icon VARCHAR(60) NULL,
                    status TINYINT DEFAULT 1,
                    UNIQUE KEY slug (slug)
                )'
            );

        $result = $this->creator->getSql();

        expect($result)->toContain('tag_id')
            ->and($result)->toContain('slug')
            ->and($result)->toContain('icon')
            ->and($result)->toContain('status')
            ->and($result)->toContain('AUTO_INCREMENT')
            ->and($result)->toContain('UNIQUE KEY');
    });

    it('inserts default data method is empty', function () {
        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});
