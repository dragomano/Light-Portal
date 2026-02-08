<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Database\PortalSql;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\AbstractIndexRepository;
use LightPortal\Repositories\TagIndexRepository;
use Tests\PortalTable;
use Tests\TestAdapterFactory;

arch()
    ->expect(TagIndexRepository::class)
    ->toExtend(AbstractIndexRepository::class);

beforeEach(function () {
    Lang::$txt['guest_title'] = 'Guest';

    User::$me = new User(1);
    User::$me->language = 'english';
    User::$me->groups = [];
    User::$me->is_admin = false;
    User::$me->is_guest = false;

    Config::$language = 'english';

    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::TAGS->value)->execute();
    $adapter->query(PortalTable::PAGES->value)->execute();
    $adapter->query(PortalTable::PAGE_TAG->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();

    $this->sql = new PortalSql($adapter);

    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->dispatcher->shouldReceive('dispatch')->andReturnNull()->byDefault();

    $this->repository = new TagIndexRepository($this->sql, $this->dispatcher);
});

it('returns tag list with frequency', function () {
    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_tags (tag_id, slug, icon, status)
        VALUES (1, 'tag-one', '', ?)
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            page_id, category_id, author_id, slug, type, entry_type, permissions,
            status, num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (1, 0, 1, 'page-one', 'bbc', ?, ?, ?, 0, 0, ?, 0, 0, 0)
    ", [EntryType::DEFAULT->name(), Permission::ALL->value, Status::ACTIVE->value, $now - 5]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_page_tag (page_id, tag_id)
        VALUES (1, 1)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title)
        VALUES (1, 'tag', 'english', 'Tag One')
    ")->execute();

    $result = $this->repository->getAll(0, 10, 'tag.tag_id DESC');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey(1)
        ->and($result[1]['slug'])->toBe('tag-one')
        ->and($result[1]['frequency'])->toBe(1)
        ->and($result[1]['link'])->toContain(';sa=tags;id=1')
        ->and($result[1]['title'])->toBe('Tag One');
});

it('returns total count', function () {
    $now = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_tags (tag_id, slug, icon, status)
        VALUES (1, 'tag-one', '', ?)
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            page_id, category_id, author_id, slug, type, entry_type, permissions,
            status, num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (1, 0, 1, 'page-one', 'bbc', ?, ?, ?, 0, 0, ?, 0, 0, 0)
    ", [EntryType::DEFAULT->name(), Permission::ALL->value, Status::ACTIVE->value, $now - 5]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_page_tag (page_id, tag_id)
        VALUES (1, 1)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title)
        VALUES (1, 'tag', 'english', 'Tag One')
    ")->execute();

    $count = $this->repository->getTotalCount();

    expect($count)->toBe(1);
});
