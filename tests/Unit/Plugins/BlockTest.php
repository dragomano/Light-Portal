<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Enums\Placement;
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\Block;

beforeEach(function () {
    Lang::$txt['lp_test_dummy_block'] = Lang::$txt['lp_test_dummy_block'] ?? [];
    Utils::$context['lp_test_dummy_block_plugin'] = [];
});

it('checks that the block is in placements', function () {
    Utils::$context['lp_active_blocks'] = [0 => ['placement' => Placement::LEFT->value]];

    $block = new TestDummyBlock();

    expect($block->isInPlacements(0, [Placement::LEFT->value, Placement::RIGHT->value]))->toBeTrue()
        ->and($block->isInPlacements(0, [Placement::HEADER->value]))->toBeFalse();
});

it('falls back to the current block context', function () {
    Utils::$context['lp_active_blocks'] = [];
    Utils::$context['lp_block'] = ['placement' => Placement::HEADER->value];

    $block = new TestDummyBlock();

    expect($block->isInPlacements(99, [Placement::HEADER->value]))->toBeTrue()
        ->and($block->isInPlacements(99, [Placement::LEFT->value]))->toBeFalse();
});

it('checks that the block is in the sidebar', function () {
    Utils::$context['lp_active_blocks'] = [0 => ['placement' => Placement::RIGHT->value]];

    $block = new TestDummyBlock();

    expect($block->isInSidebar(0))->toBeTrue();

    Utils::$context['lp_active_blocks'] = [0 => ['placement' => Placement::FOOTER->value]];

    expect($block->isInSidebar(0))->toBeFalse();
});

class TestDummyBlock extends Block
{
}
