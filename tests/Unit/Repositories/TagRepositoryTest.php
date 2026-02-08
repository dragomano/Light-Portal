<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\Compat\User;
use LightPortal\Database\PortalSql;
use LightPortal\Enums\Status;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\AbstractRepository;
use LightPortal\Repositories\TagRepository;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\Utils\CacheInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use LightPortal\Utils\SessionInterface;
use Tests\AppMockRegistry;
use Tests\PortalTable;
use Tests\TestAdapterFactory;

arch()
    ->expect(TagRepository::class)
    ->toExtend(AbstractRepository::class)
    ->toImplement(TagRepositoryInterface::class);

beforeEach(function () {
    Lang::$txt['guest_title'] = 'Guest';

    User::$me = new User(1);
    User::$me->language = 'english';

    Config::$language = 'english';

    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::TAGS->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();
    $adapter->query(PortalTable::PAGE_TAG->value)->execute();

    $this->sql = new PortalSql($adapter);

    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->dispatcher->shouldReceive('dispatch')->andReturnNull()->byDefault();

    $this->repository = new TagRepository($this->sql, $this->dispatcher);
});

it('can get all tags with translations', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_tags (tag_id, slug, icon, status)
        VALUES (1, 'tag-one', '', ?)
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title)
        VALUES (1, 'tag', 'english', 'Tag One')
    ")->execute();

    $result = $this->repository->getAll(0, 10, 'tag_id DESC');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey(1)
        ->and($result[1]['id'])->toBe(1)
        ->and($result[1]['slug'])->toBe('tag-one')
        ->and($result[1]['title'])->toBe('Tag One');
});

it('applies list filter to active tags with translations', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_tags (tag_id, slug, icon, status)
        VALUES
            (1, 'tag-active', '', ?),
            (2, 'tag-inactive', '', ?)
    ", [Status::ACTIVE->value, Status::INACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title)
        VALUES
            (1, 'tag', 'english', 'Active Tag'),
            (2, 'tag', 'english', '')
    ")->execute();

    $result = $this->repository->getAll(0, 10, 'tag_id DESC', 'list');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(1)
        ->and($result)->toHaveKey(1);
});

it('can get total count with conditions', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_tags (tag_id, slug, icon, status)
        VALUES
            (1, 'tag-one', '', ?),
            (2, 'tag-two', '', ?)
    ", [Status::ACTIVE->value, Status::INACTIVE->value]);

    $count = $this->repository->getTotalCount('', ['status = ?' => Status::ACTIVE->value]);

    expect($count)->toBe(1);
});

it('can get data by id', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_tags (tag_id, slug, icon, status)
        VALUES (1, 'tag-one', 'fa-icon', ?)
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title)
        VALUES (1, 'tag', 'english', 'Tag One')
    ")->execute();

    $result = $this->repository->getData(1);

    expect($result)->toBeArray()
        ->and($result['id'])->toBe(1)
        ->and($result['slug'])->toBe('tag-one')
        ->and($result['icon'])->toBe('fa-icon')
        ->and($result['title'])->toBe('Tag One');
});

it('returns empty array when tag id is zero', function () {
    $result = $this->repository->getData(0);

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

it('returns early when post errors are present', function () {
    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->never();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $sessionMock = mock(SessionInterface::class);
    $sessionMock->shouldReceive('withKey')->never();
    AppMockRegistry::set(SessionInterface::class, $sessionMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->never();
    AppMockRegistry::set(ResponseInterface::class, $responseMock);

    Utils::$context['post_errors'] = ['title' => 'error'];

    $this->repository->setData(1);

    expect(true)->toBeTrue();
});

it('returns early when no save actions are present', function () {
    $requestMock = mock(RequestInterface::class);
    $requestMock->shouldReceive('hasNot')->with(['save', 'save_exit'])->andReturn(true);
    AppMockRegistry::set(RequestInterface::class, $requestMock);

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->never();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $sessionMock = mock(SessionInterface::class);
    $sessionMock->shouldReceive('withKey')->never();
    AppMockRegistry::set(SessionInterface::class, $sessionMock);

    $responseMock = mock(ResponseInterface::class);
    $responseMock->shouldReceive('redirect')->never();
    AppMockRegistry::set(ResponseInterface::class, $responseMock);

    $this->repository->setData(1);

    expect(true)->toBeTrue();
});

it('removes tags and related data', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_tags (tag_id, slug, icon, status)
        VALUES (1, 'tag-one', '', ?)
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title)
        VALUES (1, 'tag', 'english', 'Tag One')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_page_tag (page_id, tag_id)
        VALUES (1, 1)
    ")->execute();

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $sessionMock = mock(SessionInterface::class);
    $sessionMock->shouldReceive('withKey')->with('lp')->andReturnSelf();
    $sessionMock->shouldReceive('free')->with('active_tags')->once();
    AppMockRegistry::set(SessionInterface::class, $sessionMock);

    $this->repository->remove([1]);

    $tagCount = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_tags')
        ->execute()
        ->current()['count'];
    $translationCount = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_translations')
        ->execute()
        ->current()['count'];
    $pageTagCount = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_page_tag')
        ->execute()
        ->current()['count'];

    expect($tagCount)->toBe(0)
        ->and($translationCount)->toBe(0)
        ->and($pageTagCount)->toBe(0);
});

it('returns early when removing empty list', function () {
    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->never();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $sessionMock = mock(SessionInterface::class);
    $sessionMock->shouldReceive('withKey')->never();
    AppMockRegistry::set(SessionInterface::class, $sessionMock);

    $this->repository->remove([]);

    $count = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_tags')
        ->execute()
        ->current()['count'];

    expect($count)->toBe(0);
});
