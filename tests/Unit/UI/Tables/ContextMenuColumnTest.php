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

    Utils::$context['form_action'] = 'https://example.com/index.php?action=admin;area=lp_pages';

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
        $data = $column->toArray();
        $html = $data['data']['function'](['id' => 7]);

        expect($data['header']['value'])->toBe('Actions')
            ->and($html)
            ->toContain('data-id="7"')
            ->toContain('sa=edit;id=7')
            ->toContain('entity.remove($root)');
    });
});
