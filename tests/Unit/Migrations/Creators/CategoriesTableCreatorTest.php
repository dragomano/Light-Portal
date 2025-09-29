<?php

declare(strict_types=1);

use Bugo\LightPortal\Migrations\Creators\CategoriesTableCreator;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalSql;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;

describe('CategoriesTableCreator', function () {
    beforeEach(function () {
        $this->adapter = Mockery::mock(PortalAdapter::class);
        $this->adapter
            ->shouldReceive('getPlatform')
            ->andReturn(Mockery::mock(['getName' => 'MySQL', 'quoteIdentifierChain' => fn($x) => $x]));
        $this->adapter->shouldReceive('getCurrentSchema')->andReturn(null);
        $this->adapter->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql = Mockery::mock(PortalSql::class);
        $this->adapter->shouldReceive('getSqlBuilder')->andReturn($this->sql);

        $this->creator = new CategoriesTableCreator($this->adapter);

        // Mock the static MetadataFactory
        $this->metadataFactory = Mockery::mock('alias:' . MetadataFactory::class);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(CategoriesTableCreator::class);
    });

    it('returns correct table name', function () {
        $reflection = new ReflectionProperty(CategoriesTableCreator::class, 'tableName');

        $result = $reflection->getValue($this->creator);

        expect($result)->toBe('lp_categories');
    });

    it('returns correct full table name', function () {
        $reflection = new ReflectionMethod(CategoriesTableCreator::class, 'getFullTableName');

        $result = $reflection->invoke($this->creator);

        expect($result)->toBe('smf_lp_categories');
    });

    it('defines correct columns', function () {
        $this->sql
            ->shouldReceive('buildSqlString')
            ->andReturn(
                'CREATE TABLE smf_lp_categories (
                    category_id INT AUTO_INCREMENT PRIMARY KEY,
                    parent_id INT UNSIGNED NOT NULL,
                    slug VARCHAR(255) NOT NULL UNIQUE,
                    icon VARCHAR(60) NULL,
                    priority TINYINT NOT NULL,
                    status TINYINT NOT NULL DEFAULT 1
                )'
            );

        $result = $this->creator->getSql();

        expect($result)->toContain('category_id')
            ->and($result)->toContain('parent_id')
            ->and($result)->toContain('slug')
            ->and($result)->toContain('icon')
            ->and($result)->toContain('priority')
            ->and($result)->toContain('status');
    });

    it('inserts default data method is empty', function () {
        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});
