<?php

/**
 * LatteLayouts.php
 *
 * @package LatteLayouts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category addon
 * @version 13.02.24
 */

namespace Bugo\LightPortal\Addons\LatteLayouts;

use Bugo\Compat\{BBCodeParser, Config, ErrorHandler};
use Bugo\Compat\{Lang, Sapi, Theme, Utils};
use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Utils\Icon;
use Exception;
use Latte\Engine;
use Latte\Essential\RawPhpExtension;
use Latte\Loaders\FileLoader;
use Latte\Runtime\Html;
use Latte\RuntimeException;

if (! defined('LP_NAME'))
	die('No direct access...');

class LatteLayouts extends Plugin
{
	public string $type = 'frontpage';

	public bool $saveable = false;

	private string $extension = '.latte';

	public function addSettings(array &$settings): void
	{
		Lang::$txt['lp_latte_layouts']['note'] = sprintf(
			Lang::$txt['lp_latte_layouts']['note'],
			$this->extension,
			Theme::$current->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . 'portal_layouts'
		);

		$settings['latte_layouts'][] = ['desc', 'note'];
		$settings['latte_layouts'][] = ['title', 'example'];
		$settings['latte_layouts'][] = ['callback', '_', $this->showExample()];
	}

	public function frontLayouts(): void
	{
		if (! str_contains(Config::$modSettings['lp_frontpage_layout'], $this->extension))
			return;

		require_once __DIR__ . '/vendor/autoload.php';

		$params = [
			'txt'         => Lang::$txt,
			'context'     => Utils::$context,
			'modSettings' => Config::$modSettings,
		];

		ob_start();

		$latte = new Engine;
		$latte->setTempDirectory(empty(Config::$modSettings['cache_enable']) ? null : Sapi::getTempDir());
		$latte->setLoader(new FileLoader(Theme::$current->settings['default_theme_dir'] . '/portal_layouts/'));
		$latte->addExtension(new RawPhpExtension);

		$latte->addFunction('teaser', function (string $text, int $length = 150) use ($latte): string {
			$text = $latte->invokeFilter('stripHtml', [$text]);

			return $latte->invokeFilter('truncate', [$text, $length]);
		});

		$latte->addFunction('icon', function (string $name, string $title = ''): Html {
			$icon = Icon::get($name);

			if (empty($title)) {
				return new Html($icon);
			}

			return new Html(str_replace(' class=', ' title="' . $title . '" class=', $icon));
		});

		try {
			$latte->render(Config::$modSettings['lp_frontpage_layout'], $params);
		} catch (RuntimeException | Exception $e) {
			ErrorHandler::fatal($e->getMessage());
		}

		Utils::$context['lp_layout'] = ob_get_clean();

		Config::$modSettings['lp_frontpage_layout'] = '';
	}

	public function customLayoutExtensions(array &$extensions): void
	{
		$extensions[] = $this->extension;
	}

	public function credits(array &$links): void
	{
		$links[] = 			[
			'title' => 'Latte',
			'link' => 'https://latte.nette.org',
			'author' => 'David Grudl',
			'license' => [
				'name' => 'the New BSD License',
				'link' => 'https://github.com/nette/latte/blob/master/license.md'
			]
		];
	}

	private function showExample(): string
	{
		return '<div class="roundframe">' . BBCodeParser::load()->parse(
			'[php]' . file_get_contents(__DIR__. '/layouts/example' . $this->extension) . '[/php]'
		) . '</div>';
	}
}
