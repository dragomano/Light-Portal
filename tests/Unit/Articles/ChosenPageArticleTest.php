<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Articles\ChosenPageArticle;
use LightPortal\Articles\PageArticle;
use LightPortal\Database\PortalSql;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Repositories\PageRepositoryInterface;
use Tests\AppMockRegistry;
use Tests\PortalTable;
use Tests\ReflectionAccessor;
use Tests\Table;
use Tests\TestAdapterFactory;

beforeEach(function() {
    User::$me = new User(1);
    User::$me->language = 'english';
    User::$me->groups = [0];

    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::CATEGORIES->value)->execute();
    $adapter->query(PortalTable::COMMENTS->value)->execute();
    $adapter->query(PortalTable::PAGE_TAG->value)->execute();
    $adapter->query(PortalTable::PAGES->value)->execute();
    $adapter->query(PortalTable::PARAMS->value)->execute();
    $adapter->query(PortalTable::TAGS->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();
    $adapter->query(Table::MEMBERS->value)->execute();

    // Enable SQLite function for GREATEST
    $pdo = $adapter->getDriver()->getConnection()->getResource();
    $pdo->sqliteCreateFunction('GREATEST', function ($a, $b) {
        return max($a, $b);
    });

    $this->sql = new PortalSql($adapter);
    $this->article = new ChosenPageArticle($this->sql);

    $mockRepository = Mockery::mock(PageRepositoryInterface::class);
    $mockRepository->shouldReceive('fetchTags')->andReturn(new ArrayIterator([]));
    AppMockRegistry::set(PageRepositoryInterface::class, $mockRepository);
});

arch()
    ->expect(ChosenPageArticle::class)
    ->toExtend(PageArticle::class)
    ->toImplement(ArticleInterface::class);

it('can initialize with selected pages', function () {
    Config::$modSettings['lp_frontpage_pages'] = '1,3,5';

    $this->article->init();

    $accessor = new ReflectionAccessor($this->article);
    $selectedPages = $accessor->getProtectedProperty('selectedPages');
    $wheres = $accessor->getProtectedProperty('wheres');
    $params = $accessor->getProtectedProperty('params');

    expect($selectedPages)->toBe(['1', '3', '5'])
        ->and($wheres)->toContain(['p.page_id' => ['1', '3', '5']])
        ->and($params)->toHaveKey('selected_pages')
        ->and($params['selected_pages'])->toBe(['1', '3', '5']);
});

it('can get selected pages data with real database', function () {
    Config::$modSettings['lp_frontpage_pages'] = '1,2';

    $now = time();
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES
            (0, 1, 'test-page-1', ?, ?, ?, ?, 10, 5, ?, 0, 0, 0),
            (0, 1, 'test-page-2', ?, ?, ?, ?, 20, 3, ?, 0, 0, 0),
            (0, 1, 'test-page-3', ?, ?, ?, ?, 15, 7, ?, 0, 0, 0)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name, avatar)
        VALUES (1, 'Test Author', 'test_author', '')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES
            (1, 'page', 'english', 'Test Page 1', 'Test content 1', 'Test description 1'),
            (2, 'page', 'english', 'Test Page 2', 'Test content 2', 'Test description 2'),
            (3, 'page', 'english', 'Test Page 3', 'Test content 3', 'Test description 3')
    ")->execute();

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toBeArray()
        ->and($data)->toHaveCount(2)
        ->and($data)->toHaveKey(1)
        ->and($data)->toHaveKey(2)
        ->and($data[1]['title'])->toBe('Test Page 1')
        ->and($data[2]['title'])->toBe('Test Page 2')
        ->and($data[1]['views']['num'])->toBe(10)
        ->and($data[2]['views']['num'])->toBe(20);
});

it('can get total count for selected pages', function () {
    Config::$modSettings['lp_frontpage_pages'] = '1,3';

    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES
            (0, 1, 'test-page-1', ?, ?, ?, ?, 10, 5, ?, 0, 0, 0),
            (0, 1, 'test-page-2', ?, ?, ?, ?, 20, 3, ?, 0, 0, 0),
            (0, 2, 'test-page-3', ?, ?, ?, ?, 15, 7, ?, 0, 0, 0)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name, avatar)
        VALUES (1, 'Test Author', 'test_author', ''), (2, 'Test Author 2', 'test_author2', '')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES
            (1, 'page', 'english', 'Test Page 1', 'Content 1', 'Description 1'),
            (2, 'page', 'english', 'Test Page 2', 'Content 2', 'Description 2'),
            (3, 'page', 'english', 'Test Page 3', 'Content 3', 'Description 3')
    ")->execute();

    $this->article->init();
    $count = $this->article->getTotalCount();

    expect($count)->toBe(2);
});

