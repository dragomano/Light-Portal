<?php

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
