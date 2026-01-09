<?php

declare(strict_types=1);

use LightPortal\Database\Migrations\Creators\TranslationsTableCreator;
use LightPortal\Database\PortalSql;
use Tests\ReflectionAccessor;
use Tests\TestAdapterFactory;

describe('TranslationsTableCreatorTest', function () {
    beforeEach(function () {
        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);
        $this->creator = new TranslationsTableCreator($this->sql);
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(TranslationsTableCreator::class);
    });

    it('returns correct table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->getProperty('tableName');

        expect($result)->toBe('lp_translations');
    });

    it('returns correct full table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->callMethod('getFullTableName');

        expect($result)->toBe('lp_translations');
    });

    it('defines correct columns', function () {
        $sql = $this->creator->getSql();

        expect($sql)->toContain('id')
            ->and($sql)->toContain('item_id')
            ->and($sql)->toContain('type')
            ->and($sql)->toContain('lang')
            ->and($sql)->toContain('title')
            ->and($sql)->toContain('content')
            ->and($sql)->toContain('description')
            ->and($sql)->toContain('UNIQUE');
    });

    it('creates table successfully', function () {
        $this->creator->createTable();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT name FROM sqlite_master WHERE type='table' AND name='lp_translations'"
        );
        $statement = $result->execute();

        $tables = [];
        foreach ($statement as $row) {
            $tables[] = $row;
        }

        expect($tables)->toHaveCount(1)
            ->and($tables[0]['name'])->toBe('lp_translations');
    });

    it('inserts default data', function () {
        $this->creator->createTable();
        $this->creator->insertDefaultData();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT * FROM lp_translations WHERE item_id = 1 AND type = 'page' AND lang = 'english'"
        );
        $statement = $result->execute();
        $row = $statement->current();

        expect($row['item_id'])->toBe(1)
            ->and($row['type'])->toBe('page')
            ->and($row['lang'])->toBe('english')
            ->and($row['content'])->toBe('<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>');
    });

    it('does not insert default data if exists', function () {
        $this->creator->createTable();
        $this->creator->insertDefaultData();
        $this->creator->insertDefaultData(); // Try to insert again

        // Verify only one row exists
        $result = $this->adapter->query(/** @lang text */ "SELECT COUNT(*) as count FROM lp_translations");
        $statement = $result->execute();
        $row = $statement->current();
        $count = $row['count'];

        expect($count)->toBe(1);
    });
});
