<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Enums\FrontPageMode;
use LightPortal\UI\Tables\PageButtonsRow;

beforeEach(function () {
    Lang::$txt['remove'] = 'Remove';
    Lang::$txt['lp_action_remove_permanently'] = 'Delete forever';
    Lang::$txt['lp_action_toggle'] = 'Toggle';
    Lang::$txt['lp_promote_to_fp'] = 'Promote';
    Lang::$txt['lp_remove_from_fp'] = 'Remove';
    Lang::$txt['quick_mod_go'] = 'Go';
    Lang::$txt['quickmod_confirm'] = 'Confirm';
});

describe('PageButtonsRow', function () {
    it('renders actions based on permissions and frontpage mode', function () {
        User::$me->permissions = ['light_portal_approve_pages'];
        Config::$modSettings['lp_frontpage_mode'] = FrontPageMode::CHOSEN_PAGES->value;

        $row = PageButtonsRow::make();
        $value = $row->toArray()['value'];

        expect($value)
            ->toContain('page_actions')
            ->toContain('toggle')
            ->toContain('promote_up')
            ->toContain('promote_down')
            ->toContain('name="mass_actions"')
            ->toContain('value="Go"');
    });
});