it('can get sorting options', function () {
    $options = $this->article->getSortingOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('created;desc')
        ->and($options)->toHaveKey('title')
        ->and($options)->toHaveKey('num_views;desc');
});

it('returns empty array when no selected pages for getData', function () {
    Config::$modSettings['lp_frontpage_pages'] = '';

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

it('returns zero count when no selected pages for getTotalCount', function () {
    Config::$modSettings['lp_frontpage_pages'] = '';

    $this->article->init();
    $count = $this->article->getTotalCount();

    expect($count)->toBe(0);
});

it('can prepare tags with mocked repository', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (0, 1, 'test-page', ?, ?, ?, ?, 10, 5, ?, 0, 0, 0)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        time(),
    ]);

    $mockRepository = Mockery::mock(PageRepositoryInterface::class);
    $mockRepository->shouldReceive('fetchTags')->andReturn(new ArrayIterator([
        1 => [
            'tag_id' => 1,
            'slug' => 'test-tag',
            'icon' => 'fas fa-tag',
            'href' => '/portal/tag/test-tag',
            'name' => 'Test Tag',
        ],
    ]));
    AppMockRegistry::set(PageRepositoryInterface::class, $mockRepository);

    $pages = [
        1 => ['id' => 1],
    ];

    $this->article->init();
    $this->article->prepareTags($pages);

    expect($pages[1])->toHaveKey('tags')
        ->and($pages[1]['tags'])->toBeArray()
        ->and($pages[1]['tags'])->toHaveCount(1)
        ->and($pages[1]['tags'][0]['name'])->toBe('Test Tag')
        ->and($pages[1]['tags'][0]['slug'])->toBe('test-tag');
});

it('returns empty when no pages for prepareTags', function () {
    $pages = [];

    $this->article->init();
    $this->article->prepareTags($pages);

    expect($pages)->toBeEmpty();
});

it('filters pages correctly based on selected pages', function () {
    Config::$modSettings['lp_frontpage_pages'] = '2,4';

    $now = time();
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES
            (0, 1, 'page-1', ?, ?, ?, ?, 10, 0, ?, 0, 0, 0),
            (0, 1, 'page-2', ?, ?, ?, ?, 20, 0, ?, 0, 0, 0),
            (0, 1, 'page-3', ?, ?, ?, ?, 30, 0, ?, 0, 0, 0),
            (0, 1, 'page-4', ?, ?, ?, ?, 40, 0, ?, 0, 0, 0)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name, avatar)
        VALUES (1, 'Test Author', 'test_author', '')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES
            (1, 'page', 'english', 'Page 1', 'Content 1', 'Description 1'),
            (2, 'page', 'english', 'Page 2', 'Content 2', 'Description 2'),
            (3, 'page', 'english', 'Page 3', 'Content 3', 'Description 3'),
            (4, 'page', 'english', 'Page 4', 'Content 4', 'Description 4')
    ")->execute();

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(2)
        ->and($data)->toHaveKey(2)
        ->and($data)->toHaveKey(4)
        ->and($data[2]['title'])->toBe('Page 2')
        ->and($data[4]['title'])->toBe('Page 4');
});

it('handles empty selected pages setting', function () {
    Config::$modSettings['lp_frontpage_pages'] = '';

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');
    $count = $this->article->getTotalCount();

    expect($result)->toBeEmpty()
        ->and($count)->toBe(0);
});

it('handles single selected page', function () {
    Config::$modSettings['lp_frontpage_pages'] = '1';

    $now = time();
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES
            (0, 1, 'page-1', ?, ?, ?, ?, 10, 0, ?, 0, 0, 0),
            (0, 1, 'page-2', ?, ?, ?, ?, 20, 0, ?, 0, 0, 0)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name, avatar)
        VALUES (1, 'Test Author', 'test_author', '')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES
            (1, 'page', 'english', 'Page 1', 'Content 1', 'Description 1'),
            (2, 'page', 'english', 'Page 2', 'Content 2', 'Description 2')
    ")->execute();

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');
    $count = $this->article->getTotalCount();

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1)
        ->and($data)->toHaveKey(1)
        ->and($data[1]['title'])->toBe('Page 1')
        ->and($count)->toBe(1);
});
