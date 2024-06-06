<?php declare(strict_types=1);

/**
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
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Utils\{Language, Str};
use MatthiasMullie\Minify\{CSS, JS};
use SplObjectStorage;

use function array_map;
use function array_merge;
use function array_unique;
use function basename;
use function class_exists;
use function file_put_contents;
use function filemtime;
use function glob;
use function in_array;
use function is_array;
use function is_dir;
use function is_file;
use function method_exists;
use function mkdir;
use function ucfirst;

use const GLOB_ONLYDIR;
use const LP_ADDON_DIR;

if (! defined('SMF'))
	die('No direct access...');

final class AddonHandler
{
	private array $settings;

	private SplObjectStorage $storage;

	private CSS $css;

	private JS $js;

	private int $maxCssFilemtime = 0;

	private int $maxJsFilemtime = 0;

	private static self $instance;

	private string $prefix = 'lp_';

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

		return array_map(static fn($item): string => basename($item), $dirs);
	}

	public function run(PortalHook $hook = PortalHook::init, array $vars = [], array $plugins = []): void
	{
		$hook = $hook->name;

		$addons = $plugins ?: Utils::$context['lp_enabled_plugins'] ?? [];

		if (empty($addons) || isset(Utils::$context['uninstalling']))
			return;

		foreach ($addons as $addon) {
			$className = __NAMESPACE__ . '\Addons\\' . $addon . '\\' . $addon;

			if (! class_exists($className))
				continue;

			$class = new $className();

			if (! $this->storage->contains($class)) {
				$this->storage->attach($class, [
					'name' => $addon,
					'icon' => $class->icon,
					'type' => $class->type,
				]);

				$path = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR;
				$snakeName = Str::getSnakeName($addon);

				Utils::$context[$this->prefix . $snakeName . '_plugin'] = $this->settings[$snakeName] ?? [];
				Utils::$context['lp_loaded_addons'][$snakeName] = $this->storage->offsetGet($class);

				$this->loadLangs($path, $snakeName);
				$this->loadAssets($path, $addon);
			}

			if (method_exists($class, $hook)) {
				$hook === PortalHook::init && in_array($addon, Utils::$context['lp_enabled_plugins'])
					? $class->init()
					: $class->$hook(...$vars);
			}
		}

		$this->minify();
	}

	private function prepareAssets(array $assets = []): void
	{
		$this->run(PortalHook::prepareAssets, [&$assets]);

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

	private function __construct()
	{
		$this->settings = (new PluginRepository())->getSettings();

		$this->storage = $this->getStorage();

		$this->css = new CSS();

		$this->js = new JS();

		$this->prepareAssets();
	}
}
