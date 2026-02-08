<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\Lists\IconList;
use LightPortal\UI\Tables\NumViewsColumn;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lp_views'] = 'Views';

    $icons = [
        'views' => '<i class="icon-views"></i>',
    ];

    $iconList = mock(IconList::class);
    $iconList->shouldReceive('__invoke')->andReturn($icons);
    AppMockRegistry::set(IconList::class, $iconList);
});

describe('NumViewsColumn', function () {
    it('configures views column with sort', function () {
        $column = NumViewsColumn::make();
        $data = $column->toArray();

        expect($data['data']['db'])->toBe('num_views')
            ->and($data['data']['class'])->toBe('centertext')
            ->and($data['sort']['default'])->toBe('p.num_views DESC')
            ->and($data['sort']['reverse'])->toBe('p.num_views');
    });
});
