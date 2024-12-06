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

use Bugo\Compat\Theme;
use Bugo\Compat\WebFetchApi;
use Bugo\LightPortal\Utils\Setting;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

use function basename;
use function file_put_contents;
use function filemtime;
use function is_dir;
use function is_file;
use function mkdir;
use function ucfirst;

use const DIRECTORY_SEPARATOR;
use const LOCK_EX;

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
		foreach (['css', 'scripts', 'images'] as $type) {
			if (empty($assets[$type]))
				continue;

			foreach ($assets[$type] as $snakeName => $links) {
				if (empty($snakeName))
					continue;

				$directory = $type . '/light_portal/' . $snakeName;
				$path = Theme::$current->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . $directory;

				if (! is_dir($path)) {
					@mkdir($path);
				}

				foreach ($links as $link) {
					if (is_file($filename = $path . DIRECTORY_SEPARATOR . basename((string) $link)))
						continue;

					file_put_contents($filename, WebFetchApi::fetch($link), LOCK_EX);
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

			if (($maxFilemtime = filemtime($file)) > $this->{'max' . ucfirst($type) . 'Filemtime'}) {
				$this->{'max' . ucfirst($type) . 'Filemtime'} = $maxFilemtime;
			}
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

	private function minifyFile(string $filePath, int $maxFilemtime, callable $minifyFunction): void
	{
		if (! is_file($filePath) || $maxFilemtime > filemtime($filePath)) {
			$minifyFunction($filePath);
		}
	}
}
