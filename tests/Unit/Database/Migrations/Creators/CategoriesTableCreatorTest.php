<?php

declare(strict_types=1);

use Bugo\LightPortal\Database\Migrations\Creators\CategoriesTableCreator;
use Bugo\LightPortal\Database\PortalSql;
use Tests\ReflectionAccessor;
use Tests\TestAdapterFactory;

describe('CategoriesTableCreatorTest', function () {
    beforeEach(function () {
        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);
        $this->creator = new CategoriesTableCreator($this->sql);
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(CategoriesTableCreator::class);
    });

    it('returns correct table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->getProtectedProperty('tableName');

        expect($result)->toBe('lp_categories');
    });

    it('returns correct full table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->callProtectedMethod('getFullTableName');

        expect($result)->toBe('lp_categories');
    });

    it('defines correct columns', function () {
        $sql = $this->creator->getSql();

        expect($sql)->toContain('category_id')
            ->and($sql)->toContain('parent_id')
            ->and($sql)->toContain('slug')
            ->and($sql)->toContain('icon')
            ->and($sql)->toContain('priority')
            ->and($sql)->toContain('status');
    });

    it('creates table successfully', function () {
        $this->creator->createTable();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT name FROM sqlite_master WHERE type='table' AND name='lp_categories'"
        );
        $statement = $result->execute();

        $tables = [];
        foreach ($statement as $row) {
            $tables[] = $row;
        }

        expect($tables)->toHaveCount(1)
            ->and($tables[0]['name'])->toBe('lp_categories');
    });

    it('inserts default data method is empty', function () {
        $this->creator->insertDefaultData();

        expect(true)->toBeTrue();
    });
});
