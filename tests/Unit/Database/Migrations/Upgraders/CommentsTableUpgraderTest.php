<?php

declare(strict_types=1);

use LightPortal\Database\Migrations\Creators\TranslationsTableCreator;
use LightPortal\Database\Migrations\Upgraders\CommentsTableUpgrader;
use LightPortal\Database\PortalSql;
use Tests\TestAdapterFactory;

describe('CommentsTableUpgrader', function () {
    beforeEach(function () {
        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);

        $translationsCreator = new TranslationsTableCreator($this->sql);
        $translationsCreator->createTable();

        $this->adapter->query(/** @lang text */ "
            CREATE TABLE lp_comments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                parent_id INTEGER NOT NULL,
                page_id INTEGER NOT NULL,
                author_id INTEGER NOT NULL,
                message TEXT NOT NULL,
                created_at INTEGER NOT NULL
            )
        ")->execute();

        $this->adapter->query(/** @lang text */ "
            INSERT INTO lp_comments (parent_id, page_id, author_id, message, created_at) VALUES
                (0, 1, 1, 'Test comment 1', 1640995200),
                (0, 1, 2, 'Test comment 2', 1640995300),
                (1, 1, 1, 'Test reply', 1640995400)
        ")->execute();

        $this->upgrader = new CommentsTableUpgrader($this->sql);
    });

    it('migrates message data to translations table and adds index on created_at', function () {
        $this->upgrader->upgrade();

        $result = $this->adapter
            ->query(/** @lang text */ "SELECT * FROM lp_translations WHERE type = 'comment' ORDER BY item_id, lang");
        $rows   = [];
        foreach ($result->execute() as $row) {
            $rows[] = $row;
        }

        expect($rows)->toHaveCount(3)
            ->and($rows[0]['item_id'])->toBe(1)
            ->and($rows[0]['type'])->toBe('comment')
            ->and($rows[0]['lang'])->toBe('english')
            ->and($rows[0]['content'])->toBe('Test comment 1')
            ->and($rows[1]['item_id'])->toBe(2)
            ->and($rows[1]['type'])->toBe('comment')
            ->and($rows[1]['lang'])->toBe('english')
            ->and($rows[1]['content'])->toBe('Test comment 2')
            ->and($rows[2]['item_id'])->toBe(3)
            ->and($rows[2]['type'])->toBe('comment')
            ->and($rows[2]['lang'])->toBe('english')
            ->and($rows[2]['content'])->toBe('Test reply');

        $result = $this->adapter->query(
            /** @lang text */ "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='lp_comments'"
        );

        $indexes = [];
        foreach ($result->execute() as $row) {
            $indexes[] = $row['name'];
        }

        expect($indexes)->toContain('idx_comments_created_at');

        $result  = $this->adapter->query(/** @lang text */ "PRAGMA table_info(lp_comments)");
        $columns = [];
        foreach ($result->execute() as $row) {
            $columns[] = $row['name'];
        }

        expect($columns)->not->toContain('message');
    });
});
