<?php

declare(strict_types=1);

use LightPortal\Database\Migrations\Creators\BlocksTableCreator;
use LightPortal\Database\PortalSql;
use Bugo\Compat\Utils;
use Tests\ReflectionAccessor;
use Tests\TestAdapterFactory;

describe('BlocksTableCreatorTest', function () {
    beforeEach(function () {
        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);
        $this->creator = new BlocksTableCreator($this->sql);

        Utils::$context = ['right_to_left' => false];
    });

    it('constructs with adapter and sql', function () {
        expect($this->creator)->toBeInstanceOf(BlocksTableCreator::class);
    });

    it('returns correct table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->getProtectedProperty('tableName');

        expect($result)->toBe('lp_blocks');
    });

    it('returns correct full table name', function () {
        $creator = new ReflectionAccessor($this->creator);
        $result = $creator->callProtectedMethod('getFullTableName');

        expect($result)->toBe('lp_blocks');
    });

    it('defines correct columns', function () {
        $sql = $this->creator->getSql();

        expect($sql)->toContain('block_id')
            ->and($sql)->toContain('icon')
            ->and($sql)->toContain('type')
            ->and($sql)->toContain('placement')
            ->and($sql)->toContain('priority')
            ->and($sql)->toContain('permissions')
            ->and($sql)->toContain('status')
            ->and($sql)->toContain('areas')
            ->and($sql)->toContain('title_class')
            ->and($sql)->toContain('content_class');
    });

    it('creates table successfully', function () {
        $this->creator->createTable();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT name FROM sqlite_master WHERE type='table' AND name='lp_blocks'"
        );
        $statement = $result->execute();

        $tables = [];
        foreach ($statement as $row) {
            $tables[] = $row;
        }

        expect($tables)->toHaveCount(1)
            ->and($tables[0]['name'])->toBe('lp_blocks');
    });

    it('inserts default data for left-to-right layout', function () {
        Utils::$context['right_to_left'] = false;

        $this->creator->createTable();
        $this->creator->insertDefaultData();

        $result = $this->adapter->query(/** @lang text */ "SELECT * FROM lp_blocks WHERE block_id = 1");
        $statement = $result->execute();
        $row = $statement->current();

        expect($row['block_id'])->toBe(1)
            ->and($row['icon'])->toBe('fas fa-user')
            ->and($row['type'])->toBe('user_info')
            ->and($row['placement'])->toBe('right')
            ->and($row['permissions'])->toBe(3);
    });

    it('inserts default data for right-to-left layout', function () {
        Utils::$context['right_to_left'] = true;

        $this->creator->createTable();
        $this->creator->insertDefaultData();

        $result = $this->adapter->query(/** @lang text */ "SELECT * FROM lp_blocks WHERE block_id = 1");
        $row = $result->execute()->current();

        expect($row['block_id'])->toBe(1)
            ->and($row['icon'])->toBe('fas fa-user')
            ->and($row['type'])->toBe('user_info')
            ->and($row['placement'])->toBe('left')
            ->and($row['permissions'])->toBe(3);
    });

    it('does not insert default data if exists', function () {
        Utils::$context['right_to_left'] = false;

        $this->creator->createTable();
        $this->creator->insertDefaultData();
        $this->creator->insertDefaultData(); // Try to insert again

        // Verify only one row exists
        $result = $this->adapter->query(/** @lang text */ "SELECT COUNT(*) as count FROM lp_blocks");
        $statement = $result->execute();
        $row = $statement->current();
        $count = $row['count'];

        expect($count)->toBe(1);
    });
});
