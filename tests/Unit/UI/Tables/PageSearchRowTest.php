<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Lists\IconList;
use LightPortal\UI\Tables\PageSearchRow;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lp_pages_search'] = 'Search pages';
    Lang::$txt['search'] = 'Search';
    Utils::$context['search'] = ['string' => 'query'];

    $icons = [
        'search' => '<i class="icon-search"></i>',
    ];

    $iconList = mock(IconList::class);
    $iconList->shouldReceive('__invoke')->andReturn($icons);
    AppMockRegistry::set(IconList::class, $iconList);
});

describe('PageSearchRow', function () {
    it('renders search input and submit button', function () {
        $row = PageSearchRow::make();
        $value = $row->toArray()['value'];

        expect($value)
            ->toContain('type="search"')
            ->toContain('name="search"')
            ->toContain('placeholder="Search pages"')
            ->toContain('name="is_search"');
    });
});
