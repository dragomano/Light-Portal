<?php

/**
 * TwigLayouts.php
 *
 * @package TwigLayouts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category addon
 * @version 01.02.24
 */

namespace Bugo\LightPortal\Addons\TwigLayouts;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Utils\{BBCodeParser, Config, ErrorHandler, Icon, Lang, Theme, Utils};
use Twig\{Loader\FilesystemLoader, Environment, Error\Error, TwigFunction};

if (! defined('LP_NAME'))
	die('No direct access...');

class TwigLayouts extends Plugin
{
	public string $type = 'frontpage';

	public bool $saveable = false;

	private string $extension = '.twig';

	public function addSettings(array &$config_vars): void
	{
		Lang::$txt['lp_twig_layouts']['note'] = sprintf(
			Lang::$txt['lp_twig_layouts']['note'],
			$this->extension,
			Theme::$current->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . 'portal_layouts'
		);

		$config_vars['twig_layouts'][] = ['desc', 'note'];
		$config_vars['twig_layouts'][] = ['title', 'example'];
		$config_vars['twig_layouts'][] = ['callback', '_', $this->showExample()];
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

		try {
			$loader = new FilesystemLoader(Theme::$current->settings['default_theme_dir'] . '/portal_layouts');

			$twig = new Environment($loader, [
				'cache' => empty(Config::$modSettings['cache_enable']) ? false : Sapi::getTempDir(),
				'debug' => false
			]);

			$twig->addFunction(new TwigFunction('show_pagination', function (string $position = 'top') {
				show_pagination($position);
			}));

			$twig->addFunction(new TwigFunction('icon', function (string $name, string $title = '') {
				$icon = Icon::get($name);

				if (empty($title)) {
					echo $icon;
					return;
				}

				echo str_replace(' class=', ' title="' . $title . '" class=', $icon);
			}));

			$twig->addFunction(new TwigFunction('debug', function (mixed $data) {
				echo parse_bbc('[code]' . print_r($data, true) . '[/code]');
			}));

			echo $twig->render(Config::$modSettings['lp_frontpage_layout'], $params);
		} catch (Error $e) {
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
		$links[] = [
			'title' => 'Twig',
			'link' => 'https://github.com/twigphp/Twig',
			'author' => 'Twig Team',
			'license' => [
				'name' => 'the BSD-3-Clause',
				'link' => 'https://github.com/twigphp/Twig/blob/3.x/LICENSE'
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
