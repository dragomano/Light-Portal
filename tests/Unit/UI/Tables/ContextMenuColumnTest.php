<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Lists\IconList;
use LightPortal\UI\Tables\ContextMenuColumn;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['lp_actions'] = 'Actions';
    Lang::$txt['modify'] = 'Modify';
    Lang::$txt['remove'] = 'Remove';

    Utils::$context['form_action']    = 'https://example.com/index.php?action=admin;area=lp_pages';
    Utils::$context['lp_actions_url'] = 'https://example.com/index.php?action=admin&area=lp_categories&actions&session=token';

    $icons = [
        'ellipsis' => '<i class="icon-ellipsis"></i>',
    ];

    $iconList = mock(IconList::class);
    $iconList->shouldReceive('__invoke')->andReturn($icons);
    AppMockRegistry::set(IconList::class, $iconList);
});

describe('ContextMenuColumn', function () {
    it('renders actions menu with edit and remove links', function () {
        $column = ContextMenuColumn::make();
        $data   = $column->toArray();
        $html   = $data['data']['function'](['id' => 7]);

        expect($data['header']['value'])->toBe('Actions')
            ->and($html)
            ->toContain('data-id="7"')
            ->toContain('sa=edit;id=7');
    });

    it('toggles the menu with Datastar signals', function () {
        $column = ContextMenuColumn::make();
        $html   = $column->toArray()['data']['function'](['id' => 7]);

        expect($html)
            ->toContain('data-signals="{ ctx_7: false }"')
            ->toContain('data-on:click__outside="$ctx_7 = false"')
            ->toContain('data-on:click="$ctx_7 = true"')
            ->toContain('data-show="$ctx_7"');
    });

    it('renders the menu button as a non-submit button', function () {
        $column = ContextMenuColumn::make();
        $html   = $column->toArray()['data']['function'](['id' => 7]);

        // The list table lives inside a form, so a submit button would reload the page instead of opening the menu
        expect($html)
            ->toContain('<button')
            ->toContain('type="button"')
            ->toContain('class="button floatnone"')
            ->toContain('data-on:click="$ctx_7 = true"');
    });

    it('removes the entity with a Datastar request', function () {
        $column = ContextMenuColumn::make();
        $html   = html_entity_decode($column->toArray()['data']['function'](['id' => 7]), ENT_QUOTES);

        expect($html)
            ->toContain('confirm(smf_you_sure)')
            ->toContain('@post(\'https://example.com/index.php?action=admin&area=lp_categories&actions&session=token\', {payload: {delete_item: 7}})');
    });
});
