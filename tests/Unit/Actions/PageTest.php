<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Actions\Page;
use LightPortal\Actions\ActionInterface;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\UI\Breadcrumbs\BreadcrumbWrapper;
use LightPortal\Utils\CacheInterface;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

arch()
    ->expect(Page::class)
    ->toImplement(ActionInterface::class);

afterEach(function () {
    AppMockRegistry::clear();
});

it('returns empty data for empty slug', function () {
    $repository = mock(PageRepositoryInterface::class);
    $repository->shouldNotReceive('getData');

    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();

    $page = new Page($repository, $dispatcher);

    expect($page->getDataBySlug(''))->toBe([]);
});

it('loads page data by slug using cache fallback', function () {
    User::$me = new User(1);
    User::$me->language = 'english';

    $repository = mock(PageRepositoryInterface::class);
    $repository->shouldReceive('getData')->once()->with('slug')->andReturn(['id' => 1]);
    $repository->shouldReceive('prepareData')->once()->with(['id' => 1]);

    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();

    $page = new Page($repository, $dispatcher);

    expect($page->getDataBySlug('slug'))->toBe(['id' => 1]);
});

it('sets canonical url for page slug and adds breadcrumbs', function () {
    Lang::$txt['lp_portal'] = 'Portal';
    Lang::$txt['lp_post_error_no_title'] = 'No title';

    Config::$scripturl = 'https://example.com/index.php';

    $GLOBALS['context'] = [
        'lp_page' => [
            'title'       => 'My Page',
            'cat_title'   => 'Category',
            'cat_icon'    => '<i class="fas fa-folder"></i>',
            'category_id' => 10,
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    $breadcrumbs = mock(BreadcrumbWrapper::class);
    $breadcrumbs->shouldReceive('add')->twice()->andReturnSelf();
    AppMockRegistry::set(BreadcrumbWrapper::class, $breadcrumbs);

    $repository = mock(PageRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();

    $page = new Page($repository, $dispatcher);
    $accessor = new ReflectionAccessor($page);

    $accessor->callMethod('setPageTitleAndCanonicalUrl', ['test']);

    expect(Utils::$context['page_title'])->toBe('My Page')
        ->and(Utils::$context['canonical_url'])->toBe(LP_PAGE_URL . 'test');
});

it('handles empty slug - redirect to forum when not chosen page mode', function () {
    Config::$modSettings['lp_frontpage_mode'] = 0;

    $repository = mock(PageRepositoryInterface::class);
    $repository->shouldNotReceive('getData');

    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();

    $page = new Page($repository, $dispatcher);
    $accessor = new ReflectionAccessor($page);

    $accessor->callMethod('handleEmptySlug');

    expect(Config::$modSettings['lp_frontpage_mode'])->toBe(0);
});

it('handles empty slug - loads chosen page when in chosen page mode', function () {
    Config::$modSettings['lp_frontpage_mode'] = 'chosen_page';
    Config::$modSettings['lp_frontpage_chosen_page'] = 'my-page';

    User::$me = new User(1);
    User::$me->language = 'english';

    $GLOBALS['context'] = [];
    Utils::$context = &$GLOBALS['context'];

    $repository = mock(PageRepositoryInterface::class);
    $repository->shouldReceive('getData')->once()->with('my-page')->andReturn(['id' => 1, 'title' => 'My Page']);
    $repository->shouldReceive('prepareData')->once();

    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();

    $page = new Page($repository, $dispatcher);
    $accessor = new ReflectionAccessor($page);

    $accessor->callMethod('handleEmptySlug');

    expect(Utils::$context['lp_page'])->toBe(['id' => 1, 'title' => 'My Page']);
});

it('handles non-empty slug - loads page data', function () {
    $GLOBALS['context'] = [];
    Utils::$context = &$GLOBALS['context'];

    User::$me = new User(1);
    User::$me->language = 'english';

    $repository = mock(PageRepositoryInterface::class);
    $repository->shouldReceive('getData')->once()->with('my-page')->andReturn(['id' => 1, 'title' => 'My Page']);
    $repository->shouldReceive('prepareData')->once();

    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();

    $page = new Page($repository, $dispatcher);
    $accessor = new ReflectionAccessor($page);

    $accessor->callMethod('handleNonEmptySlug', ['my-page']);

    expect(Utils::$context['lp_page'])->toBe(['id' => 1, 'title' => 'My Page']);
});

it('sets page title to portal when slug is empty', function () {
    $GLOBALS['context'] = [
        'lp_page' => [
            'title' => '',
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    Lang::$txt['lp_portal'] = 'Portal';
    Config::$scripturl = 'https://example.com/index.php';

    $breadcrumbs = mock(BreadcrumbWrapper::class);
    $breadcrumbs->shouldReceive('add')->once()->with('Portal')->andReturnSelf();
    AppMockRegistry::set(BreadcrumbWrapper::class, $breadcrumbs);

    $repository = mock(PageRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();

    $page = new Page($repository, $dispatcher);
    $accessor = new ReflectionAccessor($page);

    $accessor->callMethod('setPageTitleAndCanonicalUrl', [null]);

    expect(Utils::$context['page_title'])->toBe('Portal')
        ->and(Utils::$context['canonical_url'])->toBe('https://example.com/index.php');
});

it('prepares page content with parsed content', function () {
    $GLOBALS['context'] = [
        'lp_page' => [
            'content'    => 'Test content',
            'type'       => 'html',
            'created_at' => time() - 100,
            'status'     => 1,
            'can_edit'   => false,
            'errors'     => [],
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    $repository = mock(PageRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();

    $page = new Page($repository, $dispatcher);
    $accessor = new ReflectionAccessor($page);

    $accessor->callMethod('preparePageContent');

    expect(Utils::$context['lp_page']['errors'])->toBe([]);
});

it('adds warning when page is disabled but can be edited', function () {
    $GLOBALS['context'] = [
        'lp_page' => [
            'content'    => 'Test content',
            'type'       => 'html',
            'created_at' => time() - 100,
            'status'     => 0,
            'can_edit'   => true,
            'errors'     => [],
        ],
    ];
    Utils::$context = &$GLOBALS['context'];

    Lang::$txt['lp_page_visible_but_disabled'] = 'Page is visible but disabled';

    $repository = mock(PageRepositoryInterface::class);
    $dispatcher = mock(EventDispatcherInterface::class);
    $dispatcher->shouldReceive('dispatch')->byDefault()->andReturnNull();

    $page = new Page($repository, $dispatcher);
    $accessor = new ReflectionAccessor($page);

    $accessor->callMethod('preparePageContent');

    expect(Utils::$context['lp_page']['errors'])->toContain('Page is visible but disabled');
});
