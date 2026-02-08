<?php

declare(strict_types=1);

use LightPortal\Database\Migrations\Creators\PagesTableCreator;
use LightPortal\Database\PortalSql;
use Tests\ReflectionAccessor;
use Tests\TestAdapterFactory;

describe('PagesTableCreatorTest', function () {
    beforeEach(function () {
        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);
        $this->creator = new PagesTableCreator($this->sql);
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(PagesTableCreator::class);
    });

    it('returns correct table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->getProperty('tableName');

        expect($result)->toBe('lp_pages');
    });

    it('returns correct full table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->callMethod('getFullTableName');

        expect($result)->toBe('lp_pages');
    });

    it('defines correct columns', function () {
        $sql = $this->creator->getSql();

        expect($sql)->toContain('page_id')
            ->and($sql)->toContain('category_id')
            ->and($sql)->toContain('author_id')
            ->and($sql)->toContain('slug')
            ->and($sql)->toContain('type')
            ->and($sql)->toContain('entry_type')
            ->and($sql)->toContain('permissions')
            ->and($sql)->toContain('status')
            ->and($sql)->toContain('num_views')
            ->and($sql)->toContain('num_comments')
            ->and($sql)->toContain('created_at')
            ->and($sql)->toContain('updated_at')
            ->and($sql)->toContain('deleted_at')
            ->and($sql)->toContain('last_comment_id');
    });

    it('creates table successfully', function () {
        $this->creator->createTable();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT name FROM sqlite_master WHERE type='table' AND name='lp_pages'"
        );
        $statement = $result->execute();

        $tables = [];
        foreach ($statement as $row) {
            $tables[] = $row;
        }

        expect($tables)->toHaveCount(1)
            ->and($tables[0]['name'])->toBe('lp_pages');
    });

    it('inserts default data', function () {
        $this->creator->createTable();
        $this->creator->insertDefaultData();

        $result = $this->adapter->query(/** @lang text */ "SELECT * FROM lp_pages WHERE page_id = 1");
        $statement = $result->execute();
        $row = $statement->current();

        expect($row['page_id'])->toBe(1)
            ->and($row['slug'])->toBe('home')
            ->and($row['type'])->toBe('html')
            ->and($row['permissions'])->toBe(3);
    });

    it('does not insert default data if exists', function () {
        $this->creator->createTable();
        $this->creator->insertDefaultData();
        $this->creator->insertDefaultData(); // Try to insert again

        // Verify only one row exists
        $result = $this->adapter->query(/** @lang text */ "SELECT COUNT(*) as count FROM lp_pages");
        $statement = $result->execute();
        $row = $statement->current();
        $count = $row['count'];

        expect($count)->toBe(1);
    });
});
