<?php declare(strict_types=1);

/**
 * AddonHandler.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal;

use SplObjectStorage;
use Bugo\LightPortal\Repositories\PluginRepository;

if (! defined('SMF'))
	die('No direct access...');

class PluginStorage extends SplObjectStorage
{
	public function getHash($object): string
	{
		return get_class($object);
	}
}

final class AddonHandler
{
	use Helper;

	private array $pluginSettings;

	private PluginStorage $plugins;

	private static self $instance;

	private function __construct()
	{
		$this->pluginSettings = (new PluginRepository())->getSettings();
		$this->plugins = new PluginStorage();

		$this->prepareAssets();
	}

	private function __clone() {}

	public function __wakeup() {}

	public static function getInstance(): self
	{
		if (empty(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function getAll(): array
	{
		if (empty($dirs = glob(LP_ADDON_DIR . '/*', GLOB_ONLYDIR)))
			return [];

		return array_map(fn($item): string => basename($item), $dirs);
	}

	/**
	 * @see https://dragomano.github.io/Light-Portal/plugins/all_hooks
	 */
	public function run(string $hook = 'init', array $vars = [], array $plugins = []): void
	{
		$addons = $plugins ?: $this->context['lp_enabled_plugins'];

		if (empty($addons) || isset($this->context['uninstalling']))
			return;

		foreach ($addons as $addon) {
			$className = __NAMESPACE__ . '\Addons\\' . $addon . '\\' . $addon;

			if (! class_exists($className))
				continue;

			$class = new $className;

			if (! $this->plugins->contains($class)) {
				$this->plugins->attach($class, [
					'name' => $addon,
					'icon' => $class->icon,
					'type' => $class->type,
				]);

				$path = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR;
				$snakeName = $this->getSnakeName($addon);

				$this->loadLang($path, $snakeName);
				$this->loadCSS($path, $snakeName);
				$this->loadJS($path, $snakeName);

				$this->context['lp_' . $snakeName . '_plugin'] = $this->pluginSettings[$snakeName] ?? [];
				$this->context['lp_loaded_addons'][$snakeName] = $this->plugins->offsetGet($class);
			}

			if (method_exists($class, $hook)) {
				$hook === 'init' && in_array($addon, $this->context['lp_enabled_plugins']) ? $class->init() : $class->$hook(...$vars);
			}
		}
	}

	private function prepareAssets(): void
	{
		$assets = [];

		$this->run('prepareAssets', [&$assets]);

		if (empty($assets))
			return;

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

					file_put_contents($filename, $this->fetchWebData($link), LOCK_EX);
				}
			}
		}
	}

	private function loadLang(string $path, string $snakeName): void
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

	private function loadCSS(string $path, string $snakeName): void
	{
		if (! is_file($style = $path . 'style.css'))
			return;

		$addonCss = $this->settings['default_theme_dir'] . '/css/light_portal/addon_' . $snakeName . '.css';

		$isFileExists = true;
		if (! is_file($addonCss) || filemtime($style) > filemtime($addonCss))
			$isFileExists = @copy($style, $addonCss);

		if (! @is_writable($this->settings['default_theme_dir'] . '/css/light_portal') || ! $isFileExists)
			return;

		$this->loadCSSFile('light_portal/addon_' . $snakeName . '.css');
	}

	private function loadJS(string $path, string $snakeName): void
	{
		if (! is_file($script = $path . 'script.js'))
			return;

		$addonJs = $this->settings['default_theme_dir'] . '/scripts/light_portal/addon_' . $snakeName . '.js';

		$isFileExists = true;
		if (! is_file($addonJs) || filemtime($script) > filemtime($addonJs))
			$isFileExists = @copy($script, $addonJs);

		if (! @is_writable($this->settings['default_theme_dir'] . '/scripts/light_portal') || ! $isFileExists)
			return;

		$this->loadJavaScriptFile('light_portal/addon_' . $snakeName . '.js');
	}
}
