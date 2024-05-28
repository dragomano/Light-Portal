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
 * @version 2.6
 */

namespace Bugo\LightPortal;

use Bugo\Compat\{Lang, Theme, User, Utils, WebFetchApi};
use Bugo\LightPortal\Addons\AddonManagerAwareInterface;
use Bugo\LightPortal\Addons\AddonManagerAwareTrait;

use Bugo\LightPortal\Utils\Language;
use MatthiasMullie\Minify\{CSS, JS};
use SplObjectStorage;

if (! defined('SMF'))
	die('No direct access...');

final class AddonHandler implements AddonManagerAwareInterface
{
	use AddonManagerAwareTrait;
	use Helper;

	private SplObjectStorage $storage;

	private int $maxCssFilemtime = 0;

	private int $maxJsFilemtime = 0;

	private static self $instance;

	private string $prefix = 'lp_';

	public function __construct(
		private array $settings,
		private CSS $css = new CSS,
		private JS $js   = new JS,
	) {
		//$this->settings = (new PluginRepository())->getSettings(); // move to factory

		$this->storage = $this->getStorage();

		$this->prepareAssets();
	}

	// public static function getInstance(): self
	// {
	// 	return $this;
	// 	// if (empty(self::$instance)) {
	// 	// 	self::$instance = new self();
	// 	// }

	// 	// return self::$instance;
	// }

	public function getAll(): array
	{
		if (empty($dirs = glob(LP_ADDON_DIR . '/*', GLOB_ONLYDIR)))
			return [];

		return array_map(static fn($item): string => basename($item), $dirs);
	}

	/**
	 * run replacement
	 * @internal
	 */
	private function addon(string $name, ?array $options = null)
	{
		return $this->addonManager->get($name, $options);
	}

	public function __call($method, $argv)
	{
		$addon = $this->addon($method);
		if (is_callable($addon)) {
			// hint, addons should implement __invoke()
			return $addon($argv);
		}
		// if its not callable just return the instance
		return $addon;
	}

	/**
	 * This entire method will be refactored so that it can call Addons directly from the container
	 * The implication here is that if they are present then they are enabled.
	 * @param string $hook
	 * @param array $vars
	 * @param array $plugins
	 * @return void
	 */
	public function run(string $hook = 'init', array $vars = [], array $plugins = []): void
	{
		$addons = $plugins ?: Utils::$context['lp_enabled_plugins'] ?? [];

		if (empty($addons) || isset(Utils::$context['uninstalling']))
			return;

		foreach ($addons as $addon) {
			$className = __NAMESPACE__ . '\Addons\\' . $addon . '\\' . $addon;

			if (! class_exists($className))
				continue;

			$class = new $className();

			/**
			 * object storage will be absolete, since this the job of AddonManager
			 * Im not exactly sure how you are using this currently, but I would think that
			 * AbstractAddon would have properties for icon and type.... I have seen type used,
			 * but not sure if that represents an Addons "type"
			 */
			if (! $this->storage->contains($class)) {
				$this->storage->attach($class, [
					'name' => $addon,
					'icon' => $class->icon,
					'type' => $class->type,
				]);

				// we no longer need their paths since we already have them in the AddonManager
				$path = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR;
				$snakeName = $this->getSnakeName($addon);

				// If an  addons ConfigProvider is found, then it will be loaded, hence if they are present..
				Utils::$context[$this->prefix . $snakeName . '_plugin'] = $this->settings[$snakeName] ?? [];
				Utils::$context['lp_loaded_addons'][$snakeName] = $this->storage->offsetGet($class);

				// loadLangs possibly needs to become a published event... Not sure bout that.
				$this->loadLangs($path, $snakeName);
				// I can definitely see this being a published event
				$this->loadAssets($path, $addon);
			}

			/**
			 * @bugo
			 * this will work through __call so that you just call it on the $this->addonHandler->addonAlias();
			 * The AddonManager will handle calling the init method via an inititalizer or delegator
			 * based on an InitializableInterface which will expose a single method init() though I am not sure
			 * what implications that has for the overall application. I will need to dive deeper into
			 * this part of the workflow.
			 *
			 * Honestly, this $class->$hook stuff is really on my nerve :P
			 */
			if (method_exists($class, $hook)) {
				$hook === 'init' && in_array($addon, Utils::$context['lp_enabled_plugins'])
					? $class->init()
					: $class->$hook(...$vars);
			}
		}
		// no idea why we have a random call to $this->minify() here, I mean really....
		$this->minify();
	}

	private function prepareAssets(array $assets = []): void
	{
		$this->run('prepareAssets', [&$assets]);

		foreach (['css', 'scripts', 'images'] as $type) {
			if (! isset($assets[$type]))
				continue;

			foreach ($assets[$type] as $plugin => $links) {
				$customName = $type . '/light_portal/' . $plugin;
				$addonAssetDir = Theme::$current->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . $customName;

				if (! is_dir($addonAssetDir)) {
					@mkdir($addonAssetDir);
				}

				foreach ($links as $link) {
					if (is_file($filename = $addonAssetDir . DIRECTORY_SEPARATOR . basename((string) $link)))
						continue;

					file_put_contents($filename, WebFetchApi::fetch($link), LOCK_EX);
				}
			}
		}
	}

	private function loadLangs(string $path, string $snakeName): void
	{
		if (isset(Lang::$txt[$this->prefix . $snakeName]))
			return;

		$userLang  = Language::getNameFromLocale(User::$info['language']);
		$languages = array_unique(['english', $userLang]);

		$addonLanguages = [];
		foreach ($languages as $lang) {
			$langFile = $path . 'langs' . DIRECTORY_SEPARATOR . $lang . '.php';
			$addonLanguages[$lang] = is_file($langFile) ? require_once $langFile : [];
		}

		if (is_array($addonLanguages['english'])) {
			Lang::$txt[$this->prefix . $snakeName] = array_merge(
				$addonLanguages['english'], $addonLanguages[$userLang]
			);
		}
	}

	private function loadAssets(string $path, string $addon): void
	{
		$assets = [
			'css' => $path . 'style.css',
			'js'  => $path . 'script.js',
		];

		foreach ($assets as $type => $file) {
			if (! is_file($file))
				continue;

			if (in_array($addon, Utils::$context['lp_enabled_plugins'])) {
				$this->{$type}->add($file);
			}

			if (($maxFilemtime = filemtime($file)) > $this->{'max' . ucfirst($type) . 'Filemtime'}) {
				$this->{'max' . ucfirst($type) . 'Filemtime'} = $maxFilemtime;
			}
		}
	}

	private function minify(): void
	{
		$cssFile = Theme::$current->settings['default_theme_dir'] . '/css/light_portal/plugins.css';
		if (! is_file($cssFile) || $this->maxCssFilemtime > filemtime($cssFile)) {
			$this->css->minify($cssFile);
		}

		$jsFile = Theme::$current->settings['default_theme_dir'] . '/scripts/light_portal/plugins.js';
		if (! is_file($jsFile) || $this->maxJsFilemtime > filemtime($jsFile)) {
			$this->js->minify($jsFile);
		}
	}

	private function getStorage(): SplObjectStorage
	{
		return new class extends SplObjectStorage
		{
			public function getHash($object): string
			{
				return $object::class;
			}
		};
	}
}
