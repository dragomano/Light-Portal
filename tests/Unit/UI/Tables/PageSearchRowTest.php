<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Lists\IconList;
use LightPortal\UI\Tables\PageSearchRow;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lp_pages_search'] = 'Search pages';
    Lang::$txt['search']          = 'Search';
    Utils::$context['search']     = ['string' => 'query'];

    $icons = [
        'search' => '<i class="icon-search"></i>',
    ];

    $iconList = mock(IconList::class);
    $iconList->shouldReceive('__invoke')->andReturn($icons);
    AppMockRegistry::set(IconList::class, $iconList);
});

describe('PageSearchRow', function () {
    it('renders search input and submit button', function () {
        $row   = PageSearchRow::make();
        $value = $row->toArray()['value'];

        expect($value)
            ->toContain('type="search"')
            ->toContain('name="search"')
            ->toContain('placeholder="Search pages"')
            ->toContain('name="is_search"');
    });

    it('wires the field to the live search', function () {
        Utils::$context['lp_actions_url'] = 'https://example.com/index.php?action=admin&area=lp_pages&actions&sa=main';

        $row   = PageSearchRow::make();
        $value = $row->toArray()['value'];

        expect($value)
            ->toContain('data-bind="search"')
            ->toContain('data-indicator="fetching"')
            ->toContain('data-on:input__debounce.300ms=')
            ->toContain('area=lp_pages')
            ->toContain('{payload: {search_items: $search}}')
            ->toContain('data-show="$fetching"')
            ->toContain('data-ignore-morph');
    });
});
