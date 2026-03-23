<?php

declare(strict_types=1);

use Bugo\Bricks\Tables\Interfaces\TablePresenterInterface;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Actions\TagIndex;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Repositories\TagIndexRepository;
use LightPortal\UI\Breadcrumbs\BreadcrumbWrapper;
use Tests\AppMockRegistry;

afterEach(function () {
    AppMockRegistry::clear();
});

it('builds tag table and sets context', function () {
    Config::$modSettings['defaultMaxListItems'] = 50;

    Lang::$txt['lp_all_page_tags'] = 'All tags';
    Lang::$txt['lp_no_tags'] = 'No tags';
    Lang::$txt['lp_tag_column'] = 'Tag';
    Lang::$txt['lp_frequency_column'] = 'Frequency';

    $GLOBALS['context'] = [];
    Utils::$context = &$GLOBALS['context'];

    $repository = mock(TagIndexRepository::class);
    $repository
        ->shouldReceive('getAll')
        ->once()
        ->with(0, 20, 'title')
        ->andReturn([['id' => 1, 'title' => 'Tag 1', 'icon' => '<i class="fas fa-tag"></i>', 'link' => '?tag=1', 'frequency' => 5]]);
    $repository->shouldReceive('getTotalCount')->once()->andReturn(10);

    $breadcrumbs = mock(BreadcrumbWrapper::class);
    $breadcrumbs->shouldReceive('add')->once()->with(Lang::$txt['lp_all_page_tags'])->andReturnSelf();

    $presenter = mock(TablePresenterInterface::class);
    $presenter->shouldReceive('show')->once()->withArgs(function ($builder) {
        $data = $builder->build();

        expect($data['id'])->toBe('tags')
            ->and($data['title'])->toBe(Lang::$txt['lp_all_page_tags'])
            ->and($data['items_per_page'])->toBe(50)
            ->and($data['no_items_label'])->toBe(Lang::$txt['lp_no_tags'])
            ->and($data['default_sort_col'])->toBe('value')
            ->and($data['base_href'])->toBe(PortalSubAction::TAGS->url())
            ->and(array_keys($data['columns']))->toContain('value', 'frequency');

        $itemsFn = $data['get_items']['function'];
        $countFn = $data['get_count']['function'];

        expect($itemsFn(0, 20, 'title'))
            ->toBe([['id' => 1, 'title' => 'Tag 1', 'icon' => '<i class="fas fa-tag"></i>', 'link' => '?tag=1', 'frequency' => 5]])
            ->and($countFn())->toBe(10);

        return true;
    });

    AppMockRegistry::set(BreadcrumbWrapper::class, $breadcrumbs);
    AppMockRegistry::set(TablePresenterInterface::class, $presenter);

    $action = new TagIndex($repository);
    $action->show();

    expect(Utils::$context['page_title'])->toBe(Lang::$txt['lp_all_page_tags'])
        ->and(Utils::$context['canonical_url'])->toBe(PortalSubAction::TAGS->url())
        ->and(Utils::$context['robot_no_index'])->toBeTrue();
});
