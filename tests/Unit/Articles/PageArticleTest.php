<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
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
    $this->article = new PageArticle($this->sql);

    $mockRepository = Mockery::mock(PageRepositoryInterface::class);
    $mockRepository->shouldReceive('fetchTags')->andReturn(new ArrayIterator([]));
    AppMockRegistry::set(PageRepositoryInterface::class, $mockRepository);
});

it('can initialize with real database', function () {
    $this->article->init();

    expect($this->article)->toBeInstanceOf(PageArticle::class);
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

    $this->article->init();
    $count = $this->article->getTotalCount();

    expect($count)->toBe(3);
});

it('can prepare tags with real database', function () {
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

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_tags (slug, icon, status)
        VALUES ('test-tag', 'fas fa-tag', ?)
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_page_tag (page_id, tag_id)
        VALUES (1, 1)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title)
        VALUES (1, 'tag', 'english', 'Test Tag')
    ")->execute();

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

it('can get sorting options', function () {
    $options = $this->article->getSortingOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('created;desc')
        ->and($options)->toHaveKey('title')
        ->and($options)->toHaveKey('num_views;desc');
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

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1)
        ->and($data[1]['section']['name'])->toBe('Test Category')
        ->and($data[1]['title'])->toBe('Category Page');
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

    $this->article->init();
    $result = $this->article->getData(0, 10, 'last_comment;desc');

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1)
        ->and($data[1]['replies']['num'])->toBe(1);
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

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toBeEmpty();
});

