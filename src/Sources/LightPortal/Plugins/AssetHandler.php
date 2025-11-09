<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Plugins;

use Bugo\Compat\Theme;
use Bugo\Compat\WebFetch\WebFetchApi;
use LightPortal\Utils\Setting;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

if (! defined('LP_NAME'))
	die('No direct access...');

class AssetHandler
{
	private readonly CSS $css;

	private readonly JS $js;

	private int $maxCssFilemtime = 0;

	private int $maxJsFilemtime = 0;

	public function __construct()
	{
		$this->css = new CSS();
		$this->js  = new JS();
	}

	public function prepare(array $assets): void
	{
		$themeDir = Theme::$current->settings['default_theme_dir'];

		foreach (['css', 'scripts', 'images'] as $type) {
			if (empty($assets[$type]))
				continue;

			$parentDir = $themeDir . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . 'light_portal';

			$this->makeDir($parentDir);

			foreach ($assets[$type] as $snakeName => $links) {
				if (empty($assets[$type][$snakeName]))
					continue;

				$directory = $parentDir . DIRECTORY_SEPARATOR . $snakeName;

				$this->makeDir($directory);

				foreach ($links as $link) {
					$this->downloadAsset($directory, $link);
				}
			}
		}
	}

	public function handle(string $path, string $pluginName): void
	{
		$assets = [
			'css' => $path . 'style.css',
			'js'  => $path . 'script.js',
		];

		foreach ($assets as $type => $file) {
			if (! is_file($file))
				continue;

			if (in_array($pluginName, Setting::getEnabledPlugins())) {
				$this->{$type}->add($file);
			}

			$this->updateMaxFilemtime($type, $file);
		}
	}

	public function minify(): void
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

	private function makeDir(string $path): void
	{
		if (! is_dir($path)) {
			@mkdir($path, 0755, true);
			@copy(__DIR__ . '/index.php', $path . '/index.php');
		}
	}

	private function downloadAsset(string $directory, string $link): void
	{
		$filename = $directory . DIRECTORY_SEPARATOR . basename($link);

		if (is_file($filename))
			return;

		file_put_contents($filename, WebFetchApi::fetch($link), LOCK_EX);
	}

	private function updateMaxFilemtime(string $type, string $file): void
	{
		$maxFilemtime = filemtime($file);
		$property = 'max' . ucfirst($type) . 'Filemtime';

		if ($maxFilemtime > $this->{$property}) {
			$this->{$property} = $maxFilemtime;
		}
	}

	private function minifyFile(string $filePath, int $maxFilemtime, callable $minifyFunction): void
	{
		if (! is_file($filePath) || $maxFilemtime > filemtime($filePath)) {
			$minifyFunction($filePath);
		}
	}
}
