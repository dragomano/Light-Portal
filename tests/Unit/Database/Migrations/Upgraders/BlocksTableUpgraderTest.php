<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use LightPortal\Database\Migrations\Creators\TranslationsTableCreator;
use LightPortal\Database\Migrations\Upgraders\BlocksTableUpgrader;
use LightPortal\Database\PortalSql;
use Tests\TestAdapterFactory;

describe('BlocksTableUpgraderTest', function () {
    beforeEach(function () {
        Config::$language = 'english';

        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);

        $translationsCreator = new TranslationsTableCreator($this->sql);
        $translationsCreator->createTable();

        $this->adapter->query(/** @lang text */ "
            CREATE TABLE lp_blocks (
                block_id INTEGER PRIMARY KEY,
                icon VARCHAR(255),
                type VARCHAR(255),
                content TEXT,
                note VARCHAR(510)
            )
        ")->execute();

        $this->adapter->query(/** @lang text */ "
            INSERT INTO lp_blocks (block_id, icon, type, content, note) VALUES
            (1, 'fas fa-user', 'user_info', 'test content 1', 'test note 1'),
            (2, 'fas fa-clock', 'custom', 'test content 2', 'test note 2')
        ")->execute();

        // Pre-insert translation records to simulate existing data migration scenario
        $this->adapter->query(/** @lang text */ "
            INSERT INTO lp_translations (item_id, type, lang, content, description) VALUES
            (1, 'block', 'english', '', ''),
            (2, 'block', 'english', '', '')
        ")->execute();

        $this->upgrader = new BlocksTableUpgrader($this->sql);
    });

    it('migrates data to translations table and drops old columns', function () {
        $this->upgrader->upgrade();

        $result = $this->adapter->query(/** @lang text */ "SELECT * FROM lp_translations ORDER BY item_id, lang");
        $rows   = [];
        foreach ($result->execute() as $row) {
            $rows[] = $row;
        }

        expect($rows)->toHaveCount(2)
            ->and($rows[0]['item_id'])->toBe(1)
            ->and($rows[0]['type'])->toBe('block')
            ->and($rows[0]['lang'])->toBe('english')
            ->and($rows[0]['content'])->toBe('test content 1')
            ->and($rows[0]['description'])->toBe('test note 1')
            ->and($rows[1]['item_id'])->toBe(2)
            ->and($rows[1]['type'])->toBe('block')
            ->and($rows[1]['lang'])->toBe('english')
            ->and($rows[1]['content'])->toBe('test content 2')
            ->and($rows[1]['description'])->toBe('test note 2');

        $result  = $this->adapter->query(/** @lang text */ "PRAGMA table_info(lp_blocks)");
        $columns = [];
        foreach ($result->execute() as $row) {
            $columns[] = $row['name'];
        }

        expect($columns)->not->toContain('content')
            ->and($columns)->not->toContain('note');
    });
});
