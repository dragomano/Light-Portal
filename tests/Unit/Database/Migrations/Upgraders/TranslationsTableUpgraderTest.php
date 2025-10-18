<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use LightPortal\Database\Migrations\Creators\TranslationsTableCreator;
use LightPortal\Database\PortalSql;
use LightPortal\Database\Migrations\Upgraders\TranslationsTableUpgrader;
use Tests\TestAdapterFactory;

describe('TranslationsTableUpgrader', function () {
    beforeEach(function () {
        Config::$language = 'english';

        $this->adapter = TestAdapterFactory::create();
        $this->sql     = new PortalSql($this->adapter);

        $translationsCreator = new TranslationsTableCreator($this->sql);
        $translationsCreator->createTable();

        $this->upgrader = new TranslationsTableUpgrader($this->sql);
    });

    it('upgrades by adding indexes', function () {
        $this->upgrader->upgrade();

        $result = $this->adapter->query(
            /** @lang text */ "SELECT name, sql FROM sqlite_master WHERE type='index' AND tbl_name='lp_translations'"
        );

        $indexes = [];
        foreach ($result->execute() as $row) {
            $indexes[$row['name']] = $row['sql'];
        }

        expect($indexes)->toHaveKey('idx_translations_entity')
            ->and($indexes)->toHaveKey('title_prefix')
            ->and($indexes['idx_translations_entity'])->toContain('type, item_id, lang')
            ->and($indexes['title_prefix'])->toContain('title');
    });
});
