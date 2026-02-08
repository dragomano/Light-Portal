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
