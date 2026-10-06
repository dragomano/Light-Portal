<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\Enums\Placement;

arch()
    ->expect(Placement::class)
    ->toBeEnum();

it('returns values of all cases', function () {
    expect(Placement::values())->toBe(['header', 'top', 'left', 'right', 'bottom', 'footer']);
});

it('combines values with localized labels', function () {
    Lang::$txt['lp_block_placement_set'] = [
        'header' => 'Header',
        'top'    => 'Top',
        'left'   => 'Left',
        'right'  => 'Right',
        'bottom' => 'Bottom',
        'footer' => 'Footer',
    ];

    expect(Placement::all())->toBe([
        'header' => 'Header',
        'top'    => 'Top',
        'left'   => 'Left',
        'right'  => 'Right',
        'bottom' => 'Bottom',
        'footer' => 'Footer',
    ]);
});
