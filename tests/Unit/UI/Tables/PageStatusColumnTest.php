<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Enums\Status;
use LightPortal\UI\Tables\PageStatusColumn;

beforeEach(function () {
    Lang::$txt['lp_action_off'] = 'Off';
    Lang::$txt['lp_action_on'] = 'On';
    Lang::$txt['lp_page_status_set'] = [
        0 => 'No',
        1 => 'Yes',
        2 => 'Pending',
    ];
    Lang::$txt['no'] = 'No';
});

describe('PageStatusColumn', function () {
    it('renders interactive status toggle for approvers', function () {
        User::$me->permissions = ['light_portal_approve_pages'];

        $column = PageStatusColumn::make('status', '', Status::ACTIVE->value);
        $html = $column->toArray()['data']['function']([
            'id' => 4,
            'status' => Status::ACTIVE->value,
        ])->toHtml();

        expect($html)->toContain('entity.toggleStatus');
    });

    it('renders label for status outside toggle range', function () {
        $column = PageStatusColumn::make();
        $html = $column->toArray()['data']['function'](['id' => 5, 'status' => Status::UNAPPROVED->value]);

        expect($html)->toBe('Pending');
    });
});
