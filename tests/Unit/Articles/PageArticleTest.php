<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Laminas\Db\Adapter\Driver\ResultInterface;
use LightPortal\Articles\PageArticle;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\PortalResultInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Events\EventManager;
use LightPortal\Utils\CacheInterface;
use Tests\ReflectionAccessor;

it('initializes parameters with all_pages frontpage mode', function () {
    $userMock = Mockery::mock(User::class);
    $userMock->language = 'english';
    $userMock->groups = [1, 2, 3];
    $userMock->is_admin = false;
    $userMock->id = 1;
    $userMock->shouldReceive('allowedTo')->andReturn(false);

    Config::$language = 'english';
    Config::$modSettings['lp_frontpage_mode'] = 'all_pages';

    $eventMock = Mockery::mock(EventManager::class);
    $eventMock->shouldReceive('dispatch')->andReturn(null);

    $GLOBALS['event_manager_mock'] = $eventMock;

    $portalSqlMock = Mockery::mock(PortalSqlInterface::class);
    $portalSqlMock
        ->shouldReceive('select')
        ->andReturn(Mockery::mock()->shouldIgnoreMissing());
    $portalSqlMock
        ->shouldReceive('execute')
        ->andReturn(Mockery::mock()->shouldReceive('current')->andReturn(['id_member' => []]));

    $article = new PageArticle($portalSqlMock);
    $article->init();

    $accessorArticle = new ReflectionAccessor($article);
    $params = $accessorArticle->getProtectedProperty('params');

    expect($params['selected_categories'])->toBe([0]);
});

it('initializes parameters and dispatches hook', function () {
    $settingMock = Mockery::mock('SettingMock');
    $settingMock->shouldReceive('get')->with('lp_frontpage_categories', 'array', [])->andReturn([]);
    $settingMock->shouldReceive('isFrontpageMode')->with('all_pages')->andReturn(false);

    $GLOBALS['SettingMock'] = $settingMock;

    $userMock = Mockery::mock(User::class);
    $userMock->language = 'russian';
    $userMock->groups = [1, 2, 3];
    $userMock->is_admin = false;
    $userMock->id = 1;
    $userMock->shouldReceive('allowedTo')->andReturn(false);

    Config::$language = 'english';

    $portalSqlMock = Mockery::mock(PortalSqlInterface::class);
    $portalSqlMock
        ->shouldReceive('select')->andReturn(Mockery::mock()->shouldIgnoreMissing());
    $portalSqlMock
        ->shouldReceive('execute')->andReturn(Mockery::mock()->shouldReceive('current')->andReturn(['id_member' => []]));
    $portalSqlMock->shouldReceive('getTransaction')->andReturn(Mockery::mock()->shouldIgnoreMissing());

    $cacheMock = Mockery::mock(CacheInterface::class);
    $cacheMock->shouldReceive('remember')->with('board_moderators', Mockery::on(function () {
        $portalSqlMock = Mockery::mock(PortalSqlInterface::class);
        $portalSqlMock->shouldReceive('select')->andReturn(Mockery::mock()->shouldIgnoreMissing());
        $portalSqlMock->shouldReceive('execute')->andReturn(Mockery::mock()->shouldReceive('current')->andReturn(['id_member' => []]));

        $GLOBALS['app_mocks'][PortalSqlInterface::class] = $portalSqlMock;

        return true;
    }))->andReturn([]);

    $GLOBALS['app_mocks'] = [
        PortalSqlInterface::class => $portalSqlMock,
        CacheInterface::class => $cacheMock,
    ];
    $portalSqlMock->shouldReceive('getTransaction')->andReturn(Mockery::mock()->shouldIgnoreMissing());

    $cacheMock = Mockery::mock(CacheInterface::class);
    $cacheMock->shouldReceive('remember')->with('board_moderators', Mockery::on(function () {
        $portalSqlMock = Mockery::mock(PortalSqlInterface::class);
        $portalSqlMock->shouldReceive('select')->andReturn(Mockery::mock()->shouldIgnoreMissing());
        $portalSqlMock
            ->shouldReceive('execute')
            ->andReturn(Mockery::mock()->shouldReceive('current')->andReturn(['id_member' => []]));

        $GLOBALS['app_mocks'][PortalSqlInterface::class] = $portalSqlMock;

        return true;
    }))->andReturn([]);

    $GLOBALS['app_mocks'] = [
        PortalSqlInterface::class => $portalSqlMock,
        CacheInterface::class     => $cacheMock,
    ];
    $article = new PageArticle($portalSqlMock);

    $eventMock = Mockery::mock(EventManager::class);
    $eventMock->shouldReceive('dispatch')
        ->andReturn(null);

    $GLOBALS['event_manager_mock'] = $eventMock;

    $article->init();

    $paramsReflection = new ReflectionProperty(PageArticle::class, 'params');
    $params = $paramsReflection->getValue($article);

    $ordersReflection = new ReflectionProperty(PageArticle::class, 'orders');
    $orders = $ordersReflection->getValue($article);

    expect($params)->toHaveKey('lang')
        ->and($params)->toHaveKey('selected_categories')
        ->and($orders)->toHaveKey('created;desc');
});

