<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Database\PortalSql;
use LightPortal\Enums\Status;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\AbstractRepository;
use LightPortal\Repositories\CategoryRepository;
use LightPortal\Repositories\CategoryRepositoryInterface;
use LightPortal\Utils\CacheInterface;
use LightPortal\Utils\SessionInterface;
use Tests\AppMockRegistry;
use Tests\PortalTable;
use Tests\TestAdapterFactory;

arch()
    ->expect(CategoryRepository::class)
    ->toExtend(AbstractRepository::class)
    ->toImplement(CategoryRepositoryInterface::class);

beforeEach(function () {
    Lang::$txt['guest_title'] = 'Guest';

    User::$me = new User(1);
    User::$me->language = 'english';

    Config::$language = 'english';

    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::CATEGORIES->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();
    $adapter->query(PortalTable::PAGES->value)->execute();

    $this->sql = new PortalSql($adapter);

    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->dispatcher->shouldReceive('dispatch')->andReturnNull()->byDefault();

    $this->repository = new CategoryRepository($this->sql, $this->dispatcher);
});

it('can get all categories with translations', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_categories (category_id, parent_id, slug, icon, priority, status)
        VALUES (1, 0, 'news', '', 1, ?)
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, description)
        VALUES (1, 'category', 'english', 'News', 'News category')
    ")->execute();

    $result = $this->repository->getAll(0, 10, 'category_id DESC');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey(1)
        ->and($result[1]['slug'])->toBe('news')
        ->and($result[1]['title'])->toBe('News')
        ->and($result[1]['description'])->toBe('News category');
});

it('applies list filter to active categories with translations', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_categories (category_id, parent_id, slug, icon, priority, status)
        VALUES
            (1, 0, 'active', '', 1, ?),
            (2, 0, 'inactive', '', 2, ?)
    ", [Status::ACTIVE->value, Status::INACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title)
        VALUES
            (1, 'category', 'english', 'Active Category'),
            (2, 'category', 'english', '')
    ")->execute();

    $result = $this->repository->getAll(0, 10, 'category_id DESC', 'list');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(1)
        ->and($result)->toHaveKey(1);
});

it('can get total count with conditions', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_categories (category_id, parent_id, slug, icon, priority, status)
        VALUES
            (1, 0, 'one', '', 1, ?),
            (2, 0, 'two', '', 2, ?)
    ", [Status::ACTIVE->value, Status::INACTIVE->value]);

    $count = $this->repository->getTotalCount('', ['status = ?' => Status::ACTIVE->value]);

    expect($count)->toBe(1);
});

it('can get category data by id', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_categories (category_id, parent_id, slug, icon, priority, status)
        VALUES (1, 0, 'news', 'fa-icon', 1, ?)
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, description)
        VALUES (1, 'category', 'english', 'News', 'News category')
    ")->execute();

    $result = $this->repository->getData(1);

    expect($result)->toBeArray()
        ->and($result['id'])->toBe(1)
        ->and($result['slug'])->toBe('news')
        ->and($result['icon'])->toBe('fa-icon')
        ->and($result['title'])->toBe('News')
        ->and($result['description'])->toBe('News category');
});

it('returns empty array when category id is zero', function () {
    $result = $this->repository->getData(0);

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

it('updates priorities and clears cache', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_categories (category_id, parent_id, slug, icon, priority, status)
        VALUES
            (1, 0, 'one', '', 1, ?),
            (2, 0, 'two', '', 2, ?)
    ", [Status::ACTIVE->value, Status::ACTIVE->value]);

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $this->repository->updatePriority([
        2 => 1,
        1 => 2,
    ]);

    $priorities = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT category_id, priority FROM lp_categories ORDER BY category_id')
        ->execute();

    $priorities = iterator_to_array($priorities);

    expect($priorities[0]['priority'])->toBe(2)
        ->and($priorities[1]['priority'])->toBe(1);
});

it('returns category ids ordered by priority', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_categories (category_id, parent_id, slug, icon, priority, status)
        VALUES
            (1, 0, 'news', '', 1, ?),
            (2, 0, 'articles', '', 0, ?),
            (3, 0, 'blog', '', 2, ?)
    ", array_fill(0, 3, Status::ACTIVE->value));

    expect($this->repository->getIds())->toBe([2, 1, 3]);
});

it('renumbers the categories after a removal', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_categories (category_id, parent_id, slug, icon, priority, status)
        VALUES
            (1, 0, 'news', '', 0, ?),
            (2, 0, 'articles', '', 1, ?),
            (3, 0, 'blog', '', 2, ?)
    ", array_fill(0, 3, Status::ACTIVE->value));

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->andReturnNull();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $sessionMock = mock(SessionInterface::class);
    $sessionMock->shouldReceive('withKey')->with('lp')->andReturnSelf();
    $sessionMock->shouldReceive('free')->with('active_categories')->andReturnNull();
    AppMockRegistry::set(SessionInterface::class, $sessionMock);

    $this->repository->remove([1]);

    $rows = iterator_to_array($this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT category_id, priority FROM lp_categories ORDER BY category_id')
        ->execute());

    expect(count($rows))->toBe(2)
        ->and($rows[0]['priority'])->toBe(0)
        ->and($rows[1]['priority'])->toBe(1);
});
