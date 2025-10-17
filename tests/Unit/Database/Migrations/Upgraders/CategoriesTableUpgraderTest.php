<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\LightPortal\Database\Migrations\Creators\TranslationsTableCreator;
use Bugo\LightPortal\Database\Migrations\Upgraders\CategoriesTableUpgrader;
use Bugo\LightPortal\Database\PortalSql;
use Tests\TestAdapterFactory;

describe('CategoriesTableUpgraderTest', function () {
    beforeEach(function () {
        Config::$language = 'english';

        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);

        $translationsCreator = new TranslationsTableCreator($this->sql);
        $translationsCreator->createTable();

        $this->adapter->query(/** @lang text */ "
            CREATE TABLE lp_categories (
                category_id INTEGER PRIMARY KEY,
                name VARCHAR(255),
                description TEXT,
                priority INTEGER DEFAULT 0
            )
        ")->execute();

        $this->adapter->query(/** @lang text */ "
            INSERT INTO lp_categories (category_id, name, description, priority) VALUES
            (1, 'Test Category 1', 'Test description 1', 1),
            (2, 'Test Category 2', 'Test description 2', 2)
        ")->execute();

        // Pre-insert translation records to simulate existing data migration scenario
        $this->adapter->query(/** @lang text */ "
            INSERT INTO lp_translations (item_id, type, lang, content, description) VALUES
            (1, 'category', 'english', '', ''),
            (2, 'category', 'english', '', '')
        ")->execute();

        $this->upgrader = new CategoriesTableUpgrader($this->sql);
    });

    it('migrates data to translations table and adds slug and parent_id columns and drops description column', function () {
        $this->upgrader->upgrade();

        $result = $this->adapter->query(/** @lang text */ "SELECT * FROM lp_translations ORDER BY item_id, lang");
        $rows   = [];
        foreach ($result->execute() as $row) {
            $rows[] = $row;
        }

        expect($rows)->toHaveCount(2)
            ->and($rows[0]['item_id'])->toBe(1)
            ->and($rows[0]['type'])->toBe('category')
            ->and($rows[0]['lang'])->toBe('english')
            ->and($rows[0]['content'])->toBe('')
            ->and($rows[0]['description'])->toBe('Test description 1')
            ->and($rows[1]['item_id'])->toBe(2)
            ->and($rows[1]['type'])->toBe('category')
            ->and($rows[1]['lang'])->toBe('english')
            ->and($rows[1]['content'])->toBe('')
            ->and($rows[1]['description'])->toBe('Test description 2');

        $result  = $this->adapter->query(/** @lang text */ "PRAGMA table_info(lp_categories)");
        $columns = [];
        foreach ($result->execute() as $row) {
            $columns[] = $row['name'];
        }

        expect($columns)->toContain('slug')
            ->and($columns)->toContain('parent_id')
            ->and($columns)->not->toContain('description');
    });
});
