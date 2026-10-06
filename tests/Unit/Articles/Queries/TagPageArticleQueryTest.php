<?php

declare(strict_types=1);

use Bugo\Compat\User;
use LightPortal\Articles\Queries\TagPageArticleQuery;
use LightPortal\Database\PortalSql;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;
use Tests\PortalTable;
use Tests\ReflectionAccessor;
use Tests\Table;
use Tests\TestAdapterFactory;

beforeEach(function() {
    User::$me           = new User(1);
    User::$me->language = 'english';
    User::$me->groups   = [0];

    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::CATEGORIES->value)->execute();
    $adapter->query(PortalTable::COMMENTS->value)->execute();
    $adapter->query(PortalTable::PAGE_TAG->value)->execute();
    $adapter->query(PortalTable::PAGES->value)->execute();
    $adapter->query(PortalTable::PARAMS->value)->execute();
    $adapter->query(PortalTable::TAGS->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();
    $adapter->query(Table::MEMBERS->value)->execute();

    $pdo = $adapter->getDriver()->getConnection()->getResource();
    $pdo->sqliteCreateFunction('GREATEST', function ($a, $b) {
        return max($a, $b);
    });

    $this->sql = new PortalSql($adapter);

    $this->eventsMock = mock(EventDispatcherInterface::class);
    $this->eventsMock->shouldReceive('dispatch')->andReturn(null)->byDefault();

    $this->request = mock(RequestInterface::class);
    $this->request->shouldReceive('get')->with('id')->andReturn(null)->byDefault();
    AppMockRegistry::set(RequestInterface::class, $this->request);

    $this->query = new TagPageArticleQuery($this->sql, $this->eventsMock);
});

it('sets the current tag from the request and resets selected categories', function () {
    $this->request->shouldReceive('get')->with('id')->andReturn('3');

    $this->query->init([
        'status'              => Status::ACTIVE->value,
        'deleted_at'          => 0,
        'entry_type'          => EntryType::DEFAULT->value,
        'current_time'        => time(),
        'selected_categories' => [1, 2],
        'permissions'         => Permission::ALL->value,
    ]);

    $params = (new ReflectionAccessor($this->query))->getProperty('params');

    expect($params['current_tag'])->toBe(3)
        ->and($params['selected_categories'])->toBe([]);
});

it('keeps the current tag empty when the request has no tag id', function () {
    $this->query->init([
        'status'              => Status::ACTIVE->value,
        'deleted_at'          => 0,
        'entry_type'          => EntryType::DEFAULT->value,
        'current_time'        => time(),
        'selected_categories' => [1],
        'permissions'         => Permission::ALL->value,
    ]);

    $params = (new ReflectionAccessor($this->query))->getProperty('params');

    expect($params['current_tag'])->toBe(0)
        ->and($params['selected_categories'])->toBe([]);
});

it('returns pages filtered by the current tag', function () {
    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES
            (0, 1, 'tagged-page', ?, ?, ?, ?, 10, 0, ?, 0, 0, 0),
            (0, 1, 'untagged-page', ?, ?, ?, ?, 20, 0, ?, 0, 0, 0)
    ", [
        ContentType::BBC->value,
        EntryType::DEFAULT->value,
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
        ContentType::BBC->value,
        EntryType::DEFAULT->value,
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_tags (tag_id, slug, status) VALUES (1, 'news', ?), (2, 'old', ?)
    ", [Status::ACTIVE->value, Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ '
        INSERT INTO lp_page_tag (page_id, tag_id) VALUES (1, 1), (2, 2)
    ')->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES
            (1, 'page', 'english', 'Tagged Page', 'Content', 'Description'),
            (2, 'page', 'english', 'Untagged Page', 'Content', 'Description')
    ")->execute();

    $this->request->shouldReceive('get')->with('id')->andReturn('1');

    $this->query->init([
        'status'              => Status::ACTIVE->value,
        'deleted_at'          => 0,
        'entry_type'          => EntryType::DEFAULT->value,
        'current_time'        => time(),
        'selected_categories' => [],
        'permissions'         => Permission::ALL->value,
    ]);

    $this->query->prepareParams(0, 10);

    $data = iterator_to_array($this->query->getRawData());

    expect($data)->toHaveCount(1)
        ->and($data[0]['title'])->toBe('Tagged Page')
        ->and($this->query->getTotalCount())->toBe(1);
});

it('returns all pages when no tag is selected', function () {
    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES
            (0, 1, 'tagged-page', ?, ?, ?, ?, 10, 0, ?, 0, 0, 0),
            (0, 1, 'untagged-page', ?, ?, ?, ?, 20, 0, ?, 0, 0, 0)
    ", [
        ContentType::BBC->value,
        EntryType::DEFAULT->value,
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
        ContentType::BBC->value,
        EntryType::DEFAULT->value,
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_tags (tag_id, slug, status) VALUES (1, 'news', ?)
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ '
        INSERT INTO lp_page_tag (page_id, tag_id) VALUES (1, 1)
    ')->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES
            (1, 'page', 'english', 'Tagged Page', 'Content', 'Description'),
            (2, 'page', 'english', 'Untagged Page', 'Content', 'Description')
    ")->execute();

    $this->query->init([
        'status'              => Status::ACTIVE->value,
        'deleted_at'          => 0,
        'entry_type'          => EntryType::DEFAULT->value,
        'current_time'        => time(),
        'selected_categories' => [],
        'permissions'         => Permission::ALL->value,
    ]);

    $this->query->prepareParams(0, 10);

    $data = iterator_to_array($this->query->getRawData());

    expect($data)->toHaveCount(2)
        ->and($this->query->getTotalCount())->toBe(2);
});

it('excludes pages with inactive tags', function () {
    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (0, 1, 'tagged-page', ?, ?, ?, ?, 10, 0, ?, 0, 0, 0)
    ", [
        ContentType::BBC->value,
        EntryType::DEFAULT->value,
        Permission::ALL->value,
        Status::ACTIVE->value,
        $now,
    ]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_tags (tag_id, slug, status) VALUES (1, 'hidden', ?)
    ", [Status::INACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ '
        INSERT INTO lp_page_tag (page_id, tag_id) VALUES (1, 1)
    ')->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES (1, 'page', 'english', 'Tagged Page', 'Content', 'Description')
    ")->execute();

    $this->request->shouldReceive('get')->with('id')->andReturn('1');

    $this->query->init([
        'status'              => Status::ACTIVE->value,
        'deleted_at'          => 0,
        'entry_type'          => EntryType::DEFAULT->value,
        'current_time'        => time(),
        'selected_categories' => [],
        'permissions'         => Permission::ALL->value,
    ]);

    $this->query->prepareParams(0, 10);

    expect(iterator_to_array($this->query->getRawData()))->toBeEmpty()
        ->and($this->query->getTotalCount())->toBe(0);
});
