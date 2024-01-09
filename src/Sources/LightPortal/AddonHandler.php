<?php declare(strict_types=1);

/**
 * AddonHandler.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Repositories\PluginRepository;
use MatthiasMullie\Minify\{CSS, JS};
use SplObjectStorage;

if (! defined('SMF'))
	die('No direct access...');

class PluginStorage extends SplObjectStorage
{
	public function getHash($object): string
	{
		return $object::class;
	}
}

final class AddonHandler
{
	use Helper;

	private array $pluginSettings;

	private PluginStorage $plugins;

	private CSS $cssMinifier;

	private JS $jsMinifier;

	private int $maxCssFilemtime = 0;

	private int $maxJsFilemtime = 0;

	private static self $instance;

	private string $prefix = 'lp_';

	private function __construct()
	{
		$this->pluginSettings = (new PluginRepository())->getSettings();

		$this->plugins = new PluginStorage();

		$this->cssMinifier = new CSS;

		$this->jsMinifier = new JS;

		$this->prepareAssets();
	}

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

				$this->context[$this->prefix . $snakeName . '_plugin'] = $this->pluginSettings[$snakeName] ?? [];
				$this->context['lp_loaded_addons'][$snakeName] = $this->plugins->offsetGet($class);

				$this->loadLangs($path, $snakeName);

				if (in_array($addon, $this->context['lp_enabled_plugins']))
					$this->loadAssets($path);
			}

			if (method_exists($class, $hook)) {
				$hook === 'init' && in_array($addon, $this->context['lp_enabled_plugins']) ? $class->init() : $class->$hook(...$vars);
			}
		}

		$cssFile = $this->settings['default_theme_dir'] . '/css/light_portal/plugins.css';
		if (! is_file($cssFile) || $this->maxCssFilemtime > filemtime($cssFile)) {
			$this->cssMinifier->minify($cssFile);
		}

		$jsFile = $this->settings['default_theme_dir'] . '/scripts/light_portal/plugins.js';
		if (! is_file($jsFile) || $this->maxJsFilemtime > filemtime($jsFile)) {
			$this->jsMinifier->minify($jsFile);
		}
	}

	private function prepareAssets(array $assets = []): void
	{
		$this->run('prepareAssets', [&$assets]);

		foreach (['css', 'scripts', 'images'] as $type) {
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

	private function loadLangs(string $path, string $snakeName): void
	{
		if (isset($this->txt[$this->prefix . $snakeName]))
			return;

		$languages = array_unique(['english', $this->user_info['language']]);

		$addonLanguages = [];
		foreach ($languages as $lang) {
			$langFile = $path . 'langs' . DIRECTORY_SEPARATOR . $lang . '.php';
			$addonLanguages[$lang] = is_file($langFile) ? require_once $langFile : [];
		}

		if (is_array($addonLanguages['english']))
			$this->txt[$this->prefix . $snakeName] = array_merge($addonLanguages['english'], $addonLanguages[$this->user_info['language']]);
	}

	private function loadAssets(string $path): void
	{
		$assets = [
			'css' => $path . 'style.css',
			'js'  => $path . 'script.js',
		];

		foreach ($assets as $type => $file) {
			if (! is_file($file)) {
				continue;
			}

			$this->{$type . 'Minifier'}->add($file);

			if (($maxFilemtime = filemtime($file)) > $this->{'max' . ucfirst($type) . 'Filemtime'})
				$this->{'max' . ucfirst($type) . 'Filemtime'} = $maxFilemtime;
		}
	}
}