it('returns section data', function () {
    $row = [
        'cat_icon'    => 'fas fa-folder',
        'category_id' => 1,
        'cat_title'   => 'Test Category',
    ];

    $accessor = new ReflectionAccessor($this->article);
    $result = $accessor->callProtectedMethod('getSectionData', [$row]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('icon')
        ->and($result)->toHaveKey('name')
        ->and($result)->toHaveKey('link')
        ->and($result['icon'])->toBe('<i class="fas fa-folder" aria-hidden="true"></i> ')
        ->and($result['name'])->toBe('Test Category');
});

it('returns author data for page author', function () {
    $row = [
        'author_id'           => 123,
        'author_name'         => 'Test Author',
        'num_comments'        => 0,
        'comment_author_id'   => 0,
        'comment_author_name' => '',
    ];

    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('sorting', 'created;desc');
    $result = $accessor->callProtectedMethod('getAuthorData', [$row]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('id')
        ->and($result)->toHaveKey('link')
        ->and($result)->toHaveKey('name')
        ->and($result['id'])->toBe(123)
        ->and($result['name'])->toBe('Test Author');
});

it('returns author data for comment author when sorting by last_comment', function () {
    $row = [
        'author_id'           => 123,
        'author_name'         => 'Page Author',
        'comment_author_id'   => 456,
        'comment_author_name' => 'Comment Author',
        'num_comments'        => 5,
    ];

    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('sorting', 'last_comment;desc');
    $result = $accessor->callProtectedMethod('getAuthorData', [$row]);

    expect($result['id'])->toBe(456)
        ->and($result['name'])->toBe('Comment Author');
});

it('returns date based on sorting type', function () {
    $row = [
        'created_at'   => 1000,
        'date'         => 2000,
        'comment_date' => 3000,
    ];

    $accessor = new ReflectionAccessor($this->article);

    $accessor->setProtectedProperty('sorting', 'created;desc');
    $result = $accessor->callProtectedMethod('getDate', [$row]);
    expect($result)->toBe(1000);

    $accessor->setProtectedProperty('sorting', 'last_comment;desc');
    $result = $accessor->callProtectedMethod('getDate', [$row]);
    expect($result)->toBe(3000);

    $accessor->setProtectedProperty('sorting', 'updated;desc');
    $result = $accessor->callProtectedMethod('getDate', [$row]);
    expect($result)->toBe(2000);
});

it('returns views data', function () {
    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('getViewsData', [['num_views' => 42]]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('num')
        ->and($result)->toHaveKey('title')
        ->and($result)->toHaveKey('after')
        ->and($result['num'])->toBe(42);
});

it('returns replies data structure', function () {
    Config::$modSettings['lp_comment_block'] = 'default';

    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('getRepliesData', [['num_comments' => 7]]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('num')
        ->and($result)->toHaveKey('title')
        ->and($result)->toHaveKey('after')
        ->and($result['num'])->toBe(7);
});

it('checks if page is new', function () {
    User::$me = new User(1);
    User::$me->last_login = 500;

    $row = [
        'date'      => 1000,
        'author_id' => 2,
    ];

    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('isNew', [$row]);
    expect($result)->toBeTrue();

    $row['author_id'] = 1;
    $result = $accessor->callProtectedMethod('isNew', [$row]);
    expect($result)->toBeFalse();

    $row['date'] = 400;
    $row['author_id'] = 2;
    $result = $accessor->callProtectedMethod('isNew', [$row]);
    expect($result)->toBeFalse();
});

it('gets image from content', function () {
    Config::$modSettings['lp_show_images_in_articles'] = 1;

    $row = ['content' => 'Some content with <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAB..." alt="test"> image'];

    $accessor = new ReflectionAccessor($this->article);

    $result = $accessor->callProtectedMethod('getImage', [$row]);
    expect($result)->toBeString();

    Config::$modSettings['lp_show_images_in_articles'] = 0;

    $result = $accessor->callProtectedMethod('getImage', [$row]);
    expect($result)->toBe('');
});

it('checks edit permissions structure', function () {
    User::$me = new User(1);
    User::$me->is_admin = true;

    $accessor = new ReflectionAccessor($this->article);
    $result = $accessor->callProtectedMethod('canEdit', [['author_id' => 2]]);

    expect($result)->toBeTrue();
});

it('gets edit link', function () {
    $accessor = new ReflectionAccessor($this->article);
    $result = $accessor->callProtectedMethod('getEditLink', [['page_id' => 42]]);

    expect($result)->toBeString()
        ->and($result)->toContain('action=admin;area=lp_pages;sa=edit;id=42');
});

it('prepares teaser with last comment content', function () {
    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('sorting', 'last_comment;desc');

    Config::$modSettings['lp_show_teaser'] = 1;

    $page = [];
    $row = [
        'description'     => 'Test description',
        'content'         => 'Test content',
        'num_comments'    => 5,
        'comment_message' => 'Test comment message',
    ];

    $accessor->callProtectedMethod('prepareTeaser', [&$page, $row]);

    expect($page)->toHaveKey('teaser');
});

it('prepares teaser with description when available', function () {
    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('sorting', 'created;desc');

    Config::$modSettings['lp_show_teaser'] = 1;

    $page = [];
    $row = [
        'description'     => 'Test description',
        'content'         => 'Test content',
        'num_comments'    => 0,
        'comment_message' => '',
    ];

    $accessor->callProtectedMethod('prepareTeaser', [&$page, $row]);

    expect($page)->toHaveKey('teaser');
});

it('prepares teaser with content when no description', function () {
    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('sorting', 'created;desc');

    Config::$modSettings['lp_show_teaser'] = 1;

    $page = [];
    $row = [
        'description'     => '',
        'content'         => 'Test content',
        'num_comments'    => 0,
        'comment_message' => '',
    ];

    $accessor->callProtectedMethod('prepareTeaser', [&$page, $row]);

    expect($page)->toHaveKey('teaser');
});

it('prepares teaser structure', function () {
    $accessor = new ReflectionAccessor($this->article);
    $accessor->setProtectedProperty('sorting', 'created;desc');

    Config::$modSettings['lp_show_teaser'] = 1;

    $page = [];
    $row = [
        'description'     => 'Test description',
        'content'         => 'Test content',
        'num_comments'    => 0,
        'comment_message' => '',
    ];

    $accessor->callProtectedMethod('prepareTeaser', [&$page, $row]);

    expect($page)->toBeArray();
});

it('processes rows with non-empty title in getData with real database', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (0, 1, 'test-page', ?, ?, ?, ?, 10, 0, ?, 0, 0, 0)
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
        VALUES (1, 'page', 'english', 'Test Page Title', 'Test content', 'Test description')
    ")->execute();

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);
    expect($data)->toHaveCount(1)
        ->and($data[1])->toHaveKey('title')
        ->and($data[1]['title'])->toBe('Test Page Title');
});

it('skips tags with empty title in prepareTags with real database', function () {
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

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_tags (slug, icon, status)
        VALUES ('test-tag', 'fas fa-tag', ?)
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_page_tag (page_id, tag_id)
        VALUES (1, 1)
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title)
        VALUES (1, 'tag', 'english', '')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES (1, 'page', 'english', 'Test Page', 'Test content', 'Test description')
    ")->execute();

    $pages = [
        1 => ['id' => 1],
    ];

    $this->article->init();

    $this->article->prepareTags($pages);

    expect($pages[1])->not->toHaveKey('tags');
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

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(2)
        ->and($data)->toHaveKey(1)
        ->and($data)->toHaveKey(2)
        ->and($data[1]['title'])->toBe('Page in Category 1')
        ->and($data[2]['title'])->toBe('Page in Category 2');
});

it('handles empty selected categories with category 0', function () {
    Config::$modSettings['lp_frontpage_categories'] = '';
    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    // Insert page without category (category_id=0)
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (
            category_id, author_id, slug, type, entry_type, permissions, status,
            num_views, num_comments, created_at, updated_at, deleted_at, last_comment_id
        ) VALUES (0, 1, 'page-no-cat', ?, ?, ?, ?, 10, 0, ?, 0, 0, 0)
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
        VALUES (1, 'page', 'english', 'Page without Category', 'Test content', 'Test description')
    ")->execute();

    $this->article->init();
    $result = $this->article->getData(0, 10, 'created;desc');

    $data = iterator_to_array($result);

    expect($data)->toHaveCount(1)
        ->and($data)->toHaveKey(1)
        ->and($data[1]['title'])->toBe('Page without Category');
});