it('returns sorting options', function () {
    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));

    $options = $article->getSortingOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('created;desc')
        ->and($options)->toHaveKey('title');
});

it('skips rows with empty title in getData', closure: function () {
    $selectMock = Mockery::mock(PortalSelect::class);
    $selectMock->shouldReceive('from')->andReturnSelf();
    $selectMock->shouldReceive('join')->andReturnSelf();
    $selectMock->shouldReceive('columns')->andReturnSelf();
    $selectMock->shouldReceive('order')->andReturnSelf();
    $selectMock->shouldReceive('limit')->andReturnSelf();
    $selectMock->shouldReceive('offset')->andReturnSelf();
    $selectMock->shouldReceive('where')->andReturnSelf();
    $selectMock->shouldReceive('getRawState')->andReturn([]);

    $portalSqlMock = Mockery::mock(PortalSqlInterface::class);
    $portalSqlMock->shouldReceive('select')->andReturn($selectMock);

    $resultMock = Mockery::mock(PortalResultInterface::class);
    $testData = [
        [
            'page_id'             => 1,
            'title'               => '',
            'slug'                => 'test-page',
            'author_id'           => 1,
            'author_name'         => 'Test Author',
            'created_at'          => time(),
            'updated_at'          => time(),
            'num_views'           => 10,
            'num_comments'        => 0,
            'category_id'         => 1,
            'cat_title'           => 'Test Category',
            'cat_icon'            => 'fas fa-folder',
            'comment_date'        => null,
            'comment_author_id'   => 0,
            'comment_author_name' => '',
            'type'                => 'bbc',
            'content'             => 'Test content',
            'description'         => 'Test description',
        ]
    ];

    $resultMock->shouldReceive('current')->andReturn($testData[0], null);
    $resultMock->shouldReceive('valid')->andReturn(true, false);
    $resultMock->shouldReceive('next');
    $resultMock->shouldReceive('key')->andReturn(0);
    $resultMock->shouldReceive('rewind');

    $portalSqlMock->shouldReceive('execute')->andReturn($resultMock);

    $article = new PageArticle($portalSqlMock);
    $article->init();

    $reflection = new ReflectionAccessor($article);

    $result = $reflection->callProtectedMethod('getData', [0, 10, 'created;desc']);

    expect(iterator_to_array($result))->toBeEmpty();
});

