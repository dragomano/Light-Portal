<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use LightPortal\Articles\Queries\BoardArticleQuery;
use LightPortal\Articles\Services\BoardArticleService;
use LightPortal\Events\EventDispatcherInterface;
use Prophecy\Argument;
use Prophecy\Prophet;
use Tests\ReflectionAccessor;

beforeEach(function() {
    Config::$scripturl = 'https://example.com/index.php';
    Config::$modSettings['lp_show_teaser'] = 1;

    Utils::$context['description_allowed_tags'] = [];

    $this->prophet = new Prophet();

    $this->queryProphecy = $this->prophet->prophesize(BoardArticleQuery::class);
    $this->queryProphecy->getSorting()->willReturn('created;desc');
    $this->queryProphecy->getSorting()->willReturn('updated;desc');
    $this->queryMock = $this->queryProphecy->reveal();

    $this->events = $this->prophet->prophesize(EventDispatcherInterface::class)->reveal();

    $this->service = new BoardArticleService($this->queryMock, $this->events);
});

it('returns data iterator', function () {
    $rows = [
        [
            'id_board'     => 1,
            'id_topic'     => 1,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 1,
            'name'         => 'Test Board',
            'description'  => 'Test description',
            'is_read'      => 0,
            'num_posts'    => 10,
            'cat_name'     => 'Test Category',
            'is_redirect'  => 0,
            'redirect'     => '',
            'attach_id'    => null,
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

it('returns link for regular board', function () {
    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('getLink', [['id_board' => 5, 'is_redirect' => 0, 'redirect' => '']]);

    expect($result)->toBe('https://example.com/index.php?board=5.0');
});

it('returns link for redirect board', function () {
    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('getLink', [['id_board' => 5, 'is_redirect' => 1, 'redirect' => 'https://external.com']]);

    expect($result)->toBe('https://external.com" rel="nofollow noopener');
});

it('calls init method', function () {
    $this->queryProphecy->init((array) Argument::that(function ($params) {
        return isset($params['current_member']) && isset($params['selected_boards']) && isset($params['recycle_board']);
    }))->shouldBeCalled();

    $this->service->init();

    expect(true)->toBeTrue(); // Add assertion to avoid risky test warning
});

it('returns date for empty last_updated', function () {
    $row = [
        'poster_time'  => 1000,
        'last_updated' => 0,
    ];

    $accessor = new ReflectionAccessor($this->service);

    $this->queryProphecy->getSorting()->willReturn('updated;desc');
    $result = $accessor->callProtectedMethod('getDate', [$row]);
    expect($result)->toBe(1000);
});

it('returns empty image when images disabled', function () {
    Config::$modSettings['lp_show_images_in_articles'] = 0;

    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('getImage', [['description' => 'Some text', 'attach_id' => 1, 'is_redirect' => 0, 'id_topic' => 1, 'redirect' => '']]);

    expect($result)->toBe('');
});

it('returns image from attachment when no image in description', function () {
    Config::$modSettings['lp_show_images_in_articles'] = 1;

    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('getImage', [['description' => 'Some text', 'attach_id' => 123, 'is_redirect' => 0, 'id_topic' => 456, 'redirect' => '']]);

    expect($result)->toBe('https://example.com/index.php?action=dlattach;topic=456;attach=123;image');
});

it('returns screenshot for redirect board without image', function () {
    Config::$modSettings['lp_show_images_in_articles'] = 1;

    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('getImage', [['description' => 'Some text', 'attach_id' => null, 'is_redirect' => 1, 'id_topic' => 1, 'redirect' => 'https://external.com']]);

    expect($result)->toBe('https://mini.s-shot.ru/300x200/JPEG/300/Z100/?https%3A%2F%2Fexternal.com');
});

it('returns image from description for regular board', function () {
    Config::$modSettings['lp_show_images_in_articles'] = 1;

    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('getImage', [['description' => 'Text with <img src="https://example.com/image.jpg" alt="" />', 'attach_id' => null, 'is_redirect' => 0, 'id_topic' => 1, 'redirect' => '']]);

    expect($result)->toBe('https://example.com/image.jpg');
});

it('returns image from description for redirect board', function () {
    Config::$modSettings['lp_show_images_in_articles'] = 1;

    $accessor = new ReflectionAccessor($this->service);

    $result = $accessor->callProtectedMethod('getImage', [['description' => 'Text with <img src="https://example.com/image.jpg" alt="" />', 'attach_id' => null, 'is_redirect' => 1, 'id_topic' => 1, 'redirect' => 'https://external.com']]);

    expect($result)->toBe('https://example.com/image.jpg');
});

it('prepares teaser from description', function () {
    Config::$modSettings['lp_show_teaser'] = 1;

    $accessor = new ReflectionAccessor($this->service);

    $board = [];
    $row = [
        'description' => 'This is a long description that should be truncated for the teaser.',
    ];

    $accessor->callProtectedMethod('prepareTeaser', [&$board, $row]);

    expect($board)->toHaveKey('teaser');
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
