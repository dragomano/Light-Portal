<?php

declare(strict_types = 1);

/**
 * Addon.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal;

if (! defined('SMF'))
	die('No direct access...');

final class Addon
{
	public static function getAll(): array
	{
		if (empty($dirs = glob(LP_ADDON_DIR . '/*', GLOB_ONLYDIR)))
			return [];

		return array_map(fn($item): string => basename($item), $dirs);
	}

	public static function prepareAssets()
	{
		global $settings;

		$assets = [];

		Addon::run('prepareAssets', array(&$assets));

		if (empty($assets))
			return;

		foreach (['css', 'scripts'] as $type) {
			if (! isset($assets[$type]))
				continue;

			foreach ($assets[$type] as $plugin => $links) {
				$addonAssetDir = $settings['default_theme_dir'] . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR  . 'light_portal' . DIRECTORY_SEPARATOR . $plugin;

				if (! is_dir($addonAssetDir)) {
					@mkdir($addonAssetDir);
				}

				foreach ($links as $link) {
					if (is_file($filename = $addonAssetDir . DIRECTORY_SEPARATOR . basename($link)))
						continue;

					file_put_contents($filename, fetch_web_data($link), LOCK_EX);
				}
			}
		}
	}

	/**
	 * @see https://dragomano.github.io/Light-Portal/#/plugins/all_hooks
	 */
	public static function run(string $hook = '', array $vars = [], array $plugins = [])
	{
		global $context;
		static $results = [];

		$context['lp_bbc']['icon']  = 'fab fa-bimobject';
		$context['lp_html']['icon'] = 'fab fa-html5';
		$context['lp_php']['icon']  = 'fab fa-php';

		$addons = $plugins ?: $context['lp_enabled_plugins'];

		if (empty($addons))
			return;

		foreach ($addons as $addon) {
			$className = __NAMESPACE__ . '\Addons\\' . $addon . '\\' . $addon;

			if (! class_exists($className))
				continue;

			$class = new $className;

			if (empty($results[$addon]['snake'])) {
				$results[$addon]['snake'] = Helper::getSnakeName($addon);

				$path = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR;
				self::loadLanguage($path, $results[$addon]['snake']);
				self::loadAssets($path, $results[$addon]['snake']);

				$context['lp_' . $results[$addon]['snake']]['type'] = $class->type;
				$context['lp_' . $results[$addon]['snake']]['icon'] = $class->icon;
			}

			// Hook init should run only once
			if (empty($results[$addon]['init']) && method_exists($class, 'init') && in_array($addon, $context['lp_enabled_plugins'])) {
				$class->init();
				$results[$addon]['init'] = true;
			}

			if (method_exists($class, $hook)) {
				$class->$hook(...$vars);
			}
		}
	}

	private static function loadLanguage(string $path, string $snake_name)
	{
		global $txt, $user_info;

		if (isset($txt['lp_' . $snake_name]))
			return;

		$languages = array_unique(['english', $user_info['language']]);

		$addonLanguages = [];
		foreach ($languages as $lang) {
			$lang_file = $path . 'langs' . DIRECTORY_SEPARATOR . $lang . '.php';

			$addonLanguages[$lang] = is_file($lang_file) ? require_once $lang_file : [];
		}

		if (is_array($addonLanguages['english']))
			$txt['lp_' . $snake_name] = array_merge($addonLanguages['english'], $addonLanguages[$user_info['language']]);
	}

	private static function loadAssets(string $path, string $snake_name)
	{
		global $settings;

		if (! is_file($style = $path . 'style.css'))
			return;

		$addonCss = $settings['default_theme_dir'] . '/css/light_portal/addon_' . $snake_name . '.css';

		$isFileExists = true;
		if (! is_file($addonCss) || filemtime($style) > filemtime($addonCss))
			$isFileExists = @copy($style, $addonCss);

		if (! @is_writable($settings['default_theme_dir'] . '/css/light_portal') || ! $isFileExists)
			return;

		loadCSSFile('light_portal/addon_' . $snake_name . '.css');
	}
}
