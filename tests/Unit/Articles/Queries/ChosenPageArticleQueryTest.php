<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use LightPortal\Articles\Queries\ChosenPageArticleQuery;
use LightPortal\Database\PortalSql;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Events\EventDispatcherInterface;
use Prophecy\Prophet;
use Tests\PortalTable;
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

    $this->prophet = new Prophet();

    $prophecy = $this->prophet->prophesize(EventDispatcherInterface::class);
    $this->eventsMock = $prophecy->reveal();

    $this->query = new ChosenPageArticleQuery($this->sql, $this->eventsMock);
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
        INSERT INTO members (id_member, real_name, member_name)
        VALUES (1, 'Test Author', 'test_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES
            (1, 'page', 'english', 'Test Page 1', 'Test content 1', 'Test description 1'),
            (2, 'page', 'english', 'Test Page 2', 'Test content 2', 'Test description 2'),
            (3, 'page', 'english', 'Test Page 3', 'Test content 3', 'Test description 3')
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
        INSERT INTO members (id_member, real_name, member_name)
        VALUES (1, 'Test Author', 'test_author'), (2, 'Test Author 2', 'test_author2')
    ")->execute();

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

    expect($count)->toBe(2);
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
        INSERT INTO members (id_member, real_name, member_name)
        VALUES (1, 'Test Author', 'test_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES
            (1, 'page', 'english', 'Page 1', 'Content 1', 'Description 1'),
            (2, 'page', 'english', 'Page 2', 'Content 2', 'Description 2'),
            (3, 'page', 'english', 'Page 3', 'Content 3', 'Description 3'),
            (4, 'page', 'english', 'Page 4', 'Content 4', 'Description 4')
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

    expect($data)->toHaveCount(2)
        ->and($data)->toHaveKey(0)
        ->and($data)->toHaveKey(1)
        ->and($data[0]['title'])->toBe('Page 2')
        ->and($data[1]['title'])->toBe('Page 4');
});

it('returns empty when no selected pages for getRawData', function () {
    Config::$modSettings['lp_frontpage_pages'] = '';

    $this->query->init([
        'status'               => Status::ACTIVE->value,
        'deleted_at'           => 0,
        'entry_type'           => EntryType::DEFAULT->name(),
        'current_time'         => time(),
        'selected_categories'  => [],
        'permissions'          => Permission::ALL->value,
    ]);

    $result = $this->query->getRawData();

    $data = iterator_to_array($result);

    expect($data)->toBeEmpty();
});

it('returns zero count when no selected pages for getTotalCount', function () {
    Config::$modSettings['lp_frontpage_pages'] = '';

    $this->query->init([
        'status'               => Status::ACTIVE->value,
        'deleted_at'           => 0,
        'entry_type'           => EntryType::DEFAULT->name(),
        'current_time'         => time(),
        'selected_categories'  => [],
        'permissions'          => Permission::ALL->value,
    ]);

    $count = $this->query->getTotalCount();

    expect($count)->toBe(0);
});
