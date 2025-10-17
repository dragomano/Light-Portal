<?php

declare(strict_types=1);

use Bugo\LightPortal\Database\Migrations\Upgraders\CommentsTableUpgrader;
use Bugo\LightPortal\Database\PortalSql;
use Tests\TestAdapterFactory;

describe('CommentsTableUpgrader', function () {
    beforeEach(function () {
        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);

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

    it('upgrades by adding index on created_at', function () {
        $this->upgrader->upgrade();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='lp_comments'"
        );

        $indexes = [];
        foreach ($result->execute() as $row) {
            $indexes[] = $row['name'];
        }

        expect($indexes)->toContain('idx_comments_created_at');
    });
});
