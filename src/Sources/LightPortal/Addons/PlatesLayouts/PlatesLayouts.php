<?php

/**
 * PlatesLayouts.php
 *
 * @package PlatesLayouts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category addon
 * @version 28.01.24
 */

namespace Bugo\LightPortal\Addons\PlatesLayouts;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Utils\{BBCodeParser, Config, ErrorHandler, Icon, Lang, Theme, Utils};
use League\Plates\Engine;
use League\Plates\Exception\TemplateNotFound;

if (! defined('LP_NAME'))
	die('No direct access...');

class PlatesLayouts extends Plugin
{
	public string $type = 'frontpage';

	public bool $saveable = false;

	private string $extension = '.tpl.php';

	public function addSettings(array &$config_vars): void
	{
		Lang::$txt['lp_plates_layouts']['note'] = sprintf(
			Lang::$txt['lp_plates_layouts']['note'],
			$this->extension,
			Theme::$current->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . 'portal_layouts'
		);

		$config_vars['plates_layouts'][] = ['desc', 'note'];
		$config_vars['plates_layouts'][] = ['title', 'example'];
		$config_vars['plates_layouts'][] = ['callback', '_', $this->showExample()];
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
			$templates = new Engine(
				Theme::$current->settings['default_theme_dir'] . '/portal_layouts', 'tpl.php'
			);

			$templates->registerFunction(
				'debug', fn(mixed $data) => parse_bbc('[code]' . print_r($data, true) . '[/code]')
			);

			$templates->registerFunction('icon', function (string $name, string $title = ''): string {
				$icon = Icon::get($name);

				if (empty($title)) {
					return $icon;
				}

				return str_replace(' class=', ' title="' . $title . '" class=', $icon);
			});

			$layout = strstr(
				Config::$modSettings['lp_frontpage_layout'], '.', true
			) ?: Config::$modSettings['lp_frontpage_layout'];

			echo $templates->render($layout, $params);
		} catch (TemplateNotFound $e) {
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
			'title' => 'Plates',
			'link' => 'https://github.com/thephpleague/plates',
			'author' => 'The League of Extraordinary Packages',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/thephpleague/plates?tab=MIT-1-ov-file#readme'
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
