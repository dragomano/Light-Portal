<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\UI\Tables\TitleColumn;

beforeEach(function () {
    Lang::$txt['lp_title'] = 'Title';
});

describe('TitleColumn', function () {
    it('renders link for active entries', function () {
        $column = TitleColumn::make('title', '', 'pages');
        $html = $column->toArray()['data']['function']([
            'id' => 2,
            'status' => true,
            'title' => 'My Page',
        ])->toHtml();

        expect($html)
            ->toContain('class="bbc_link"')
            ->toContain('My Page')
            ->toContain(LP_BASE_URL . ';sa=pages;id=2');
    });

    it('renders plain title for inactive entries', function () {
        $column = TitleColumn::make('title', '', 'pages');
        $value = $column->toArray()['data']['function']([
            'id' => 2,
            'status' => false,
            'title' => 'Draft',
        ]);

        expect($value)->toBe('Draft');
    });
});
