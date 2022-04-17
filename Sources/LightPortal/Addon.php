<?php declare(strict_types=1);

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

use function fetch_web_data;
use function loadCSSFile;

if (! defined('SMF'))
	die('No direct access...');

final class Addon
{
	use Helper;

	private array $chest = [];

	public function getAll(): array
	{
		if (empty($dirs = glob(LP_ADDON_DIR . '/*', GLOB_ONLYDIR)))
			return [];

		return array_map(fn($item): string => basename($item), $dirs);
	}

	public function prepareAssets(): Addon
	{
		$assets = [];

		$this->run('prepareAssets', [&$assets]);

		if (empty($assets))
			return $this;

		foreach (['css', 'scripts'] as $type) {
			if (! isset($assets[$type]))
				continue;

			foreach ($assets[$type] as $plugin => $links) {
				$addonAssetDir = $this->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR  . 'light_portal' . DIRECTORY_SEPARATOR . $plugin;

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

		return $this;
	}

	/**
	 * @see https://dragomano.github.io/Light-Portal/plugins/all_hooks
	 */
	public function run(string $hook = '', array $vars = [], array $plugins = [])
	{
		static $results = [], $settings = [];

		$addons = $plugins ?: $this->context['lp_enabled_plugins'];

		if (empty($addons) || isset($this->context['uninstalling']))
			return;

		if (empty($settings))
			$settings = $this->getSettings();

		foreach ($addons as $addon) {
			$className = __NAMESPACE__ . '\Addons\\' . $addon . '\\' . $addon;

			if (! class_exists($className))
				continue;

			$class = new $className;

			$snakeName = $this->getSnakeName($addon);

			if (empty($this->chest[$snakeName])) {
				$this->chest[$snakeName] = [
					'name' => $addon,
					'icon' => $class->icon,
					'type' => $class->type,
				];

				$path = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR;

				$this->loadLanguage($path, $snakeName);
				$this->loadCss($path, $snakeName);
				$this->loadJs($path, $snakeName);

				$this->context['lp_' . $snakeName . '_plugin'] = $settings[$snakeName] ?? [];
			}

			// Hook init should run only once
			if (empty($results[$addon]) && method_exists($class, 'init') && in_array($addon, $this->context['lp_enabled_plugins'])) {
				$class->init();
				$results[$addon] = true;
			}

			if (method_exists($class, $hook)) {
				$class->$hook(...$vars);
			}
		}

		$this->context['lp_loaded_addons'] = $this->chest;
	}

	private function loadLanguage(string $path, string $snakeName)
	{
		if (isset($this->txt['lp_' . $snakeName]))
			return;

		$languages = array_unique(['english', $this->user_info['language']]);

		$addonLanguages = [];
		foreach ($languages as $lang) {
			$langFile = $path . 'langs' . DIRECTORY_SEPARATOR . $lang . '.php';
			$addonLanguages[$lang] = is_file($langFile) ? require_once $langFile : [];
		}

		if (is_array($addonLanguages['english']))
			$this->txt['lp_' . $snakeName] = array_merge($addonLanguages['english'], $addonLanguages[$this->user_info['language']]);
	}

	private function loadCss(string $path, string $snakeName)
	{
		if (! is_file($style = $path . 'style.css'))
			return;

		$addonCss = $this->settings['default_theme_dir'] . '/css/light_portal/addon_' . $snakeName . '.css';

		$isFileExists = true;
		if (! is_file($addonCss) || filemtime($style) > filemtime($addonCss))
			$isFileExists = @copy($style, $addonCss);

		if (! @is_writable($this->settings['default_theme_dir'] . '/css/light_portal') || ! $isFileExists)
			return;

		loadCSSFile('light_portal/addon_' . $snakeName . '.css');
	}

	private function loadJs(string $path, string $snakeName)
	{
		if (! is_file($script = $path . 'script.js'))
			return;

		$addonJs = $this->settings['default_theme_dir'] . '/scripts/light_portal/addon_' . $snakeName . '.js';

		$isFileExists = true;
		if (! is_file($addonJs) || filemtime($script) > filemtime($addonJs))
			$isFileExists = @copy($script, $addonJs);

		if (! @is_writable($this->settings['default_theme_dir'] . '/scripts/light_portal') || ! $isFileExists)
			return;

		loadJavaScriptFile('light_portal/addon_' . $snakeName . '.js');
	}

	private function getSettings(): array
	{
		if (($settings = $this->cache()->get('plugin_settings', 259200)) === null) {
			$request = $this->smcFunc['db_query']('', '
				SELECT name, option, value
				FROM {db_prefix}lp_plugins',
				[]
			);

			$settings = [];
			while ($row = $this->smcFunc['db_fetch_assoc']($request))
				$settings[$row['name']][$row['option']] = $row['value'];

			$this->smcFunc['db_free_result']($request);
			$this->context['lp_num_queries']++;

			$this->cache()->put('plugin_settings', $settings, 259200);
		}

		return $settings;
	}
}
