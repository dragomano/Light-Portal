<?php

declare(strict_types=1);

use LightPortal\Database\Migrations\Upgraders\TagsTableUpgrader;
use LightPortal\Database\PortalSql;
use Tests\TestAdapterFactory;

describe('TagsTableUpgraderTest', function () {
    beforeEach(function () {
        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);

        $this->adapter->query(/** @lang text */ "
            CREATE TABLE lp_tags (
                tag_id INTEGER PRIMARY KEY,
                name VARCHAR(255)
            )
        ")->execute();

        $this->upgrader = new TagsTableUpgrader($this->sql);
    });

    it('adds slug column to lp_tags table', function () {
        $this->upgrader->upgrade();

        $result  = $this->adapter->query(/** @lang text */ "PRAGMA table_info(lp_tags)");
        $columns = [];
        foreach ($result->execute() as $row) {
            $columns[] = $row['name'];
        }

        expect($columns)->toContain('slug');
    });
});
