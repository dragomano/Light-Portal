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
 * @version 29.12.23
 */

namespace Bugo\LightPortal\Addons\TwigLayouts;

use Bugo\LightPortal\Addons\Plugin;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Error\Error;
use Twig\TwigFunction;

if (! defined('LP_NAME'))
	die('No direct access...');

class TwigLayouts extends Plugin
{
	public string $type = 'frontpage';

	public bool $saveable = false;

	private string $extension = '.twig';

	public function addSettings(array &$config_vars): void
	{
		$this->txt['lp_twig_layouts']['note'] = sprintf(
			$this->txt['lp_twig_layouts']['note'],
			$this->extension,
			$this->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . 'portal_layouts'
		);

		$config_vars['twig_layouts'][] = ['desc', 'note'];
		$config_vars['twig_layouts'][] = ['title', 'example'];
		$config_vars['twig_layouts'][] = ['callback', '_', $this->showExample()];
	}

	public function frontLayouts(): void
	{
		if (! str_contains($this->modSettings['lp_frontpage_layout'], $this->extension))
			return;

		require_once __DIR__ . '/vendor/autoload.php';

		$params = [
			'txt'         => $this->txt,
			'context'     => $this->context,
			'modSettings' => $this->modSettings,
		];

		ob_start();

		try {
			$loader = new FilesystemLoader($this->settings['default_theme_dir'] . '/portal_layouts');

			$twig = new Environment($loader, [
				'cache' => empty($this->modSettings['cache_enable']) ? false : $this->cachedir,
				'debug' => false
			]);

			$twig->addExtension(new DebugExtension());

			$show_pagination_function = new TwigFunction('show_pagination', function (string $position = 'top') {
				show_pagination($position);
			});
			$twig->addFunction($show_pagination_function);

			$debug_function = new TwigFunction('debug', function (mixed $data) {
				echo parse_bbc('[code]' . print_r($data, true) . '[/code]');
			});
			$twig->addFunction($debug_function);

			echo $twig->render($this->modSettings['lp_frontpage_layout'], $params);
		} catch (Error $e) {
			$this->fatalError($e->getMessage());
		}

		$this->context['lp_layout'] = ob_get_clean();

		$this->modSettings['lp_frontpage_layout'] = '';
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
		return '<div class="roundframe">' . $this->parseBbc('[php]' . file_get_contents(__DIR__. '/layouts/example' . $this->extension) . '[/php]') . '</div>';
	}
}
