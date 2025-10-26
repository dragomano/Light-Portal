<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use LightPortal\Articles\Queries\PageArticleQuery;
use LightPortal\Database\PortalSql;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use Laminas\Db\Sql\Where;
use LightPortal\Events\EventDispatcherInterface;
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

    $this->eventsMock = mock(EventDispatcherInterface::class);
    $this->eventsMock->shouldReceive('dispatch')->andReturn(null)->byDefault();

    $this->query = new PageArticleQuery($this->sql, $this->eventsMock);
});

it('can get all pages data with real database', function () {
    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES
            (0, 1, 'test-page-1', ?, ?, ?, ?, 10, 5, ?, 0, 0, 0),
            (0, 1, 'test-page-2', ?, ?, ?, ?, 20, 3, ?, 0, 0, 0)
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
        INSERT INTO members (id_member, real_name, member_name)
        VALUES (1, 'Test Author', 'test_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES
            (1, 'page', 'english', 'Test Page 1', 'Test content 1', 'Test description 1'),
            (2, 'page', 'english', 'Test Page 2', 'Test content 2', 'Test description 2')
    ")->execute();

    $this->query->init([
        'status'               => Status::ACTIVE->value,
        'deleted_at'           => 0,
        'entry_type'           => EntryType::DEFAULT->name(),
        'current_time'         => time(),
        'selected_categories'  => [],
        'permissions'          => Permission::ALL->value,
    ]);

    $this->query->prepareParams(0, 10);

    $result = $this->query->getRawData();

    $data = iterator_to_array($result);

    expect($data)->toBeArray()
        ->and($data)->toHaveCount(2)
        ->and($data)->toHaveKey(0)
        ->and($data)->toHaveKey(1)
        ->and($data[0]['title'])->toBe('Test Page 1')
        ->and($data[1]['title'])->toBe('Test Page 2')
        ->and($data[0]['num_views'])->toBe(10)
        ->and($data[1]['num_views'])->toBe(20);
});

it('can get total count with real database', function () {
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
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES
            (1, 'page', 'english', 'Test Page 1', 'Content 1', 'Description 1'),
            (2, 'page', 'english', 'Test Page 2', 'Content 2', 'Description 2'),
            (3, 'page', 'english', 'Test Page 3', 'Content 3', 'Description 3')
    ")->execute();

    $this->query->init([
        'status'               => Status::ACTIVE->value,
        'deleted_at'           => 0,
        'entry_type'           => EntryType::DEFAULT->name(),
        'current_time'         => time(),
        'selected_categories'  => [],
        'permissions'          => Permission::ALL->value,
    ]);

    $count = $this->query->getTotalCount();

    expect($count)->toBe(3);
});

it('can handle pages with categories in real database', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_categories (category_id, icon, status, slug)
        VALUES (1, 'fas fa-folder', ?, 'test-category')
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title)
        VALUES (1, 'category', 'english', 'Test Category')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (1, 1, 'category-page', ?, ?, ?, ?, 5, 2, ?, 0, 0, 0)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        time(),
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name)
        VALUES (1, 'Author', 'author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES (1, 'page', 'english', 'Category Page', 'Content', 'Description')
    ")->execute();

    $this->query->init([
        'status'               => Status::ACTIVE->value,
        'deleted_at'           => 0,
        'entry_type'           => EntryType::DEFAULT->name(),
        'current_time'         => time(),
        'selected_categories'  => [],
        'permissions'          => Permission::ALL->value,
    ]);

    $this->query->prepareParams(0, 10);

    $result = $this->query->getRawData();

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1)
        ->and($data[0]['cat_title'])->toBe('Test Category')
        ->and($data[0]['title'])->toBe('Category Page');
});

it('can handle pages with comments in real database', function () {
    Config::$modSettings['lp_comment_block'] = 'default';

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (0, 1, 'commented-page', ?, ?, ?, ?, 15, 1, ?, 0, 0, 1)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        time(),
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_params (item_id, type, name, value)
        VALUES (1, 'page', 'allow_comments', '1')
    ")->execute();

    $commentTime = time() - 3600; // 1 hour ago
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_comments (id, parent_id, page_id, author_id, created_at, updated_at)
        VALUES (1, 0, 1, 2, ?, 0)
    ", [$commentTime]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name)
        VALUES
            (1, 'Page Author', 'page_author'),
            (2, 'Comment Author', 'comment_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES
            (1, 'page', 'english', 'Commented Page', 'Page content', 'Page description'),
            (1, 'comment', 'english', 'Comment content', '', '')
    ")->execute();

    $this->query->init([
        'status'               => Status::ACTIVE->value,
        'deleted_at'           => 0,
        'entry_type'           => EntryType::DEFAULT->name(),
        'current_time'         => time(),
        'selected_categories'  => [],
        'permissions'          => Permission::ALL->value,
    ]);

    $this->query->setSorting('last_comment;desc');
    $this->query->prepareParams(0, 10);

    $result = $this->query->getRawData();

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1)
        ->and($data[0]['num_comments'])->toBe(1);
});