it('processes rows with non-empty title in getData', function () {
    Config::$modSettings['avatar_url'] = '';

    $userLoadedMock = Mockery::mock();
    $userLoadedMock->shouldReceive('format')->andReturn([
        'name'   => 'Test Author',
        'avatar' => ['image' => 'avatar_image'],
    ]);

    $accessor = new ReflectionAccessor(new User());
    $accessor->setProtectedProperty('loaded', [1 => $userLoadedMock]);

    $selectMock = Mockery::mock(PortalSelect::class);
    $selectMock->shouldReceive('from')->andReturnSelf();
    $selectMock->shouldReceive('join')->andReturnSelf();
    $selectMock->shouldReceive('columns')->andReturnSelf();
    $selectMock->shouldReceive('order')->andReturnSelf();
    $selectMock->shouldReceive('limit')->andReturnSelf();
    $selectMock->shouldReceive('offset')->andReturnSelf();
    $selectMock->shouldReceive('where')->andReturnSelf();
    $selectMock->shouldReceive('getRawState')->andReturn([]);

    $portalSqlMock = Mockery::mock(PortalSqlInterface::class);
    $portalSqlMock->shouldReceive('select')->andReturn($selectMock);

    $resultMock = Mockery::mock(PortalResultInterface::class);
    $testData = [
        [
            'page_id'             => 1,
            'title'               => 'Test Page Title',
            'slug'                => 'test-page',
            'author_id'           => 1,
            'author_name'         => 'Test Author',
            'created_at'          => time(),
            'updated_at'          => time(),
            'date'                => time(),
            'num_views'           => 10,
            'num_comments'        => 0,
            'category_id'         => 1,
            'cat_title'           => 'Test Category',
            'cat_icon'            => 'fas fa-folder',
            'comment_date'        => null,
            'comment_author_id'   => 0,
            'comment_author_name' => '',
            'type'                => 'bbc',
            'content'             => 'Test content',
            'description'         => 'Test description',
        ]
    ];

    $resultMock->shouldReceive('current')->andReturn($testData[0], null);
    $resultMock->shouldReceive('valid')->andReturn(true, false);
    $resultMock->shouldReceive('next');
    $resultMock->shouldReceive('key')->andReturn(0);
    $resultMock->shouldReceive('rewind');

    $portalSqlMock->shouldReceive('execute')->andReturn($resultMock);

    $article = new PageArticle($portalSqlMock);
    $article->init();

    $reflection = new ReflectionAccessor($article);

    $result = $reflection->callProtectedMethod('getData', [0, 10, 'created;desc']);

    $data = iterator_to_array($result);
    expect($data)->toHaveCount(1)
        ->and($data[1])->toHaveKey('title')
        ->and($data[1]['title'])->toBe('Test Page Title');
});

