<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Enums\FrontPageMode;
use LightPortal\UI\Tables\ButtonsRow;

beforeEach(function () {
    Lang::$txt['remove'] = 'Remove';
    Lang::$txt['lp_action_remove_permanently'] = 'Delete forever';
    Lang::$txt['lp_action_toggle'] = 'Toggle';
    Lang::$txt['lp_promote_to_fp'] = 'Promote';
    Lang::$txt['lp_remove_from_fp'] = 'Remove';
    Lang::$txt['quick_mod_go'] = 'Go';
    Lang::$txt['quickmod_confirm'] = 'Confirm';
});

describe('ButtonsRow', function () {
    it('renders actions based on permissions and frontpage mode', function () {
        User::$me->permissions = ['light_portal_approve_pages'];
        Config::$modSettings['lp_frontpage_mode'] = FrontPageMode::CHOSEN_PAGES->value;

        $row   = ButtonsRow::bulkActions(actionName: 'page_actions');
        $value = $row->toArray()['value'];

        expect($value)
            ->toContain('page_actions')
            ->toContain('name="bulk_actions"')
            ->toContain('value="Go"');
    });

    it('renders toggle option when user has approve_pages permission', function () {
        User::$me->permissions = ['light_portal_approve_pages'];
        Config::$modSettings['lp_frontpage_mode'] = FrontPageMode::CHOSEN_PAGES->value;

        $row   = ButtonsRow::bulkActions(options: ['toggle' => 'lp_action_toggle']);
        $value = $row->toArray()['value'];

        expect($value)->toContain('toggle')
            ->and($value)->toContain('Toggle');
    });

    it('renders promote options when frontpage mode is chosen_pages', function () {
        User::$me->permissions = ['light_portal_approve_pages'];
        Config::$modSettings['lp_frontpage_mode'] = FrontPageMode::CHOSEN_PAGES->value;

        $row   = ButtonsRow::bulkActions(options: [
            'promote_up'   => 'lp_promote_to_fp',
            'promote_down' => 'lp_promote_from_fp',
        ]);
        $value = $row->toArray()['value'];

        expect($value)->toContain('promote_up')
            ->and($value)->toContain('promote_down');
    });
});
