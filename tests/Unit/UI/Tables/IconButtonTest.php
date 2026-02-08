<?php

declare(strict_types=1);

use LightPortal\Lists\IconList;
use LightPortal\UI\Tables\IconButton;
use Tests\AppMockRegistry;

beforeEach(function () {
    $icons = [
        'plus' => '<i class="icon-plus"></i>',
    ];

    $iconList = mock(IconList::class);
    $iconList->shouldReceive('__invoke')->andReturn($icons);
    AppMockRegistry::set(IconList::class, $iconList);
});

describe('IconButton', function () {
    it('renders button with icon and custom attributes', function () {
        $html = IconButton::make('plus', ['data-id' => '1', 'x-on:click' => 'do()']);

        expect($html)
            ->toContain('<button')
            ->toContain('class="button"')
            ->toContain('data-id="1"')
            ->toContain('x-on:click="do()"')
            ->toContain('icon-plus');
    });
});
