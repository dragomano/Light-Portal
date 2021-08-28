<?php

namespace Bugo\LightPortal;

/**
 * Addons.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Addons
{
	/**
	 * @return array
	 */
	public static function getAll(): array
	{
		$dirs = glob(LP_ADDON_DIR . '/*', GLOB_ONLYDIR) or array();

		$addons = [];
		foreach ($dirs as $dir) {
			$addons[] = basename($dir);
		}

		return $addons;
	}

	/**
	 * @param string $addon_name
	 * @param string $snake_name
	 * @return void
	 */
	public static function loadLanguage(string $addon_name, string $snake_name)
	{
		global $txt, $user_info;

		if (isset($txt['lp_' . $snake_name]))
			return;

		$addon_dir = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $addon_name . '/langs/';
		$languages = array_unique(['english', $user_info['language']]);

		$addon_languages = [];
		foreach ($languages as $lang) {
			$lang_file = $addon_dir . $lang . '.php';

			$addon_languages[$lang] = is_file($lang_file) ? require_once $lang_file : [];
		}

		if (is_array($addon_languages['english']))
			$txt['lp_' . $snake_name] = array_merge($addon_languages['english'], $addon_languages[$user_info['language']]);
	}

	/**
	 * @param string $addon_name
	 * @param string $snake_name
	 * @return void
	 */
	public static function loadCss(string $addon_name, string $snake_name)
	{
		global $settings;

		$style = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $addon_name . '/style.css';

		if (!is_file($style))
			return;

		$addon_css = $settings['default_theme_dir'] . '/css/light_portal/addon_' . $snake_name . '.css';

		$css_exists = true;
		if (!is_file($addon_css) || filemtime($style) > filemtime($addon_css))
			$css_exists = @copy($style, $addon_css);

		if (!@is_writable($settings['default_theme_dir'] . '/css/light_portal') || !$css_exists)
			return;

		loadCSSFile('light_portal/addon_' . $snake_name . '.css');
	}

	/**
	 * @see https://dragomano.github.io/Light-Portal/#/plugins/all_hooks
	 *
	 * @param string $hook
	 * @param array $vars (extra variables)
	 * @param array $plugins (that should be loaded)
	 * @return void
	 */
	public static function run(string $hook = '', array $vars = [], array $plugins = [])
	{
		global $context;
		static $results = [];

		$context['lp_bbc']['icon']  = 'fab fa-bimobject';
		$context['lp_html']['icon'] = 'fab fa-html5';
		$context['lp_php']['icon']  = 'fab fa-php';

		$addons = !empty($plugins) ? $plugins : $context['lp_enabled_plugins'];

		if (empty($addons))
			return;

		foreach ($addons as $id => $addon) {
			$className = __NAMESPACE__ . '\Addons\\' . $addon . '\\' . $addon;

			if (!class_exists($className))
				continue;

			$class = new $className;

			if (!isset($snake_name[$id])) {
				$snake_name[$id] = Helpers::getSnakeName($addon);

				self::loadLanguage($addon, $snake_name[$id]);
				self::loadCss($addon, $snake_name[$id]);

				$context['lp_' . $snake_name[$id]]['type'] = property_exists($class, 'type') ? $class->type : 'block';
				$context['lp_' . $snake_name[$id]]['icon'] = property_exists($class, 'icon') ? $class->icon : 'fas fa-puzzle-piece';
			}

			// Hook init should run only once
			if (empty($results[$id]['init']) && method_exists($class, 'init') && in_array($addon, $context['lp_enabled_plugins'])) {
				$class->init();
				$results[$id]['init'] = $addon;
			}

			if (method_exists($class, $hook))
				$class->$hook(...$vars);
		}
	}
}
