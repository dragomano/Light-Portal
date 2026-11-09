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
    it('renders the search box without a submit button', function () {
        $row   = PageSearchRow::make();
        $value = $row->toArray()['value'];

        expect($value)
            ->toContain('lp_search_box')
            ->toContain('type="search"')
            ->toContain('name="search"')
            ->toContain('placeholder="Search pages (* and ? are masks)"')
            ->toContain('title="* and ? are masks"')
            ->toContain('lp_search_icon')
            ->not()->toContain('name="is_search"')
            ->not()->toContain('<button');
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
            ->toContain('{payload: {search_items: $search, page_type: $page_type}}')
            ->toContain('data-show="$fetching"')
            ->toContain('data-ignore-morph');
    });

    it('shows the number of matching pages', function () {
        Utils::$context['lp_pages_found'] = 7;

        $row   = PageSearchRow::make();
        $value = $row->toArray()['value'];

        expect($value)
            ->toContain('lp_search_found')
            ->toContain('Found:')
            ->toContain('data-text="$pages_found"')
            ->toContain('>7<');
    });

    it('does not show the counter when the context has no count', function () {
        unset(Utils::$context['lp_pages_found']);

        $value = PageSearchRow::make()->toArray()['value'];

        expect($value)->not()->toContain('lp_search_found');
    });

    it('sends the same live request from Enter and from the magnifier', function () {
        Utils::$context['lp_actions_url'] = 'https://example.com/index.php?action=admin&area=lp_pages&actions&sa=main';

        $value = PageSearchRow::make()->toArray()['value'];

        // Enter and the magnifier must not submit the form the classic way when Datastar is running
        expect($value)
            ->toContain('data-on:keydown=')
            ->toContain('data-on:click=')
            ->toContain('evt.key ===')
            ->toContain('evt.preventDefault()')
            ->and(substr_count($value, 'search_items: $search'))->toBe(3);
    });
});