it('retrieves data with mocked Db', function () {
    Config::$modSettings['avatar_url'] = '';

    $userLoadedMock = Mockery::mock();
    $userLoadedMock->shouldReceive('format')->andReturn([
        'name'   => 'Test Author',
        'avatar' => ['image' => 'avatar_image'],
    ]);

    $accessor = new ReflectionAccessor(new User());
    $accessor->setProtectedProperty('loaded', [1 => $userLoadedMock]);

    $selectMock = Mockery::mock(PortalSelect::class);
    $selectMock->shouldReceive('from')->andReturnSelf();
    $selectMock->shouldReceive('join')->andReturnSelf();
    $selectMock->shouldReceive('columns')->andReturnSelf();
    $selectMock->shouldReceive('order')->andReturnSelf();
    $selectMock->shouldReceive('limit')->andReturnSelf();
    $selectMock->shouldReceive('offset')->andReturnSelf();
    $selectMock->shouldReceive('where')->andReturnSelf();
    $selectMock->shouldReceive('group')->andReturnSelf();
    $selectMock->shouldReceive('having')->andReturnSelf();
    $selectMock->shouldReceive('quantifier')->andReturnSelf();
    $selectMock->shouldReceive('combine')->andReturnSelf();
    $selectMock->shouldReceive('getRawState')->andReturn([]);

    $resultMock = Mockery::mock(PortalResultInterface::class);
    $testData = [
        [
            'page_id'             => 1,
            'title'               => 'Test Page',
            'slug'                => 'test-page',
            'author_id'           => 1,
            'author_name'         => 'Test Author',
            'created_at'          => time(),
            'updated_at'          => time(),
            'date'                => time(),
            'num_views'           => 10,
            'num_comments'        => 0,
            'category_id'         => 1,
            'cat_title'           => 'Test Category',
            'cat_icon'            => 'fas fa-folder',
            'comment_date'        => null,
            'comment_author_id'   => 0,
            'comment_author_name' => '',
            'type'                => 'bbc',
            'content'             => 'Test content',
            'description'         => 'Test description',
        ]
    ];

    $resultMock->shouldReceive('current')->andReturn($testData[0], null);
    $resultMock->shouldReceive('valid')->andReturn(true, false);
    $resultMock->shouldReceive('next');
    $resultMock->shouldReceive('key')->andReturn(0);
    $resultMock->shouldReceive('rewind');

    $portalSqlMock = Mockery::mock(PortalSqlInterface::class);
    $portalSqlMock->shouldReceive('select')->andReturn($selectMock);
    $portalSqlMock->shouldReceive('execute')->andReturn($resultMock);

    $article = new PageArticle($portalSqlMock);
    $article->init();

    $accessor = new ReflectionAccessor($article);
    $result = $accessor->callProtectedMethod('getData', [0, 10, 'created;desc']);

    $data = array_map(function ($value) {
        return $value;
    }, iterator_to_array($result));

    expect($data)->toBeArray()
        ->and($data)->toHaveKey(1)
        ->and($data[1])->toHaveKey('id')
        ->and($data[1])->toHaveKey('title')
        ->and($data[1])->toHaveKey('author')
        ->and($data[1]['title'])->toBe('Test Page');
});

it('returns total count with mocked Db', function () {
    $portalSqlMock = Mockery::mock(PortalSqlInterface::class);
    $selectMock = Mockery::mock(PortalSelect::class);
    $portalSqlMock->shouldReceive('select')->andReturn($selectMock);

    $selectMock->shouldReceive('from')->andReturnSelf();
    $selectMock->shouldReceive('columns')->andReturnSelf();
    $selectMock->shouldReceive('join')->andReturnSelf();
    $selectMock->shouldReceive('where')->andReturnSelf();
    $selectMock->shouldReceive('order')->andReturnSelf();
    $selectMock->shouldReceive('limit')->andReturnSelf();
    $selectMock->shouldReceive('offset')->andReturnSelf();

    $whereMock1 = Mockery::mock();
    $whereMock1->shouldReceive('in')->andReturnSelf();
    $whereMock2 = Mockery::mock();
    $whereMock2->shouldReceive('in')->andReturnSelf();

    $selectMock->shouldReceive('where')->andReturn($whereMock1, $whereMock2);
    $resultMock = Mockery::mock(PortalResultInterface::class);
    $resultMock->shouldReceive('current')->andReturn(['count' => 42]);
    $portalSqlMock->shouldReceive('execute')->andReturn($resultMock);
    $article = new PageArticle($portalSqlMock);
    $article->init();

    $count = $article->getTotalCount();

    expect($count)->toBe(42);
});

it('skips empty pages array in prepareTags', function () {
    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));
    $accessor = new ReflectionAccessor($article);

    $pages = [];
    $accessor->callProtectedMethod('prepareTags', [$pages]);

    expect($pages)->toBeEmpty();
});

