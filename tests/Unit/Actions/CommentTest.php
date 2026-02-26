<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use LightPortal\Actions\Comment;
use LightPortal\Actions\ActionInterface;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\CommentRepositoryInterface;
use LightPortal\Utils\NotifierInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\ResponseInterface;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

arch()
    ->expect(Comment::class)
    ->toImplement(ActionInterface::class);

afterEach(function () {
    AppMockRegistry::clear();
});

it('returns early when api is empty', function () {
    $GLOBALS['context'] = [
        'lp_page' => [
            'slug' => 'test-page',
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    $request = mock(RequestInterface::class);
    $request->shouldReceive('isEmpty')->with('api')->andReturn(true);
    AppMockRegistry::set(RequestInterface::class, $request);

    $response = mock(ResponseInterface::class);
    $response->shouldNotReceive('exit');
    AppMockRegistry::set(ResponseInterface::class, $response);

    $repository = mock(CommentRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();
    $notifier = mock(NotifierInterface::class);

    $action = new Comment($repository, $dispatcher, $notifier);
    $action->show();
});

it('sets page slug correctly', function () {
    $GLOBALS['context'] = [
        'lp_page' => [
            'slug' => 'initial-page',
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    $repository = mock(CommentRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $notifier = mock(NotifierInterface::class);

    $action = new Comment($repository, $dispatcher, $notifier);
    $result = $action->setPageSlug('new-page');

    expect($result)->toBeInstanceOf(Comment::class);

    $accessor = new ReflectionAccessor($action);
    expect($accessor->getProperty('pageSlug'))->toBe('new-page');
});

it('replaces member tags to markdown', function () {
    Config::$scripturl = 'https://example.com/index.php';

    $GLOBALS['context'] = [
        'lp_page' => [
            'slug' => 'test-page',
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    $repository = mock(CommentRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();
    $notifier = mock(NotifierInterface::class);

    $action = new Comment($repository, $dispatcher, $notifier);
    $accessor = new ReflectionAccessor($action);

    $result = $accessor->callMethod('replaceMemberTagsToMarkdown', ['Hello [member=5]John[/member]']);

    expect($result)->toBe('Hello [@John](https://example.com/index.php?action=profile;u=5)');
});

it('replaces multiple member tags to markdown', function () {
    Config::$scripturl = 'https://example.com/index.php';

    $GLOBALS['context'] = [
        'lp_page' => [
            'slug' => 'test-page',
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    $repository = mock(CommentRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();
    $notifier = mock(NotifierInterface::class);

    $action = new Comment($repository, $dispatcher, $notifier);
    $accessor = new ReflectionAccessor($action);

    $result = $accessor->callMethod('replaceMemberTagsToMarkdown', ['[member=1]User1[/member] and [member=2]User2[/member]']);

    expect($result)->toBe('[@User1](https://example.com/index.php?action=profile;u=1) and [@User2](https://example.com/index.php?action=profile;u=2)');
});

it('returns original text when no member tags', function () {
    Config::$scripturl = 'https://example.com/index.php';

    $GLOBALS['context'] = [
        'lp_page' => [
            'slug' => 'test-page',
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    $repository = mock(CommentRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();
    $notifier = mock(NotifierInterface::class);

    $action = new Comment($repository, $dispatcher, $notifier);
    $accessor = new ReflectionAccessor($action);

    $result = $accessor->callMethod('replaceMemberTagsToMarkdown', ['Hello world without tags']);

    expect($result)->toBe('Hello world without tags');
});

it('builds comment tree', function () {
    $GLOBALS['context'] = [
        'lp_page' => [
            'slug' => 'test-page',
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    $repository = mock(CommentRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();
    $notifier = mock(NotifierInterface::class);

    $action = new Comment($repository, $dispatcher, $notifier);
    $accessor = new ReflectionAccessor($action);

    $data = [
        1 => ['id' => 1, 'parent_id' => 0],
        2 => ['id' => 2, 'parent_id' => 1],
    ];

    $tree = $accessor->callMethod('getTree', [$data]);

    expect($tree)->toHaveKey(1)
        ->and($tree[1]['replies'])->toHaveKey(2);
});

it('builds comment tree with nested replies', function () {
    $GLOBALS['context'] = [
        'lp_page' => [
            'slug' => 'test-page',
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    $repository = mock(CommentRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();
    $notifier = mock(NotifierInterface::class);

    $action = new Comment($repository, $dispatcher, $notifier);
    $accessor = new ReflectionAccessor($action);

    $data = [
        1 => ['id' => 1, 'parent_id' => 0, 'replies' => []],
        2 => ['id' => 2, 'parent_id' => 1, 'replies' => []],
        3 => ['id' => 3, 'parent_id' => 2, 'replies' => []],
    ];

    $tree = $accessor->callMethod('getTree', [$data]);

    expect($tree)->toHaveKey(1)
        ->and($tree[1]['replies'])->toHaveKey(2)
        ->and($tree[1]['replies'][2]['replies'])->toHaveKey(3);
});


it('returns page index url for non-frontpage', function () {
    $GLOBALS['context'] = [
        'lp_page' => [
            'slug' => 'my-page',
        ],
        'canonical_url' => 'https://example.com/my-page',
    ];
    Utils::$context = &$GLOBALS['context'];

    $repository = mock(CommentRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();
    $notifier = mock(NotifierInterface::class);

    $action = new Comment($repository, $dispatcher, $notifier);
    $action->setPageSlug('my-page');

    $accessor = new ReflectionAccessor($action);
    $url = $accessor->callMethod('getPageIndexUrl');

    expect($url)->toBe('https://example.com/my-page');
});


it('mention members does nothing when mentions disabled', function () {
    $GLOBALS['context'] = [
        'lp_page' => [
            'slug' => 'test-page',
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    $repository = mock(CommentRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();
    $notifier = mock(NotifierInterface::class);
    $notifier->shouldNotReceive('notify');

    $action = new Comment($repository, $dispatcher, $notifier);
    $accessor = new ReflectionAccessor($action);

    $options = ['item' => 1, 'author_id' => 1];
    $verifiedMembers = [];

    $accessor->callMethod('mentionMembers', [$verifiedMembers, $options]);
});
