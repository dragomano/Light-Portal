<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Lists\IconList;
use LightPortal\UI\Tables\PageContextMenuColumn;
use LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;

beforeEach(function () {
    Lang::$txt['modify'] = 'Modify';
    Lang::$txt['remove'] = 'Remove';
    Lang::$txt['restore_message'] = 'Restore';
    Lang::$txt['lp_action_remove_permanently'] = 'Delete forever';

    Config::$scripturl = 'https://example.com/index.php';

    Utils::$context['lp_actions_url'] = 'https://example.com/index.php?action=admin&area=lp_pages&actions&sa=main&session=token';

    $icons = [
        'ellipsis' => '<i class="icon-ellipsis"></i>',
    ];

    $iconList = mock(IconList::class);
    $iconList->shouldReceive('__invoke')->andReturn($icons);
    AppMockRegistry::set(IconList::class, $iconList);
});

describe('PageContextMenuColumn', function () {
    it('renders default edit/remove actions when not deleted', function () {
        $column = PageContextMenuColumn::make();
        $html = html_entity_decode($column->toArray()['data']['function'](['id' => 11]), ENT_QUOTES);

        expect($html)
            ->toContain('sa=edit;id=11')
            ->toContain('data-signals="{ ctx_11: false }"')
            ->toContain('data-on:click="$ctx_11 = true"')
            ->toContain('data-show="$ctx_11"')
            ->toContain("@post('https://example.com/index.php?action=admin&area=lp_pages&actions&sa=main&session=token', {payload: {delete_item: 11}})");
    });

    it('renders restore/remove forever actions when deleted', function () {
        $request = mock(RequestInterface::class);
        $request->shouldReceive('has')->with('deleted')->andReturn(true);
        AppMockRegistry::set(RequestInterface::class, $request);

        $column = PageContextMenuColumn::make();
        $html   = html_entity_decode($column->toArray()['data']['function'](['id' => 12]), ENT_QUOTES);

        expect($html)
            ->toContain('{payload: {restore_item: 12}}')
            ->toContain('{payload: {remove_forever: 12}}')
            ->and($html)->not->toContain('{payload: {delete_item: 12}}');
    });
});
