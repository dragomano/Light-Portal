<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Utils\Setting;

arch()
    ->expect(Setting::class)
    ->toHaveMethod('get');

beforeEach(function () {
    Config::$modSettings = [];
    User::$me->id = 0;
    User::$me->allowedTo = function () {
        return false;
    };
});

afterEach(function () {
    Config::$modSettings = [];
    Lang::$txt['lang_rtl'] = null;
    unset(Utils::$context['current_action']);
    unset(Utils::$context['lp_blocks']);
});

describe('Setting::get()', function () {
    it('returns default when key is not set', function () {
        $result = Setting::get('unknown_key');

        expect($result)->toBeNull();
    });

    it('returns default for missing key with custom default', function () {
        $result = Setting::get('unknown_key', 'string', 'default_value');

        expect($result)->toBe('default_value');
    });

    it('returns string value by default', function () {
        Config::$modSettings['test_key'] = 'test_value';

        $result = Setting::get('test_key');

        expect($result)->toBe('test_value');
    });

    it('returns boolean value', function () {
        Config::$modSettings['bool_key'] = 'true';

        $result = Setting::get('bool_key', 'bool');

        expect($result)->toBeTrue();
    });

    it('returns false for non-true boolean string', function () {
        Config::$modSettings['bool_key'] = 'false';

        $result = Setting::get('bool_key', 'bool');

        expect($result)->toBeFalse();
    });

    it('returns integer value', function () {
        Config::$modSettings['int_key'] = '42';

        $result = Setting::get('int_key', 'int');

        expect($result)->toBe(42);
    });

    it('returns float value', function () {
        Config::$modSettings['float_key'] = '3.14';

        $result = Setting::get('float_key', 'float');

        expect($result)->toBe(3.14);
    });

    it('returns array from comma-separated string', function () {
        Config::$modSettings['array_key'] = 'one,two,three';

        $result = Setting::get('array_key', 'array');

        expect($result)->toBe(['one', 'two', 'three']);
    });

    it('returns array from json string', function () {
        Config::$modSettings['json_key'] = '{"a":1,"b":2}';

        $result = Setting::get('json_key', 'array', [], 'json');

        expect($result)->toBe(['a' => 1, 'b' => 2]);
    });

    it('filters empty values from array', function () {
        Config::$modSettings['array_key'] = 'one,,two,,three';

        $result = Setting::get('array_key', 'array');

        expect($result)->toBe([0 => 'one', 2 => 'two', 4 => 'three']);
    });
});

describe('Setting::getFrontpageTopics()', function () {
    it('returns empty array when not set', function () {
        $result = Setting::getFrontpageTopics();

        expect($result)->toBe([]);
    });

    it('returns array from setting', function () {
        Config::$modSettings['lp_frontpage_topics'] = '1,2,3';

        $result = Setting::getFrontpageTopics();

        expect($result)->toBe(['1', '2', '3']);
    });
});

describe('Setting::getHeaderPanelWidth()', function () {
    it('returns default 12 when not set', function () {
        $result = Setting::getHeaderPanelWidth();

        expect($result)->toBe(12);
    });

    it('returns integer value from setting', function () {
        Config::$modSettings['lp_header_panel_width'] = '8';

        $result = Setting::getHeaderPanelWidth();

        expect($result)->toBe(8);
    });
});

describe('Setting::getFooterPanelWidth()', function () {
    it('returns default 12 when not set', function () {
        $result = Setting::getFooterPanelWidth();

        expect($result)->toBe(12);
    });

    it('returns integer value from setting', function () {
        Config::$modSettings['lp_footer_panel_width'] = '6';

        $result = Setting::getFooterPanelWidth();

        expect($result)->toBe(6);
    });
});

describe('Setting::getLeftPanelWidth()', function () {
    it('returns default array when not set', function () {
        $result = Setting::getLeftPanelWidth();

        expect($result)->toBe(['lg' => 3, 'xl' => 2]);
    });

    it('returns array from json setting', function () {
        Config::$modSettings['lp_left_panel_width'] = '{"lg":4,"xl":3}';

        $result = Setting::getLeftPanelWidth();

        expect($result)->toBe(['lg' => 4, 'xl' => 3]);
    });
});

describe('Setting::getRightPanelWidth()', function () {
    it('returns default array when not set', function () {
        $result = Setting::getRightPanelWidth();

        expect($result)->toBe(['lg' => 3, 'xl' => 2]);
    });

    it('returns array from json setting', function () {
        Config::$modSettings['lp_right_panel_width'] = '{"lg":4,"xl":3}';

        $result = Setting::getRightPanelWidth();

        expect($result)->toBe(['lg' => 4, 'xl' => 3]);
    });
});

