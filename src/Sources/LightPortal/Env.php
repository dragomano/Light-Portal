<?php

declare(strict_types=1);

namespace Bugo\LightPortal;

use Bugo\Compat\Config;

defined('LP_NAME') || define('LP_NAME', 'Light Portal');
defined('LP_VERSION') || define('LP_VERSION', '2.6.3');
defined('LP_PLUGIN_LIST') || define('LP_PLUGIN_LIST', 'https://d8d75ea98b25aa12.mokky.dev/addons');
defined('LP_ADDON_URL') || define('LP_ADDON_URL', Config::$boardurl . '/Sources/LightPortal/Addons');
defined('LP_ADDON_DIR') || define('LP_ADDON_DIR', __DIR__ . '/Addons');
defined('LP_CACHE_TIME') || define('LP_CACHE_TIME', (int) (Config::$modSettings['lp_cache_update_interval'] ?? 72000));
defined('LP_ACTION') || define('LP_ACTION', Config::$modSettings['lp_portal_action'] ?? 'portal');
defined('LP_PAGE_PARAM') || define('LP_PAGE_PARAM', Config::$modSettings['lp_page_param'] ?? 'page');
defined('LP_BASE_URL') || define('LP_BASE_URL', Config::$scripturl . '?action=' . LP_ACTION);
defined('LP_PAGE_URL') || define('LP_PAGE_URL', Config::$scripturl . '?' . LP_PAGE_PARAM . '=');
defined('LP_ALIAS_PATTERN') || define('LP_ALIAS_PATTERN', '^[a-z][a-z0-9-_]+$');
defined('LP_AREAS_PATTERN') || define('LP_AREAS_PATTERN', '^[a-z][a-z0-9=|\-,!]+$');
defined('LP_ADDON_PATTERN') || define('LP_ADDON_PATTERN', '^[A-Z][a-zA-Z]+$');

/**
 * @deprecated
 */
if (str_starts_with(SMF_VERSION, '3.0')) {
	$aliases = [
		'Bugo\\LightPortal\\Actions\\BoardIndexNext' => 'Bugo\\LightPortal\\Actions\\BoardIndex',
		'Bugo\\LightPortal\\Utils\\LanguageNext'     => 'Bugo\\LightPortal\\Utils\\Language',
		'Bugo\\LightPortal\\Utils\\SMFTraitNext'     => 'Bugo\\LightPortal\\Utils\\SMFTrait',
	];

	$applyAlias = static fn($class, $alias) => class_alias($class, $alias);

	array_map($applyAlias, array_keys($aliases), $aliases);
}

if (is_file(__DIR__ . '/Libs/scssphp/scssphp/src/Compiler.php')) {
	/** @noinspection PhpIgnoredClassAliasDeclaration */
	class_alias('Bugo\\LightPortal\\Compilers\\Sass', 'Bugo\\LightPortal\\Compilers\\Zero');
}
