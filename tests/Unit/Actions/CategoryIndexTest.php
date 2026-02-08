<?php

declare(strict_types=1);

use Bugo\Bricks\Tables\Interfaces\TablePresenterInterface;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Actions\CategoryIndex;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Repositories\CategoryIndexRepository;
use LightPortal\UI\Breadcrumbs\BreadcrumbWrapper;
use Tests\AppMockRegistry;

afterEach(function () {
    AppMockRegistry::clear();
});

it('builds category table and sets context', function () {
    Config::$modSettings['defaultMaxListItems'] = 25;

    Lang::$txt['lp_all_categories'] = 'All categories';
    Lang::$txt['lp_no_categories'] = 'No categories';
    Lang::$txt['lp_category'] = 'Category';
    Lang::$txt['lp_total_pages_column'] = 'Total pages';

    $GLOBALS['context'] = [];
    Utils::$context = &$GLOBALS['context'];

    $repository = mock(CategoryIndexRepository::class);
    $repository->shouldReceive('getAll')->once()->with(0, 10, 'title')->andReturn([['id' => 1]]);
    $repository->shouldReceive('getTotalCount')->once()->andReturn(3);

    $breadcrumbs = mock(BreadcrumbWrapper::class);
    $breadcrumbs->shouldReceive('add')->once()->with(Lang::$txt['lp_all_categories'])->andReturnSelf();

    $presenter = mock(TablePresenterInterface::class);
    $presenter->shouldReceive('show')->once()->withArgs(function ($builder) {
        $data = $builder->build();

        expect($data['id'])->toBe('categories')
            ->and($data['title'])->toBe(Lang::$txt['lp_all_categories'])
            ->and($data['items_per_page'])->toBe(25)
            ->and($data['no_items_label'])->toBe(Lang::$txt['lp_no_categories'])
            ->and($data['default_sort_col'])->toBe('title')
            ->and($data['base_href'])->toBe(PortalSubAction::CATEGORIES->url())
            ->and(array_keys($data['columns']))->toContain('title', 'num_pages');

        $itemsFn = $data['get_items']['function'];
        $countFn = $data['get_count']['function'];

        expect($itemsFn(0, 10, 'title'))->toBe([['id' => 1]])
            ->and($countFn())->toBe(3);

        return true;
    });

    AppMockRegistry::set(BreadcrumbWrapper::class, $breadcrumbs);
    AppMockRegistry::set(TablePresenterInterface::class, $presenter);

    $action = new CategoryIndex($repository);
    $action->show();

    expect(Utils::$context['page_title'])->toBe(Lang::$txt['lp_all_categories'])
        ->and(Utils::$context['canonical_url'])->toBe(PortalSubAction::CATEGORIES->url())
        ->and(Utils::$context['robot_no_index'])->toBeTrue();
});
