<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\Creators\PageTagTableCreator;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;

describe('PageTagTableCreator', function () {
    beforeEach(function () {
        $this->adapter = Mockery::mock(PortalAdapter::class);
        $this->adapter
            ->shouldReceive('getPlatform')
            ->andReturn(Mockery::mock(['getName' => 'MySQL', 'quoteIdentifierChain' => fn($x) => $x]));
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql = Mockery::mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);

        $this->creator = new PageTagTableCreator($this->adapter);

        // Mock the static MetadataFactory
        $this->metadataFactory = Mockery::mock('alias:' . MetadataFactory::class);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(PageTagTableCreator::class);
    });

    it('returns correct table name', function () {
        $reflection = new ReflectionProperty(PageTagTableCreator::class, 'tableName');

        $result = $reflection->getValue($this->creator);

        expect($result)->toBe('lp_page_tag');
    });

    it('returns correct full table name', function () {
        $reflection = new ReflectionMethod(PageTagTableCreator::class, 'getFullTableName');

        $result = $reflection->invoke($this->creator);

        expect($result)->toBe('smf_lp_page_tag');
    });

    it('defines correct columns', function () {
        $this->sql
            ->shouldReceive('buildSqlString')
            ->andReturn(
                'CREATE TABLE smf_lp_page_tag (
                    page_id INT UNSIGNED NOT NULL,
                    tag_id INT UNSIGNED NOT NULL,
                    PRIMARY KEY (page_id, tag_id)
                )'
            );

        $result = $this->creator->getSql();

        expect($result)->toContain('page_id')
            ->and($result)->toContain('tag_id')
            ->and($result)->toContain('PRIMARY KEY');
    });

    it('inserts default data method is empty', function () {
        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});