it('skips tags with empty title in prepareTags', function () {
    $selectMock = Mockery::mock(PortalSelect::class);

    $selectMock->shouldReceive('from')->andReturnSelf();
    $selectMock->shouldReceive('join')->andReturnSelf();
    $selectMock->shouldReceive('where')->andReturnSelf();
    $selectMock->shouldReceive('order')->andReturnSelf();
    $selectMock->shouldReceive('addTranslationJoins')->andReturnSelf();

    $resultMock = Mockery::mock(PortalResultInterface::class);
    $resultMock->shouldReceive('current')->andReturn([
        'page_id' => 1,
        'tag_id'  => 1,
        'slug'    => 'test-tag',
        'icon'    => 'fas fa-tag',
        'title'   => '',
    ]);
    $resultMock->shouldReceive('valid')->andReturn(true, false);
    $resultMock->shouldReceive('next')->once();
    $resultMock->shouldReceive('key')->andReturn(0);
    $resultMock->shouldReceive('rewind')->once();

    $portalSqlMock = Mockery::mock(PortalSqlInterface::class);

    $portalSqlMock->shouldReceive('select')->andReturn($selectMock);
    $portalSqlMock->shouldReceive('execute')->andReturn($resultMock);
    $article = new PageArticle($portalSqlMock);

    $paramsReflection = new ReflectionProperty(PageArticle::class, 'params');
    $paramsReflection->setValue($article, [
        'lang'                => 'english',
        'fallback_lang'       => 'english',
        'status'              => 1,
        'entry_type'          => 'page',
        'current_time'        => time(),
        'permissions'         => [1,2,3],
        'selected_categories' => [0],
    ]);

    $pages = [
        1 => ['id' => 1],
    ];

    $article->prepareTags($pages);

    expect($pages[1])->not->toHaveKey('tags');
});

it('prepares tags with mocked Db', function () {
    $selectMock = Mockery::mock(PortalSelect::class);
    $selectMock->shouldReceive('from')->andReturnSelf();
    $selectMock->shouldReceive('join')->andReturnSelf();
    $selectMock->shouldReceive('where')->andReturnSelf();
    $selectMock->shouldReceive('order')->andReturnSelf();
    $selectMock->shouldReceive('from')->andReturnSelf();
    $selectMock->shouldReceive('join')->andReturnSelf();
    $selectMock->shouldReceive('where')->andReturnSelf();
    $selectMock->shouldReceive('order')->andReturnSelf();
    $selectMock->shouldReceive('from')->andReturnSelf();
    $selectMock->shouldReceive('join')->andReturnSelf();
    $selectMock->shouldReceive('where')->andReturnSelf();
    $selectMock->shouldReceive('order')->andReturnSelf();

    $resultMock = Mockery::mock(PortalResultInterface::class);
    $resultMock->shouldReceive('current')->andReturn([
        'page_id' => 1,
        'tag_id'  => 1,
        'slug'    => 'test-tag',
        'icon'    => 'fas fa-tag',
        'title'   => 'Test Tag',
    ]);
    $resultMock->shouldReceive('valid')->andReturn(true, false);
    $resultMock->shouldReceive('next')->andReturn(null);
    $resultMock->shouldReceive('key')->andReturn(0);
    $resultMock->shouldReceive('rewind')->andReturn(null);

    $portalSqlMock = Mockery::mock(PortalSqlInterface::class);
    $portalSqlMock->shouldReceive('select')->andReturn($selectMock);
    $portalSqlMock->shouldReceive('execute')->andReturn($resultMock);
    $portalSqlMock->shouldReceive('execute')->andReturn($resultMock);

    Config::$modSettings['avatar_url'] = '';

    $pages = [
        1 => ['id' => 1],
        2 => ['id' => 2],
    ];

    $article  = new PageArticle($portalSqlMock);
    $accessor = new ReflectionAccessor($article);
    $accessor->setProtectedProperty('params', [
        'lang'                => 'english',
        'fallback_lang'       => 'english',
        'status'              => 1,
        'entry_type'          => 'page',
        'current_time'        => time(),
        'permissions'         => [1,2,3],
        'selected_categories' => [0],
    ]);
    $accessor->callProtectedMethod('prepareTags', [&$pages]);

    expect($pages[1])->toHaveKey('tags')
        ->and($pages[1]['tags'])->toBeArray();
});

