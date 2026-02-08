<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\UI\Tables\IconColumn;

beforeEach(function () {
    Lang::$txt['custom_profile_icon'] = 'Icon';
});

describe('IconColumn', function () {
    it('sets icon column defaults', function () {
        $column = IconColumn::make();
        $data = $column->toArray();

        expect($data['header']['value'])->toBe('Icon')
            ->and($data['data']['db'])->toBe('icon')
            ->and($data['data']['class'])->toBe('centertext')
            ->and($data['sort']['default'])->toBe('icon')
            ->and($data['sort']['reverse'])->toBe('icon DESC');
    });
});
