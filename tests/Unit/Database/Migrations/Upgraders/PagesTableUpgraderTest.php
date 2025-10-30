<?php

declare(strict_types=1);

use LightPortal\Database\Migrations\Creators\TranslationsTableCreator;
use LightPortal\Database\Migrations\Upgraders\PagesTableUpgrader;
use LightPortal\Database\PortalSql;
use Tests\TestAdapterFactory;

describe('PagesTableUpgrader', function () {
    beforeEach(function () {
        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);

        $translationsCreator = new TranslationsTableCreator($this->sql);
        $translationsCreator->createTable();

        $this->adapter->query(/** @lang text */ "
            CREATE TABLE lp_pages (
                page_id INTEGER PRIMARY KEY,
                category_id INTEGER,
                author_id INTEGER,
                alias VARCHAR(255),
                title VARCHAR(255),
                content TEXT,
                description TEXT,
                type VARCHAR(10),
                permissions INTEGER,
                status INTEGER,
                created_at INTEGER,
                updated_at INTEGER
            )
        ")->execute();

        $this->adapter->query(/** @lang text */ "
            INSERT INTO lp_pages (
                page_id, category_id, author_id, alias, title, content,
                description, type, permissions, status, created_at, updated_at
            ) VALUES
                (1, 1, 1, 'test-page-1', 'Test Page 1', 'Test content 1',
                'Test description 1', 'bbc', 1, 1, 1672531200, 1672531200),
                (2, 1, 1, 'test-page-2', 'Test Page 2', 'Test content 2',
                'Test description 2', 'bbc', 1, 1, 1672617600, 1672617600)
        ")->execute();

        // Insert existing translations with title (simulating records that already exist for pages)
        $this->adapter->query(/** @lang text */ "
            INSERT INTO lp_translations (item_id, type, lang, title) VALUES
                (1, 'page', 'english', 'Test Page 1'),
                (2, 'page', 'english', 'Test Page 2')
        ")->execute();

        $this->upgrader = new PagesTableUpgrader($this->sql);
    });

    it('migrates data to translations table and adds index and drops old columns', function () {
        $this->upgrader->upgrade();

        $result = $this->adapter->query(/** @lang text */ "SELECT * FROM lp_translations ORDER BY item_id, lang");
        $rows   = [];
        foreach ($result->execute() as $row) {
            $rows[] = $row;
        }

        expect($rows)->toHaveCount(2)
            ->and($rows[0]['item_id'])->toBe(1)
            ->and($rows[0]['type'])->toBe('page')
            ->and($rows[0]['lang'])->toBe('english')
            ->and($rows[0]['title'])->toBe('Test Page 1') // Title should be preserved
            ->and($rows[0]['content'])->toBe('Test content 1')
            ->and($rows[0]['description'])->toBe('Test description 1')
            ->and($rows[1]['item_id'])->toBe(2)
            ->and($rows[1]['type'])->toBe('page')
            ->and($rows[1]['lang'])->toBe('english')
            ->and($rows[1]['title'])->toBe('Test Page 2') // Title should be preserved
            ->and($rows[1]['content'])->toBe('Test content 2')
            ->and($rows[1]['description'])->toBe('Test description 2');

        $result  = $this->adapter->query(/** @lang text */ "PRAGMA table_info(lp_pages)");
        $columns = [];
        foreach ($result->execute() as $row) {
            $columns[] = $row['name'];
        }

        expect($columns)->not->toContain('content')
            ->and($columns)->not->toContain('description');
    });

    it('upgrades by adding index on created_at', function () {
        $this->upgrader->upgrade();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='lp_pages'"
        );

        $indexes = [];
        foreach ($result->execute() as $row) {
            $indexes[] = $row['name'];
        }

        expect($indexes)->toContain('idx_pages_created_at');
    });
});
