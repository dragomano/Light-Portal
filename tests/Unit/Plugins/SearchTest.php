<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Laminas\Db\Extra\Adapter\ExtendedProfiler;
use LightPortal\Database\PortalSql;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Plugins\Search\Search;
use Tests\AppMockRegistry;
use Tests\PortalTable;
use Tests\ReflectionAccessor;
use Tests\Table;
use Tests\TestAdapterFactory;

beforeEach(function () {
    Lang::$txt['today'] = 'Today at';
    Lang::$txt['yesterday'] = 'Yesterday at';
    Lang::$txt['lp_search'] = Lang::$txt['lp_search'] ?? [];

    Utils::$context['lp_search_plugin'] = [];

    User::$me = new User(1);
    User::$me->language = 'english';
    User::$me->groups = [0];

    Config::$scripturl = 'https://example.com/index.php';

    $adapter = TestAdapterFactory::create();
    $adapter->setProfiler(new ExtendedProfiler());
    $adapter->query(PortalTable::PAGES->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();
    $adapter->query(Table::MEMBERS->value)->execute();

    // Permission::all() reads the board moderators from the core table
    $adapter->query(/** @lang text */ '
        CREATE TABLE moderators (
            id_board INTEGER NOT NULL DEFAULT 0,
            id_member INTEGER NOT NULL DEFAULT 0
        )
    ')->execute();

    // Enable the SQLite function for GREATEST
    $pdo = $adapter->getDriver()->getConnection()->getResource();
    $pdo->sqliteCreateFunction('GREATEST', fn($a, $b) => max($a, $b));

    $this->sql = new PortalSql($adapter);
    AppMockRegistry::set(PortalSqlInterface::class, $this->sql);

    $this->plugin   = new Search();
    $this->accessor = new ReflectionAccessor($this->plugin);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name) VALUES (1, 'Test Author', 'test_author')
    ")->execute();

    $pages = [
        [1, 'obrien-page', 'O&#039;Brien the 100% farmer'],
        [2, 'plain-page', 'Simple page'],
    ];

    foreach ($pages as [$id, $slug, $title]) {
        // Passing parameters executes the query right away
        $this->sql->getAdapter()->query(/** @lang text */ "
            INSERT INTO lp_pages (
                page_id, category_id, author_id, slug, type, entry_type, permissions, status,
                num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
            ) VALUES (?, 0, 1, ?, ?, ?, ?, ?, 0, 0, ?, 0, 0, 0)
        ", [
            $id,
            $slug,
            ContentType::BBC->value,
            EntryType::DEFAULT->value,
            Permission::ALL->value,
            Status::ACTIVE->value,
            time(),
        ]);

        $this->sql->getAdapter()->query(/** @lang text */ "
            INSERT INTO lp_translations (item_id, type, lang, title, content, description)
            VALUES (?, 'page', 'english', ?, 'Text', '')
        ", [$id, $title]);
    }

    $this->search = fn(string $term) => $this->accessor->callMethod('query', [$term]);
});

it('finds a page by a word with an apostrophe', function () {
    $results = ($this->search)("O'Brien");

    // The word must be entity-encoded once: the second encoding used to break the match
    expect($results)->toHaveCount(1)
        ->and($results[0]['title'])->toBe("O'Brien the 100% farmer");
});

it('does not treat the like wildcards as patterns', function () {
    // Only the page that really contains the percent sign is found
    $results = ($this->search)('%');

    expect($results)->toHaveCount(1)
        ->and($results[0]['title'])->toBe("O'Brien the 100% farmer");
});

it('finds pages by the masks typed by a user', function () {
    expect(($this->search)('Sim*'))->toHaveCount(1)
        ->and(($this->search)('S?mple'))->toHaveCount(1)
        ->and(($this->search)('0%'))->toHaveCount(1);
});

it('finds pages by plain words', function () {
    expect(($this->search)('farmer'))->toHaveCount(1)
        ->and(($this->search)('simple'))->toHaveCount(1)
        ->and(($this->search)('nothing here'))->toBeEmpty();
});
