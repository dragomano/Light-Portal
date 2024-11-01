<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal;

use Bugo\Compat\{Lang, Theme, User, Utils, WebFetchApi};
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Utils\{Language, Setting, Str};
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
use function is_dir;
use function is_file;
use function method_exists;
use function mkdir;
use function ucfirst;

use const DIRECTORY_SEPARATOR;
use const GLOB_ONLYDIR;
use const LOCK_EX;
use const LP_ADDON_DIR;

if (! defined('SMF'))
	die('No direct access...');

final class AddonHandler
{
	private array $settings;

	private readonly SplObjectStorage $storage;

	private readonly CSS $css;

	private readonly JS $js;

	private int $maxCssFilemtime = 0;

	private int $maxJsFilemtime = 0;

	private static self $instance;

	private const PREFIX = 'lp_';

	public static function getInstance(array $plugins = []): self
	{
		if (empty(self::$instance) || $plugins) {
			self::$instance = new self($plugins);
		}

		return self::$instance;
	}

	public function getAll(): array
	{
		if (empty($dirs = glob(LP_ADDON_DIR . '/*', GLOB_ONLYDIR)))
			return [];

		return array_map(static fn($item): string => basename($item), $dirs);
	}

	public function run(PortalHook $hook = PortalHook::init, array $vars = []): void
	{
		$hookName = $hook->name;

        foreach ($this->storage as $class) {
			if (method_exists($class, $hookName)) {
				$hookName === PortalHook::init && in_array($class->getName(), Setting::getEnabledPlugins())
					? $class->init()
					: $class->$hookName(...$vars);
			}
        }
	}

	private function prepareAssets(array $assets = []): void
	{
		$this->run(PortalHook::prepareAssets, [&$assets]);

		foreach (['css', 'scripts', 'images'] as $type) {
			if (! isset($assets[$type]))
				continue;

			foreach ($assets[$type] as $plugin => $links) {
				$customName = $type . '/light_portal/' . $plugin;
				$pluginAssetDir = Theme::$current->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . $customName;

				if (! is_dir($pluginAssetDir)) {
					@mkdir($pluginAssetDir);
				}

				foreach ($links as $link) {
					if (is_file($filename = $pluginAssetDir . DIRECTORY_SEPARATOR . basename((string) $link)))
						continue;

					file_put_contents($filename, WebFetchApi::fetch($link), LOCK_EX);
				}
			}
		}
	}

	private function loadLangs(string $path, string $snakeName): void
	{
		if (isset(Lang::$txt[self::PREFIX . $snakeName]))
			return;

		$userLang  = Language::getNameFromLocale(User::$info['language']);
		$languages = array_unique([Language::FALLBACK, $userLang]);

		Lang::$txt[self::PREFIX . $snakeName] = array_merge(
			...array_map(function ($lang) use ($path) {
				$langFile = $path . 'langs' . DIRECTORY_SEPARATOR . $lang . '.php';
				return is_file($langFile) ? require $langFile : [];
			}, $languages)
		);
	}

	private function loadAssets(string $path, string $plugin): void
	{
		$assets = [
			'css' => $path . 'style.css',
			'js'  => $path . 'script.js',
		];

		foreach ($assets as $type => $file) {
			if (! is_file($file))
				continue;

			if (in_array($plugin, Setting::getEnabledPlugins())) {
				$this->{$type}->add($file);
			}

			if (($maxFilemtime = filemtime($file)) > $this->{'max' . ucfirst($type) . 'Filemtime'}) {
				$this->{'max' . ucfirst($type) . 'Filemtime'} = $maxFilemtime;
			}
		}
	}

	private function minifyAssets(): void
	{
		$this->minifyFile(
			Theme::$current->settings['default_theme_dir'] . '/css/light_portal/plugins.css',
			$this->maxCssFilemtime,
			[$this->css, 'minify']
		);

		$this->minifyFile(
			Theme::$current->settings['default_theme_dir'] . '/scripts/light_portal/plugins.js',
			$this->maxJsFilemtime,
			[$this->js, 'minify']
		);
	}

	private function minifyFile(string $filePath, int $maxFilemtime, callable $minifyFunction): void
	{
		if (! is_file($filePath) || $maxFilemtime > filemtime($filePath)) {
			$minifyFunction($filePath);
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

	private function prepareList(array $plugins = [])
	{
		$plugins = $plugins ?: Setting::getEnabledPlugins();

		if ($plugins === [] || isset(Utils::$context['uninstalling']))
			return;

		foreach ($plugins as $plugin) {
			$className = __NAMESPACE__ . "\\Plugins\\$plugin\\$plugin";

			if (! class_exists($className))
				continue;

			$class = new $className();

			if (! $this->storage->contains($class)) {
				$this->storage->attach($class, [
					'name' => $plugin,
					'icon' => $class->icon,
					'type' => $class->type,
				]);

				$path = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR;
				$snakeName = Str::getSnakeName($plugin);

				Utils::$context[self::PREFIX . $snakeName . '_plugin'] = $this->settings[$snakeName] ?? [];
				Utils::$context['lp_loaded_addons'][$snakeName] = $this->storage->offsetGet($class);

				$this->loadLangs($path, $snakeName);
				$this->loadAssets($path, $plugin);
			}
		}
	}

	private function __construct(array $plugins = [])
	{
		$this->settings = (new PluginRepository())->getSettings();
		$this->storage = $this->getStorage();

		$this->css = new CSS();
		$this->js  = new JS();

		$this->prepareAssets();
		$this->minifyAssets();

		$this->prepareList($plugins);
	}
}
