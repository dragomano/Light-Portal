<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Actions\CardListInterface;
use LightPortal\Actions\CategoryPageList;
use LightPortal\Articles\Services\CategoryPageArticleService;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Lists\CategoryList;
use LightPortal\UI\Breadcrumbs\BreadcrumbWrapper;
use LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;

afterEach(function () {
    AppMockRegistry::clear();
});

it('shows pages without category when id is zero', function () {
    Lang::$txt['lp_all_categories'] = 'All categories';
    Lang::$txt['lp_all_pages_without_category'] = 'Pages without category';
    Lang::$txt['lp_all_pages_with_category'] = 'Pages in %s';
    Lang::$txt['lp_no_category'] = 'No category';
    Lang::$txt['back'] = 'Back';

    Config::$scripturl = 'https://example.com/index.php';
    $GLOBALS['context'] = [];
    Utils::$context = &$GLOBALS['context'];

    $request = mock(RequestInterface::class);
    $request->shouldReceive('get')->with('id')->andReturn('0');
    AppMockRegistry::set(RequestInterface::class, $request);

    $categoryList = new class {
        public function __invoke(): array
        {
            return [
                0 => [
                    'id' => 0,
                    'title' => '',
                    'description' => '',
                    'icon' => '',
                ],
            ];
        }
    };
    AppMockRegistry::set(CategoryList::class, $categoryList);

    $breadcrumbs = mock(BreadcrumbWrapper::class);
    $breadcrumbs->shouldReceive('add')->twice()->andReturnSelf();
    AppMockRegistry::set(BreadcrumbWrapper::class, $breadcrumbs);

    $cardList = mock(CardListInterface::class);
    $cardList->shouldReceive('show')->once();

    $articleService = mock(CategoryPageArticleService::class);
    $articleService->shouldReceive('init')->once();

    $action = new CategoryPageList($cardList, $articleService);
    $action->show();

    expect(Utils::$context['page_title'])->toBe(Lang::$txt['lp_all_pages_without_category'])
        ->and(Utils::$context['canonical_url'])->toBe(PortalSubAction::CATEGORIES->url() . ';id=0')
        ->and(Utils::$context['robot_no_index'])->toBeTrue();
});

it('shows pages for selected category', function () {
    Lang::$txt['lp_all_categories'] = 'All categories';
    Lang::$txt['lp_all_pages_without_category'] = 'Pages without category';
    Lang::$txt['lp_all_pages_with_category'] = 'Pages in %s';
    Lang::$txt['lp_no_category'] = 'No category';
    Lang::$txt['back'] = 'Back';

    Config::$scripturl = 'https://example.com/index.php';
    $GLOBALS['context'] = [];
    Utils::$context = &$GLOBALS['context'];

    $request = mock(RequestInterface::class);
    $request->shouldReceive('get')->with('id')->andReturn('5');
    AppMockRegistry::set(RequestInterface::class, $request);

    $categoryList = new class {
        public function __invoke(): array
        {
            return [
                5 => [
                    'id' => 5,
                    'title' => 'News',
                    'description' => 'Latest updates',
                    'icon' => '<i class="fas fa-folder"></i>',
                ],
            ];
        }
    };
    AppMockRegistry::set(CategoryList::class, $categoryList);

    $breadcrumbs = mock(BreadcrumbWrapper::class);
    $breadcrumbs->shouldReceive('add')->twice()->andReturnSelf();
    AppMockRegistry::set(BreadcrumbWrapper::class, $breadcrumbs);

    $cardList = mock(CardListInterface::class);
    $cardList->shouldReceive('show')->once();

    $articleService = mock(CategoryPageArticleService::class);
    $articleService->shouldReceive('init')->once();

    $action = new CategoryPageList($cardList, $articleService);
    $action->show();

    expect(Utils::$context['page_title'])->toBe('Pages in News')
        ->and(Utils::$context['description'])->toBe('Latest updates')
        ->and(Utils::$context['lp_category_edit_link'])
        ->toBe('https://example.com/index.php?action=admin;area=lp_categories;sa=edit;id=5')
        ->and(Utils::$context['canonical_url'])->toBe(PortalSubAction::CATEGORIES->url() . ';id=5')
        ->and(Utils::$context['robot_no_index'])->toBeTrue();
});
