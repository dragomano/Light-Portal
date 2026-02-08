<?php

declare(strict_types=1);

use LightPortal\Database\Migrations\Creators\ParamsTableCreator;
use LightPortal\Database\PortalSql;
use Tests\ReflectionAccessor;
use Tests\TestAdapterFactory;

describe('ParamsTableCreatorTest', function () {
    beforeEach(function () {
        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);
        $this->creator = new ParamsTableCreator($this->sql);
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(ParamsTableCreator::class);
    });

    it('returns correct table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->getProperty('tableName');

        expect($result)->toBe('lp_params');
    });

    it('returns correct full table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->callMethod('getFullTableName');

        expect($result)->toBe('lp_params');
    });

    it('defines correct columns', function () {
        $sql = $this->creator->getSql();

        expect($sql)->toContain('id')
            ->and($sql)->toContain('item_id')
            ->and($sql)->toContain('type')
            ->and($sql)->toContain('name')
            ->and($sql)->toContain('value')
            ->and($sql)->toContain('UNIQUE');
    });

    it('creates table successfully', function () {
        $this->creator->createTable();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT name FROM sqlite_master WHERE type='table' AND name='lp_params'"
        );
        $statement = $result->execute();

        $tables = [];
        foreach ($statement as $row) {
            $tables[] = $row;
        }

        expect($tables)->toHaveCount(1)
            ->and($tables[0]['name'])->toBe('lp_params');
    });

    it('inserts default data', function () {
        $this->creator->createTable();
        $this->creator->insertDefaultData();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT * FROM lp_params WHERE item_id = 1 AND type = 'page' AND name = 'show_author_and_date'"
        );
        $statement = $result->execute();
        $row = $statement->current();

        expect($row['item_id'])->toBe(1)
            ->and($row['type'])->toBe('page')
            ->and($row['name'])->toBe('show_author_and_date')
            ->and($row['value'])->toBe('0');
    });

    it('does not insert default data if exists', function () {
        $this->creator->createTable();
        $this->creator->insertDefaultData();
        $this->creator->insertDefaultData(); // Try to insert again

        // Verify only one row exists
        $result = $this->adapter->query(/** @lang text */ "SELECT COUNT(*) as count FROM lp_params");
        $statement = $result->execute();
        $row = $statement->current();
        $count = $row['count'];

        expect($count)->toBe(1);
    });
});
