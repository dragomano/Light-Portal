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
    $GLOBALS['scripturl'] = 'https://example.com/index.php';
}

if (! isset($GLOBALS['modSettings'])) {
    $GLOBALS['modSettings'] = [];
    $GLOBALS['modSettings']['avatar_url'] = '';
    $GLOBALS['modSettings']['smileys_url'] = 'https://example.com/Smileys';
}

if (! isset($GLOBALS['_POST'])) {
    $GLOBALS['_POST'] = [];
}

if (! isset($GLOBALS['_REQUEST'])) {
    $GLOBALS['_REQUEST'] = [];
}

if (! isset($GLOBALS['_FILES'])) {
    $GLOBALS['_FILES'] = [];
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
    define('LP_ADDON_DIR', sys_get_temp_dir() . '/addons');
}

if (! defined('LP_ALIAS_PATTERN')) {
    define('LP_ALIAS_PATTERN', '^[a-z][a-z0-9\-]+$');
}

if (! defined('LP_CACHE_TIME')) {
    define('LP_CACHE_TIME', 72000);
}

if (! defined('LP_AREAS_PATTERN')) {
    define('LP_AREAS_PATTERN', '^[a-z][a-z0-9=|\-,!]+$');
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/namespace_functions.php';
