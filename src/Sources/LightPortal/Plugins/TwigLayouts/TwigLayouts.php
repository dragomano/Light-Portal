<?php

/**
 * @package TwigLayouts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 18.11.24
 */

namespace Bugo\LightPortal\Plugins\TwigLayouts;

use Bugo\Compat\{Config, ErrorHandler};
use Bugo\Compat\{Lang, Sapi, Theme, Utils};
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Utils\{Icon, Str};
use Twig\{Environment, Error\Error};
use Twig\{Loader\FilesystemLoader, TwigFunction};

if (! defined('LP_NAME'))
	die('No direct access...');

class TwigLayouts extends Plugin
{
	public string $type = 'frontpage';

	public bool $saveable = false;

	private string $extension = '.twig';

	public function addSettings(Event $e): void
	{
		Lang::$txt['lp_twig_layouts']['note'] = sprintf(
			Lang::$txt['lp_twig_layouts']['note'],
			$this->extension,
			Theme::$current->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . 'portal_layouts'
		);

		$e->args->settings[$this->name][] = ['desc', 'note'];
		$e->args->settings[$this->name][] = ['title', 'example'];
		$e->args->settings[$this->name][] = ['callback', '_', $this->showExamples()];
	}

	public function frontLayouts(): void
	{
		if (! str_contains((string) Config::$modSettings['lp_frontpage_layout'], $this->extension))
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

			$twig->addFunction(new TwigFunction('show_pagination', static function (string $position = 'top') {
				show_pagination($position);
			}));

			$twig->addFunction(new TwigFunction('icon', static function (string $name, string $title = '') {
				$icon = Icon::get($name);

				if (empty($title)) {
					echo $icon;
					return;
				}

				echo str_replace(' class=', ' title="' . $title . '" class=', $icon);
			}));

			$twig->addFunction(new TwigFunction('debug', static function (mixed $data) {
				echo parse_bbc('[code]' . print_r($data, true) . '[/code]');
			}));

			echo $twig->render(Config::$modSettings['lp_frontpage_layout'], $params);
		} catch (Error $e) {
			ErrorHandler::fatal($e->getMessage());
		}

		Utils::$context['lp_layout'] = ob_get_clean();

		Config::$modSettings['lp_frontpage_layout'] = '';
	}

	public function customLayoutExtensions(Event $e): void
	{
		$e->args->extensions[] = $this->extension;
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'Twig',
			'link' => 'https://github.com/twigphp/Twig',
			'author' => 'Twig Team',
			'license' => [
				'name' => 'the BSD-3-Clause',
				'link' => 'https://github.com/twigphp/Twig/blob/3.x/LICENSE'
			]
		];
	}

	private function showExamples(): string
	{
		$examples = glob(__DIR__ . '/layouts/*' . $this->extension);

		$list = Str::html('ul', ['class' => 'bbc_list']);

		foreach ($examples as $file) {
			$file = basename($file);
			$list->addHtml(
				Str::html('li')->setHtml(
					Str::html('a', $file)->href(LP_ADDON_URL . '/TwigLayouts/layouts/' . $file)
				)
			);
		}

		return Str::html('div', ['class' => 'roundframe'])
			->setHtml($list);
	}
}
