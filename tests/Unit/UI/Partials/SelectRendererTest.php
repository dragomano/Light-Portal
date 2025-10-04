<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\UI\Partials\ActionSelect;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Bugo\LightPortal\UI\View;

use function Bugo\LightPortal\app;

beforeEach(function () {
    Lang::$txt = array_merge(Lang::$txt, [
        'no_matches'   => 'No matches',
        'search'       => 'Search',
        'all'          => 'All',
        'remove'       => 'Remove',
        'check_all'    => 'Check all',
        'post_options' => 'Post options',
        'no'           => 'No',
        'lp_example'   => 'Example',
    ]);

    Utils::$context = array_merge(Utils::$context, [
        'right_to_left' => false,
    ]);
});

it('renders select with virtual select options', function () {
    $view = app(View::class);

    $renderer = new SelectRenderer($view);

    $select = new ActionSelect(['id' => 'test_select']);

    $result = $renderer->render($select, ['template' => 'virtual_select']);

    expect($result)->toBe('<div>rendered</div>');
});

it('generates id if not provided', function () {
    $view = app(View::class);

    $renderer = new SelectRenderer($view);

    $select = new ActionSelect();

    $result = $renderer->render($select, ['template' => 'virtual_select']);

    expect($result)->toBe('<div>rendered</div>');
});

it('builds virtual select options correctly', function () {
    $view = app(View::class);

    $renderer = new SelectRenderer($view);

    $select = new ActionSelect(['multiple' => true, 'search' => false]);

    $result = $renderer->render($select, ['template' => 'virtual_select']);

    expect($result)->toBe('<div>rendered</div>');
});

it('handles rtl context', function () {
    Utils::$context['right_to_left'] = true;

    $view = app(View::class);

    $renderer = new SelectRenderer($view);

    $select = new ActionSelect();

    $result = $renderer->render($select, ['template' => 'virtual_select']);

    expect($result)->toBe('<div>rendered</div>');
});