it('skips rows with empty title in getData with real database', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (0, 1, 'empty-title-page', ?, ?, ?, ?, 10, 0, ?, 0, 0, 0)
    ", [
        ContentType::BBC->name(),
        EntryType::DEFAULT->name(),
        Permission::ALL->value,
        Status::ACTIVE->value,
        time(),
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name)
        VALUES (1, 'Test Author', 'test_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES (1, 'page', 'english', '', 'Test content', 'Test description')
    ")->execute();

    $this->query->init([
        'status'               => Status::ACTIVE->value,
        'deleted_at'           => 0,
        'entry_type'           => EntryType::DEFAULT->name(),
        'current_time'         => time(),
        'selected_categories'  => [],
        'permissions'          => Permission::ALL->value,
    ]);

    $this->query->prepareParams(0, 10);

    $result = $this->query->getRawData();

    $data = iterator_to_array($result);

    expect($data)->toBeEmpty();
});

it('filters pages by selected categories', function () {
    Config::$modSettings['lp_frontpage_categories'] = '1,2';

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_categories (category_id, icon, status, slug)
        VALUES
            (1, 'fas fa-folder', ?, 'category-1'),
            (2, 'fas fa-folder', ?, 'category-2'),
            (3, 'fas fa-folder', ?, 'category-3')
    ", [
        Status::ACTIVE->value,
        Status::ACTIVE->value,
        Status::ACTIVE->value,
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title)
        VALUES
            (1, 'category', 'english', 'Category 1'),
            (2, 'category', 'english', 'Category 2'),
            (3, 'category', 'english', 'Category 3')
    ")->execute();

    // Insert pages: some in categories 1 and 2, some without category (0), some in category 3
    $now = time();
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES
            (1, 1, 'page-in-cat-1', ?, ?, ?, ?, 10, 5, ?, 0, 0, 0),
            (2, 1, 'page-in-cat-2', ?, ?, ?, ?, 15, 3, ?, 0, 0, 0),
            (0, 1, 'page-no-cat', ?, ?, ?, ?, 20, 2, ?, 0, 0, 0),
            (3, 1, 'page-in-cat-3', ?, ?, ?, ?, 25, 1, ?, 0, 0, 0)
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
        INSERT INTO members (id_member, real_name, member_name)
        VALUES (1, 'Test Author', 'test_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES
            (1, 'page', 'english', 'Page in Category 1', 'Content 1', 'Description 1'),
            (2, 'page', 'english', 'Page in Category 2', 'Content 2', 'Description 2'),
            (3, 'page', 'english', 'Page without Category', 'Content 3', 'Description 3'),
            (4, 'page', 'english', 'Page in Category 3', 'Content 4', 'Description 4')
    ")->execute();

    $this->query->init([
        'status'               => Status::ACTIVE->value,
        'deleted_at'           => 0,
        'entry_type'           => EntryType::DEFAULT->name(),
        'current_time'         => time(),
        'selected_categories'  => [1, 2],
        'permissions'          => Permission::ALL->value,
    ]);

    $this->query->prepareParams(0, 10);

    $result = $this->query->getRawData();

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(2)
        ->and($data)->toHaveKey(0)
        ->and($data)->toHaveKey(1)
        ->and($data[0]['title'])->toBe('Page in Category 1')
        ->and($data[1]['title'])->toBe('Page in Category 2');
});

it('applies base conditions correctly', function () {
    $this->query->init([
        'status'               => Status::ACTIVE->value,
        'deleted_at'           => 0,
        'entry_type'           => EntryType::DEFAULT->name(),
        'current_time'         => time(),
        'selected_categories'  => [1, 2],
        'permissions'          => Permission::ALL->value,
    ]);

    $select = $this->sql->select()->from('lp_pages');
    $accessor = new ReflectionAccessor($this->query);
    $accessor->callProtectedMethod('applyBaseConditions', [$select]);

    $state = $select->getRawState();

    // Check WHERE conditions - it's a Where object, not an array
    $where = $state['where'];

    expect($where)->toBeInstanceOf(Where::class);
});
