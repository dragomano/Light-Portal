<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Plugins;

use Bugo\Compat\{Lang, Theme, User, Utils, WebFetchApi};
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\EventManager;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Utils\{Language, Setting, Str};
use MatthiasMullie\Minify\{CSS, JS};

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
use function mkdir;
use function ucfirst;

use const DIRECTORY_SEPARATOR;
use const GLOB_ONLYDIR;
use const LOCK_EX;

if (! defined('LP_NAME'))
	die('No direct access...');

final class PluginHandler
{
	private array $settings;

	private PluginRegistry $registry;

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
		if (empty($dirs = glob(__DIR__ . '/*', GLOB_ONLYDIR)))
			return [];

		return array_map(static fn($item): string => basename($item), $dirs);
	}

	private function prepareAssets(): void
	{
		$assets = [];

		EventManager::getInstance()->dispatch(
			PortalHook::prepareAssets,
			new Event(new class ($assets) {
				public function __construct(public array &$assets) {}
			})
		);

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

	private function prepareListeners(array $plugins = []): void
	{
		$plugins = $plugins ?: Setting::getEnabledPlugins();

		if ($plugins === [])
			return;

		foreach ($plugins as $plugin) {
			$className = __NAMESPACE__ . "\\$plugin\\$plugin";

			if (! class_exists($className))
				continue;

			$snakeName = Str::getSnakeName($plugin);

			if (! $this->registry->has($snakeName)) {
				$path = __DIR__ . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR;

				$this->loadLangs($path, $snakeName);
				$this->loadAssets($path, $plugin);

				Utils::$context[self::PREFIX . $snakeName . '_plugin'] = $this->settings[$snakeName] ?? [];

				$class = new $className();

				EventManager::getInstance()->addListeners(PortalHook::cases(), $class);

				$this->registry->add($snakeName, [
					'name' => $plugin,
					'icon' => $class->icon,
					'type' => $class->type,
				]);

				Utils::$context['lp_loaded_addons'][$snakeName] = $this->registry->get($snakeName);
			}
		}
	}

	private function __construct(array $plugins = [])
	{
		$this->settings = (new PluginRepository())->getSettings();
		$this->registry = PluginRegistry::getInstance();

		$this->css = new CSS();
		$this->js  = new JS();

		$this->prepareAssets();
		$this->prepareListeners($plugins);
		$this->minifyAssets();
	}
}
