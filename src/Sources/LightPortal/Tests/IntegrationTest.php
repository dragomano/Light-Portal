<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests;

use Bugo\LightPortal\Integration;
use Tester\Assert;

require_once __DIR__ . '/bootstrap.php';

test('hook methods exist', function () {
	Assert::true(method_exists(Integration::class, 'preLoad'));
	Assert::true(method_exists(Integration::class, 'preJavascriptOutput'));
	Assert::true(method_exists(Integration::class, 'preCssOutput'));
	Assert::true(method_exists(Integration::class, 'loadTheme'));
	Assert::true(method_exists(Integration::class, 'changeRedirect'));
	Assert::true(method_exists(Integration::class, 'actions'));
	Assert::true(method_exists(Integration::class, 'defaultAction'));
	Assert::true(method_exists(Integration::class, 'currentAction'));
	Assert::true(method_exists(Integration::class, 'menuButtons'));
	Assert::true(method_exists(Integration::class, 'displayButtons'));
	Assert::true(method_exists(Integration::class, 'deleteMembers'));
	Assert::true(method_exists(Integration::class, 'loadIllegalGuestPermissions'));
	Assert::true(method_exists(Integration::class, 'loadPermissions'));
	Assert::true(method_exists(Integration::class, 'alertTypes'));
	Assert::true(method_exists(Integration::class, 'fetchAlerts'));
	Assert::true(method_exists(Integration::class, 'profileAreas'));
	Assert::true(method_exists(Integration::class, 'profilePopup'));
	Assert::true(method_exists(Integration::class, 'whoisOnline'));
	Assert::true(method_exists(Integration::class, 'cleanCache'));
});

test('preLoad method', function () {
	global $context, $boardurl;

	Assert::type('float', $context['lp_load_time']);
	Assert::type('int', $context['lp_num_queries']);
	Assert::same('Light Portal', LP_NAME);
	Assert::type('string', LP_VERSION);
	Assert::contains('https', LP_PLUGIN_LIST);
	Assert::same($boardurl . '/Sources/LightPortal/Addons', LP_ADDON_URL);
	Assert::same(dirname(__DIR__) . '/Addons', LP_ADDON_DIR);
	Assert::type('int', LP_CACHE_TIME);
	Assert::same('portal', LP_ACTION);
	Assert::same('page', LP_PAGE_PARAM);
});

test('userInfo method', function () {
	global $scripturl;

	Assert::contains($scripturl, LP_BASE_URL);
	Assert::contains(LP_ACTION, LP_BASE_URL);
	Assert::contains('?' . LP_PAGE_PARAM . '=', LP_PAGE_URL);
});

test('loadTheme method', function () {
	global $txt, $context;

	Assert::hasKey('lp_portal', $txt);

	Assert::contains('<div>%1$s</div>', $context['lp_all_title_classes']);
	Assert::contains('<div>%1$s</div>', $context['lp_all_content_classes']);
});

test('actions method', function () {
	$actions = [];

	(new Integration)->actions($actions);

	Assert::hasKey('forum', $actions);
});

test('actions method with portal', function () {
	global $modSettings;

	$actions = [];

	$modSettings['lp_frontpage_mode'] = false;

	(new Integration)->actions($actions);

	Assert::hasNotKey(LP_ACTION, $actions);

	$modSettings['lp_frontpage_mode'] = true;

	(new Integration)->actions($actions);

	Assert::hasKey(LP_ACTION, $actions);
});
