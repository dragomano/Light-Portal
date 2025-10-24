<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use LightPortal\Articles\Queries\TopicArticleQuery;
use LightPortal\Articles\Services\TopicArticleService;
use LightPortal\Events\EventDispatcherInterface;
use Prophecy\Prophet;
use Tests\ReflectionAccessor;

beforeEach(function() {
    $this->prophet = new Prophet();

    $this->queryProphecy = $this->prophet->prophesize(TopicArticleQuery::class);
    $this->queryProphecy->getSorting()->willReturn('created;desc');
    $this->queryProphecy->getSorting()->willReturn('updated;desc');
    $this->queryMock = $this->queryProphecy->reveal();

    $this->events = $this->prophet->prophesize(EventDispatcherInterface::class)->reveal();

    $this->service = new TopicArticleService($this->queryMock, $this->events);
});

it('returns data iterator', function () {
    $rows = [
        [
            'id_topic'         => 1,
            'id_board'         => 1,
            'name'             => 'Test Board',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'poster_time'      => 1000,
            'date'             => 2000,
            'last_msg_time'    => 3000,
            'subject'          => 'Test Topic',
            'is_sticky'        => 0,
            'num_views'        => 42,
            'num_replies'      => 7,
            'body'             => 'Test content',
            'id_attach'        => null,
            'new_from'         => 1,
            'id_msg_modified'  => 2,
            'id_first_msg'     => 1,
            'last_body'        => 'Last comment content',
            'smileys_enabled'  => 1,
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

it('returns section data', function () {
    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('getSectionData', [[
        'name'     => 'Test Board',
        'id_board' => 1,
    ]]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('name')
        ->and($result)->toHaveKey('link')
        ->and($result['name'])->toBe('Test Board');
});

it('returns author data for topic author', function () {
    $accessor = new ReflectionAccessor($this->service);

    $this->queryProphecy->getSorting()->willReturn('created;desc');
    $result = $accessor->callProtectedMethod('getAuthorData', [[
        'id_member'        => 123,
        'poster_name'      => 'Test Author',
        'last_poster_id'   => 456,
        'last_poster_name' => 'Last Poster',
        'num_replies'      => 0,
    ]]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('id')
        ->and($result)->toHaveKey('link')
        ->and($result)->toHaveKey('name')
        ->and($result['id'])->toBe(123)
        ->and($result['name'])->toBe('Test Author');
});

it('returns author data for last poster when sorting by last_comment', function () {
    $accessor = new ReflectionAccessor($this->service);

    $this->queryProphecy->getSorting()->willReturn('last_comment;desc');
    $result = $accessor->callProtectedMethod('getAuthorData', [[
        'id_member'        => 123,
        'poster_name'      => 'Topic Author',
        'last_poster_id'   => 456,
        'last_poster_name' => 'Last Poster',
        'num_replies'      => 5,
    ]]);

    expect($result['id'])->toBe(456)
        ->and($result['name'])->toBe('Last Poster');
});

it('returns date based on sorting type', function () {
    $accessor = new ReflectionAccessor($this->service);

    $this->queryProphecy->getSorting()->willReturn('created;desc');
    $result = $accessor->callProtectedMethod('getDate', [[
        'poster_time'   => 1000,
        'date'          => 2000,
        'last_msg_time' => 3000,
    ]]);
    expect($result)->toBe(1000);

    $this->queryProphecy->getSorting()->willReturn('last_comment;desc');
    $result = $accessor->callProtectedMethod('getDate', [[
        'poster_time'   => 1000,
        'date'          => 2000,
        'last_msg_time' => 3000,
    ]]);
    expect($result)->toBe(3000);

    $this->queryProphecy->getSorting()->willReturn('updated;desc');
    $result = $accessor->callProtectedMethod('getDate', [[
        'poster_time'   => 1000,
        'date'          => 2000,
        'last_msg_time' => 3000,
    ]]);
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
    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('getRepliesData', [['num_replies' => 7]]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('num')
        ->and($result)->toHaveKey('title')
        ->and($result)->toHaveKey('after')
        ->and($result['num'])->toBe(7);
});

it('checks if topic is new', function () {
    User::$me = new User(1);
    User::$me->last_login = 500;

    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('isNew', [[
        'poster_time'     => 1000,
        'id_member'       => 2,
        'new_from'        => 1,
        'id_msg_modified' => 2,
        'last_poster_id'  => 2,
    ]]);
    expect($result)->toBeTrue();

    User::$me->is_guest = true;

    $result = $accessor->callProtectedMethod('isNew', [[
        'poster_time'     => 1000,
        'id_member'       => 1,
        'new_from'        => 1,
        'id_msg_modified' => 2,
        'last_poster_id'  => 1,
    ]]);
    expect($result)->toBeFalse();

});

it('gets image from content and attachments', function () {
    Config::$modSettings['lp_show_images_in_articles'] = 1;

    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('getImage', [['body' => 'Some content with [img]https://example.com/image.jpg[/img]', 'id_topic' => 1]]);
    expect($result)->toBeString();

    $result = $accessor->callProtectedMethod('getImage', [['body' => 'Some content', 'id_attach' => 1, 'id_topic' => 1]]);
    expect($result)->toBeTruthy();

    Config::$modSettings['lp_show_images_in_articles'] = 0;
    $result = $accessor->callProtectedMethod('getImage', [['body' => 'Some content', 'id_attach' => 1, 'id_topic' => 1]]);
    expect($result)->toBe('');
});

it('checks edit permissions structure', function () {
    User::$me = new User(1);
    User::$me->is_admin = true;

    $accessor = new ReflectionAccessor($this->service);
    $result = $accessor->callProtectedMethod('canEdit', [['id_member' => 2]]);

    expect($result)->toBeTrue();
});

it('gets edit link', function () {
    $accessor = new ReflectionAccessor($this->service);
    $result = $accessor->callProtectedMethod('getEditLink', [['id_first_msg' => 42, 'id_topic' => 1]]);

    expect($result)->toBeString()
        ->and($result)->toContain('action=post;msg=42;topic=1.0');
});

it('prepares teaser with last comment content', function () {
    $accessor = new ReflectionAccessor($this->service);

    $this->queryProphecy->getSorting()->willReturn('last_comment;desc');
    Config::$modSettings['lp_show_teaser'] = 1;

    $topic = [];
    $row = [
        'last_body'       => 'Last comment content',
        'body'            => 'Original content',
        'num_replies'     => 5,
        'smileys_enabled' => 1,
        'id_first_msg'    => 1,
    ];

    $accessor->callProtectedMethod('prepareTeaser', [&$topic, $row]);

    expect($topic)->toHaveKey('teaser');
});

it('prepares teaser with original content when no last comment', function () {
    $accessor = new ReflectionAccessor($this->service);

    $this->queryProphecy->getSorting()->willReturn('created;desc');
    Config::$modSettings['lp_show_teaser'] = 1;

    $topic = [];
    $row = [
        'body'            => 'Original content',
        'num_replies'     => 0,
        'smileys_enabled' => 1,
        'id_first_msg'    => 1,
    ];

    $accessor->callProtectedMethod('prepareTeaser', [&$topic, $row]);

    expect($topic)->toHaveKey('teaser');
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
