<?php

declare(strict_types=1);

use Tests\CustomTestCase;

require_once __DIR__ . '/../src/Sources/LightPortal/Libs/autoload.php';

pest()->extends(CustomTestCase::class);

if (! isset($GLOBALS['txt'])) {
    $txt['custom_profile_icon'] = 'Icon';

    require_once __DIR__ . '/../src/Themes/default/languages/LightPortal/LightPortal.english.php';

    $GLOBALS['txt'] = $txt;
}

if (! isset($GLOBALS['context'])) {
    $GLOBALS['context'] = [];
}

if (! isset($GLOBALS['smcFunc'])) {
    $GLOBALS['smcFunc'] = [];
}

if (! isset($GLOBALS['scripturl'])) {
    $GLOBALS['scripturl'] = 'https://example.com';
}

if (! defined('LP_NAME')) {
    define('LP_NAME', 'Light Portal');
}

if (! defined('LP_ACTION')) {
    define('LP_ACTION', 'portal');
}

if (! defined('LP_PAGE_PARAM')) {
    define('LP_PAGE_PARAM', 'page');
}

if (! defined('LP_BASE_URL')) {
    define('LP_BASE_URL', $GLOBALS['scripturl'] . '?action=' . LP_ACTION);
}

if (! defined('LP_PAGE_URL')) {
    define('LP_PAGE_URL', $GLOBALS['scripturl'] . '?' . LP_PAGE_PARAM . '=');
}

if (! defined('LP_ADDON_DIR')) {
    define('LP_ADDON_DIR', __DIR__ . '/addons');
}

if (! defined('LP_ALIAS_PATTERN')) {
    define('LP_ALIAS_PATTERN', '^[a-z][a-z0-9\-]+$');
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/namespace_functions.php';