it('returns section data', function () {
    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));
    $accessor = new ReflectionAccessor($article);

    $row = [
        'cat_icon'    => 'fas fa-folder',
        'category_id' => 1,
        'cat_title'   => 'Test Category',
    ];

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

    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));
    $accessor = new ReflectionAccessor($article);
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

    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));
    $accessor = new ReflectionAccessor($article);
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

    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));
    $accessor = new ReflectionAccessor($article);

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
    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));
    $accessor = new ReflectionAccessor($article);

    $result = $accessor->callProtectedMethod('getViewsData', [['num_views' => 42]]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('num')
        ->and($result)->toHaveKey('title')
        ->and($result)->toHaveKey('after')
        ->and($result['num'])->toBe(42);
});

it('returns replies data structure', function () {
    Config::$modSettings['lp_comment_block'] = 'default';

    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));
    $accessor = new ReflectionAccessor($article);

    $result = $accessor->callProtectedMethod('getRepliesData', [['num_comments' => 7]]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('num')
        ->and($result)->toHaveKey('title')
        ->and($result)->toHaveKey('after')
        ->and($result['num'])->toBeInt();
});

it('checks if page is new', function () {
    $userMock = Mockery::mock(User::class);
    $userMock->last_login = 500;
    $userMock->id = 1;

    $accessorUser = new ReflectionAccessor($userMock);
    $accessorUser->setProtectedProperty('me', $userMock);

    $row = [
        'date'      => 1000,
        'author_id' => 2,
    ];

    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));
    $accessor = new ReflectionAccessor($article);

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
    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));

    Config::$modSettings['lp_show_images_in_articles'] = 1;
    Config::$modSettings['smileys_url'] = '';

    $row = ['content' => 'Some content with <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAB..." alt="test"> image'];

    $reflection = new ReflectionAccessor($article);

    $result = $reflection->callProtectedMethod('getImage', [$row]);
    expect($result)->toBeString();

    Config::$modSettings['lp_show_images_in_articles'] = 0;
    Config::$modSettings['smileys_url'] = '';

    $result = $reflection->callProtectedMethod('getImage', [$row]);
    expect($result)->toBe('');
});

it('checks edit permissions structure', function () {
    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));

    $userMock = Mockery::mock(User::class);
    $userMock->is_admin = true;
    $userMock->id = 1;

    $accessorUser = new ReflectionAccessor($userMock);
    $accessorUser->setProtectedProperty('me', $userMock);

    $reflection = new ReflectionAccessor($article);
    $result = $reflection->callProtectedMethod('canEdit', [['author_id' => 2]]);

    expect($result)->toBeTrue();
});

it('gets edit link', function () {
    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));

    $reflection = new ReflectionAccessor($article);
    $result = $reflection->callProtectedMethod('getEditLink', [['page_id' => 42]]);

    expect($result)->toBeString()
        ->and($result)->toContain('action=admin;area=lp_pages;sa=edit;id=42');
});

it('prepares teaser with last comment content', function () {
    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));

    $reflection = new ReflectionAccessor($article);
    $reflection->setProtectedProperty('sorting', 'last_comment;desc');

    Config::$modSettings['lp_show_teaser'] = 1;

    $page = [];
    $row = [
        'description'     => 'Test description',
        'content'         => 'Test content',
        'num_comments'    => 5,
        'comment_message' => 'Test comment message',
    ];

    $accessor = new ReflectionAccessor($article);
    $accessor->callProtectedMethod('prepareTeaser', [&$page, $row]);

    expect($page)->toHaveKey('teaser');
});

it('prepares teaser with description when available', function () {
    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));
    $accessor = new ReflectionAccessor($article);
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
    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));
    $accessor = new ReflectionAccessor($article);
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
    $article = new PageArticle(Mockery::mock(PortalSqlInterface::class));
    $accessor = new ReflectionAccessor($article);
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
