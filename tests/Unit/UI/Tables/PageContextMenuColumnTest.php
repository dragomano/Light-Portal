<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
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
        $html = $column->toArray()['data']['function'](['id' => 11]);

        expect($html)
            ->toContain('sa=edit;id=11')
            ->toContain('entity.remove($root)');
    });

    it('renders restore/remove forever actions when deleted', function () {
        $request = mock(RequestInterface::class);
        $request->shouldReceive('has')->with('deleted')->andReturn(true);
        AppMockRegistry::set(RequestInterface::class, $request);

        $column = PageContextMenuColumn::make();
        $html = $column->toArray()['data']['function'](['id' => 12]);

        expect($html)
            ->toContain('entity.restore($root)')
            ->toContain('entity.removeForever($root)');
    });
});
