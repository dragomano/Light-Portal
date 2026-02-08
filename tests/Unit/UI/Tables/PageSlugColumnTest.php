<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\UI\Tables\PageSlugColumn;

beforeEach(function () {
    Lang::$txt['lp_slug'] = 'Slug';
});

describe('PageSlugColumn', function () {
    it('configures slug column with sort', function () {
        $column = PageSlugColumn::make();
        $data = $column->toArray();

        expect($data['data']['db'])->toBe('slug')
            ->and($data['data']['class'])->toBe('centertext')
            ->and($data['sort']['default'])->toBe('p.slug DESC')
            ->and($data['sort']['reverse'])->toBe('p.slug');
    });
});
