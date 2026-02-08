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

    $cache = new class implements CacheInterface {
        public function withKey(?string $key): CacheInterface
        {
            return $this;
        }

        public function setLifeTime(int $lifeTime): CacheInterface
        {
            return $this;
        }

        public function remember(string $key, callable $callback, ?int $time = null): mixed
        {
            return $callback();
        }

        public function setFallback(callable $callback): mixed
        {
            return $callback();
        }

        public function get(string $key, int $time = null): null
        {
            return null;
        }

        public function put(string $key, mixed $value, int $time = null): void {}

        public function forget(?string $key = null): void {}

        public function flush(): void {}
    };
    AppMockRegistry::set(CacheInterface::class, $cache);

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
            'title' => 'My Page',
            'cat_title' => 'Category',
            'cat_icon' => '<i class="fas fa-folder"></i>',
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
