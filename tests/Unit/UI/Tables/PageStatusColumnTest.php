<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
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

    Utils::$context['lp_actions_url'] = 'https://example.com/index.php?action=admin&area=lp_pages&actions&session=token';
});

describe('PageStatusColumn', function () {
    it('renders interactive status toggle for approvers', function () {
        User::$me->permissions = ['light_portal_approve_pages'];

        $column = PageStatusColumn::make('status', '', Status::ACTIVE->value);
        $html   = $column->toArray()['data']['function']([
            'id'     => 4,
            'status' => Status::ACTIVE->value,
        ]);

        expect($html)
            ->toContain('data-signals="{ st_4: true }"')
            ->toContain('data-class:on="$st_4"')
            ->and(html_entity_decode($html, ENT_QUOTES))
            ->toContain("@post('https://example.com/index.php?action=admin&area=lp_pages&actions&session=token', {payload: {toggle_item: 4}})");
    });

    it('renders label for status outside toggle range', function () {
        $column = PageStatusColumn::make();
        $html   = $column->toArray()['data']['function'](['id' => 5, 'status' => Status::UNAPPROVED->value]);

        expect($html)->toBe('Pending');
    });

    it('renders an inactive signal for inactive pages', function () {
        User::$me->permissions = ['light_portal_approve_pages'];

        $column = PageStatusColumn::make('status', '', Status::ACTIVE->value);
        $html   = $column->toArray()['data']['function'](['id' => 6, 'status' => Status::INACTIVE->value]);

        expect($html)->toContain('data-signals="{ st_6: false }"');
    });

    it('renders a static status mark without the approve permission', function () {
        User::$me->permissions = [];

        $column = PageStatusColumn::make('status', '', Status::ACTIVE->value);
        $html   = $column->toArray()['data']['function'](['id' => 7, 'status' => Status::ACTIVE->value]);

        expect($html)
            ->toContain('class="on"')
            ->and($html)->not->toContain('data-on:click')
            ->and($html)->not->toContain('data-signals');
    });
});
