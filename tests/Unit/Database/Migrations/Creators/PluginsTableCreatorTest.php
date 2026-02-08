<?php

declare(strict_types=1);

use LightPortal\Database\Migrations\Creators\PluginsTableCreator;
use LightPortal\Database\PortalSql;
use Tests\ReflectionAccessor;
use Tests\TestAdapterFactory;

describe('PluginsTableCreatorTest', function () {
    beforeEach(function () {
        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);
        $this->creator = new PluginsTableCreator($this->sql);
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(PluginsTableCreator::class);
    });

    it('returns correct table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->getProperty('tableName');

        expect($result)->toBe('lp_plugins');
    });

    it('returns correct full table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->callMethod('getFullTableName');

        expect($result)->toBe('lp_plugins');
    });

    it('defines correct columns', function () {
        $sql = $this->creator->getSql();

        expect($sql)->toContain('id')
            ->and($sql)->toContain('name')
            ->and($sql)->toContain('config')
            ->and($sql)->toContain('value')
            ->and($sql)->toContain('UNIQUE');
    });

    it('creates table successfully', function () {
        $this->creator->createTable();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT name FROM sqlite_master WHERE type='table' AND name='lp_plugins'"
        );
        $statement = $result->execute();

        $tables = [];
        foreach ($statement as $row) {
            $tables[] = $row;
        }

        expect($tables)->toHaveCount(1)
            ->and($tables[0]['name'])->toBe('lp_plugins');
    });

    it('inserts default data', function () {
        $this->creator->createTable();
        $this->creator->insertDefaultData();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT * FROM lp_plugins WHERE name = 'hello_portal' AND config = 'keyboard_navigation'"
        );
        $statement = $result->execute();
        $row = $statement->current();

        expect($row['name'])->toBe('hello_portal')
            ->and($row['config'])->toBe('keyboard_navigation')
            ->and($row['value'])->toBe('1');
    });

    it('does not insert default data if exists', function () {
        $this->creator->createTable();
        $this->creator->insertDefaultData();
        $this->creator->insertDefaultData(); // Try to insert again

        // Verify only one row exists for each config
        $result = $this->adapter->query(
            /** @lang text */ "SELECT COUNT(*) as count FROM lp_plugins WHERE name = 'hello_portal'"
        );
        $statement = $result->execute();
        $row = $statement->current();
        $count = $row['count'];

        expect($count)->toBe(4); // 4 default configs
    });
});