describe('Setting::getColumnWidth()', function () {
    it('returns 12 when no panels', function () {
        Utils::$context['lp_blocks'] = [];

        $result = Setting::getColumnWidth('lg');

        expect($result)->toBe(12);
    });

    it('calculates correctly with left panel only', function () {
        Utils::$context['lp_blocks'] = ['left' => true, 'right' => false];
        Config::$modSettings['lp_left_panel_width'] = '{"lg":3,"xl":2}';
        Config::$modSettings['lp_right_panel_width'] = '{"lg":3,"xl":2}';

        $result = Setting::getColumnWidth('lg');

        expect($result)->toBe(9);
    });

    it('calculates correctly with both panels', function () {
        Utils::$context['lp_blocks'] = ['left' => true, 'right' => true];
        Config::$modSettings['lp_left_panel_width'] = '{"lg":3,"xl":2}';
        Config::$modSettings['lp_right_panel_width'] = '{"lg":4,"xl":3}';

        $result = Setting::getColumnWidth('lg');

        expect($result)->toBe(5);
    });
});

describe('Setting::getPanelDirection()', function () {
    it('returns default 0 when not set', function () {
        $result = Setting::getPanelDirection('left');

        expect($result)->toBe('0');
    });

    it('returns direction from json array', function () {
        Config::$modSettings['lp_panel_direction'] = '{"left":"1","right":"0"}';

        $result = Setting::getPanelDirection('left');

        expect($result)->toBe('1');
    });
});

describe('Setting::isSwapLeftRight()', function () {
    it('returns false when lp_swap_left_right is false and not RTL', function () {
        Config::$modSettings['lp_swap_left_right'] = '0';

        $result = Setting::isSwapLeftRight();

        expect($result)->toBeFalse();
    });

    it('returns true when lp_swap_left_right is true and not RTL', function () {
        Config::$modSettings['lp_swap_left_right'] = '1';

        $result = Setting::isSwapLeftRight();

        expect($result)->toBeTrue();
    });

    it('returns false when lp_swap_left_right is true but RTL', function () {
        Config::$modSettings['lp_swap_left_right'] = '1';
        Lang::$txt['lang_rtl'] = '1';

        $result = Setting::isSwapLeftRight();

        expect($result)->toBeFalse();
    });

    it('returns true when lp_swap_left_right is false but RTL', function () {
        Config::$modSettings['lp_swap_left_right'] = '0';
        Lang::$txt['lang_rtl'] = '1';

        $result = Setting::isSwapLeftRight();

        expect($result)->toBeTrue();
    });
});

describe('Setting::canMention()', function () {
    beforeEach(function () {
        User::$me->id = 0;
        User::$me->allowedTo = fn() => false;
    });

    it('returns true when enable_mentions is true', function () {
        Config::$modSettings['enable_mentions'] = '1';

        $result = Setting::canMention();

        expect($result)->toBeTrue();
    });

    it('returns true when user cannot mention', function () {
        Config::$modSettings['enable_mentions'] = '0';

        $result = Setting::canMention();

        expect($result)->toBeTrue();
    });
});

describe('Setting::hideBlocksInACP()', function () {
    it('returns false when lp_hide_blocks_in_acp is false', function () {
        Config::$modSettings['lp_hide_blocks_in_acp'] = '0';

        $result = Setting::hideBlocksInACP();

        expect($result)->toBeFalse();
    });

    it('returns false when not in admin', function () {
        Config::$modSettings['lp_hide_blocks_in_acp'] = '1';

        $result = Setting::hideBlocksInACP();

        expect($result)->toBeFalse();
    });

    it('returns true when hide_blocks is true and in admin', function () {
        Config::$modSettings['lp_hide_blocks_in_acp'] = '1';
        Utils::$context['current_action'] = 'admin';

        $result = Setting::hideBlocksInACP();

        expect($result)->toBeTrue();
    });
});

describe('Setting::getEnabledPlugins()', function () {
    it('returns empty array when not set', function () {
        $result = Setting::getEnabledPlugins();

        expect($result)->toBe([]);
    });

    it('returns array from comma-separated string', function () {
        Config::$modSettings['lp_enabled_plugins'] = 'plugin1,plugin2';

        $result = Setting::getEnabledPlugins();

        expect($result)->toBe(['plugin1', 'plugin2']);
    });
});

describe('Setting::getFrontpagePages()', function () {
    it('returns empty array when not set', function () {
        $result = Setting::getFrontpagePages();

        expect($result)->toBe([]);
    });

    it('returns array from comma-separated string', function () {
        Config::$modSettings['lp_frontpage_pages'] = 'page1,page2';

        $result = Setting::getFrontpagePages();

        expect($result)->toBe(['page1', 'page2']);
    });
});

describe('Setting::getDisabledActions()', function () {
    it('returns empty array when not set', function () {
        $result = Setting::getDisabledActions();

        expect($result)->toBe([]);
    });

    it('returns array from comma-separated string', function () {
        Config::$modSettings['lp_disabled_actions'] = 'action1,action2';

        $result = Setting::getDisabledActions();

        expect($result)->toBe(['action1', 'action2']);
    });
});
