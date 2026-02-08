<?php

declare(strict_types=1);

use LightPortal\UI\Tables\Link;

describe('Link', function () {
    it('renders anchor with text and attributes', function () {
        $html = Link::make('Edit', ['href' => '/edit', 'data-id' => '5'], 'button small');

        expect($html)
            ->toContain('<a')
            ->toContain('class="button small"')
            ->toContain('href="/edit"')
            ->toContain('data-id="5"')
            ->toContain('Edit');
    });
});
