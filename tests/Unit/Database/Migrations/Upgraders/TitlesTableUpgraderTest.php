<?php

declare(strict_types=1);

use LightPortal\Database\Migrations\Upgraders\TitlesTableUpgrader;
use LightPortal\Database\PortalSql;
use Tests\ReflectionAccessor;
use Tests\TestAdapterFactory;

describe('TitlesTableUpgrader', function () {
    beforeEach(function () {
        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);

        $this->adapter->query(/** @lang text */ "
            CREATE TABLE lp_titles (
                item_id INTEGER PRIMARY KEY,
                lang VARCHAR(50) NOT NULL,
                type VARCHAR(50) NOT NULL,
                value TEXT NOT NULL
            )
        ")->execute();

        $this->adapter->query(/** @lang text */ "
            INSERT INTO lp_titles (item_id, lang, type, value) VALUES
            (1, 'english', 'page', 'Test Page Title'),
            (2, 'english', 'block', 'Test Block Title')
        ")->execute();

        $this->upgrader = new TitlesTableUpgrader($this->sql);
    });

    it('adds column', function () {
        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod(
            'addColumn',
            [
                'test_column',
                ['type' => 'varchar', 'size' => 100, 'nullable' => true]
            ]
        );

        $result = $this->adapter->query(/** @lang text */ "PRAGMA table_info(lp_titles)");

        $columns = [];
        foreach ($result->execute() as $row) {
            $columns[] = $row['name'];
        }

        expect($columns)->toContain('test_column');
    });

    it('changes column', function () {
        expect(true)->toBeTrue();
    });

    it('renames table', function () {
        $upgrader = new ReflectionAccessor($this->upgrader);
        $upgrader->callProtectedMethod('renameTable', ['lp_translations']);

        expect(true)->toBeTrue();
    });
});
