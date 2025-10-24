<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use LightPortal\Articles\Queries\PageArticleQuery;
use LightPortal\Articles\Services\PageArticleService;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\PageRepositoryInterface;
use Prophecy\Prophet;
use Tests\ReflectionAccessor;

beforeEach(function() {
    $this->prophet = new Prophet();

    $this->queryProphecy = $this->prophet->prophesize(PageArticleQuery::class);
    $this->queryProphecy->getSorting()->willReturn('created;desc');

    $this->queryMock = $this->queryProphecy->reveal();

    $this->events = $this->prophet->prophesize(EventDispatcherInterface::class)->reveal();
    $this->pageRepositoryProphecy = $this->prophet->prophesize(PageRepositoryInterface::class);
    $this->pageRepository = $this->pageRepositoryProphecy->reveal();

    $this->service = new PageArticleService($this->queryMock, $this->events, $this->pageRepository);
});

it('can get sorting options', function () {
    $options = $this->service->getSortingOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('created;desc')
        ->and($options)->toHaveKey('title')
        ->and($options)->toHaveKey('num_views;desc');
});

it('returns section data', function () {
    $row = [
        'cat_icon'    => 'fas fa-folder',
        'category_id' => 1,
        'cat_title'   => 'Test Category',
    ];

    $accessor = new ReflectionAccessor($this->service);
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

    $accessor = new ReflectionAccessor($this->service);
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

    $this->queryProphecy->getSorting()->willReturn('last_comment;desc');
    $accessor = new ReflectionAccessor($this->service);
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

    $accessor = new ReflectionAccessor($this->service);

    $this->queryProphecy->getSorting()->willReturn('created;desc');
    $result = $accessor->callProtectedMethod('getDate', [$row]);
    expect($result)->toBe(1000);

    $this->queryProphecy->getSorting()->willReturn('last_comment;desc');
    $result = $accessor->callProtectedMethod('getDate', [$row]);
    expect($result)->toBe(3000);

    $this->queryProphecy->getSorting()->willReturn('updated;desc');
    $result = $accessor->callProtectedMethod('getDate', [$row]);
    expect($result)->toBe(2000);
});

it('returns views data', function () {
    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('getViewsData', [['num_views' => 42]]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('num')
        ->and($result)->toHaveKey('title')
        ->and($result)->toHaveKey('after')
        ->and($result['num'])->toBe(42);
});

it('returns replies data structure', function () {
    Config::$modSettings['lp_comment_block'] = 'default';

    $accessor = new ReflectionAccessor($this->service);

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

    $accessor = new ReflectionAccessor($this->service);

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

    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('getImage', [$row]);
    expect($result)->toBeString();

    Config::$modSettings['lp_show_images_in_articles'] = 0;

    $result = $accessor->callProtectedMethod('getImage', [$row]);
    expect($result)->toBe('');
});

it('checks edit permissions structure', function () {
    User::$me = new User(1);
    User::$me->is_admin = true;

    $accessor = new ReflectionAccessor($this->service);
    $result = $accessor->callProtectedMethod('canEdit', [['author_id' => 2]]);

    expect($result)->toBeTrue();
});

it('gets edit link', function () {
    $accessor = new ReflectionAccessor($this->service);
    $result = $accessor->callProtectedMethod('getEditLink', [['page_id' => 42]]);

    expect($result)->toBeString()
        ->and($result)->toContain('action=admin;area=lp_pages;sa=edit;id=42');
});

it('prepares teaser with last comment content', function () {
    $this->queryProphecy->getSorting()->willReturn('last_comment;desc');
    $accessor = new ReflectionAccessor($this->service);

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
    $this->queryProphecy->getSorting()->willReturn('created;desc');
    $accessor = new ReflectionAccessor($this->service);

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
    $this->queryProphecy->getSorting()->willReturn('created;desc');
    $accessor = new ReflectionAccessor($this->service);

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
    $this->queryProphecy->getSorting()->willReturn('created;desc');
    $accessor = new ReflectionAccessor($this->service);

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

it('skips teaser when teasers disabled', function () {
    Config::$modSettings['lp_show_teaser'] = 0;

    $accessor = new ReflectionAccessor($this->service);

    $board = [];
    $row = [
        'description' => 'This is a description.',
    ];

    $accessor->callProtectedMethod('prepareTeaser', [&$board, $row]);

    expect($board)->not->toHaveKey('teaser');
});

it('returns data iterator', function () {
    $rows = [
        [
            'page_id'      => 1,
            'author_id'    => 1,
            'author_name'  => 'Test Author',
            'date'         => 1000,
            'created_at'   => 1000,
            'updated_at'   => 2000,
            'comment_date' => 3000,
            'num_views'    => 10,
            'num_comments' => 5,
            'category_id'  => 0,
            'content'      => 'Test content',
            'description'  => 'Test description',
            'title'        => 'Test Page',
            'type'         => 'bbc',
            'slug'         => 'test-page',
            'cat_icon'     => '',
            'cat_title'    => '',
            'comment_author_id' => 0,
            'comment_author_name' => '',
        ]
    ];

    $this->queryProphecy->setSorting('created;desc')->shouldBeCalled();
    $this->queryProphecy->prepareParams(0, 10)->shouldBeCalled();
    $this->queryProphecy->getRawData()->willReturn($rows);
    $data = iterator_to_array($this->service->getData(0, 10, 'created;desc'));

    expect($data)->toBeArray()->and($data)->toHaveKey(1);
});

it('returns total count', function () {
    $this->queryProphecy->getTotalCount()->willReturn(5);

    $count = $this->service->getTotalCount();

    expect($count)->toBe(5);
});

it('prepares tags for pages', function () {
    $pages = [
        1 => ['title' => 'Page 1'],
        2 => ['title' => 'Page 2'],
        3 => ['title' => 'Page 3'],
    ];

    $tag1 = [
        'tag_id' => 10,
        'slug'   => 'tag-1',
        'icon'   => 'fas fa-tag',
        'href'   => '/tags/id=10',
        'name'   => 'Tag 1',
    ];
    $tag2 = [
        'tag_id' => 20,
        'slug'   => 'tag-2',
        'icon'   => 'fas fa-tag',
        'href'   => '/tags/id=20',
        'name'   => 'Tag 2',
    ];
    $tag3 = [
        'tag_id' => 30,
        'slug'   => 'tag-3',
        'icon'   => 'fas fa-tag',
        'href'   => '/tags/id=30',
        'name'   => 'Tag 3',
    ];

    $this->pageRepositoryProphecy->fetchTags([1, 2, 3])
        ->willReturn((function () use ($tag1, $tag2, $tag3) {
            yield 1 => $tag1;
            yield 1 => $tag3;
            yield 2 => $tag2;
        })());

    $this->service->prepareTags($pages);

    expect($pages[1]['tags'])->toHaveCount(2)
        ->and($pages[1]['tags'])->toContain($tag1)
        ->and($pages[1]['tags'])->toContain($tag3)
        ->and($pages[2]['tags'])->toHaveCount(1)
        ->and($pages[2]['tags'])->toContain($tag2)
        ->and($pages[3])->not()->toHaveKey('tags');
});

it('skips prepare tags when pages array is empty', function () {
    $pages = [];

    $this->pageRepositoryProphecy->fetchTags([])->shouldNotBeCalled();

    $accessor = new ReflectionAccessor($this->service);
    $accessor->callProtectedMethod('prepareTags', [&$pages]);

    expect($pages)->toBeEmpty();
});
