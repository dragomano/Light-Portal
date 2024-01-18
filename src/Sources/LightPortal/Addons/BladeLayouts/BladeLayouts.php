<?php

/**
 * BladeLayouts.php
 *
 * @package BladeLayouts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category addon
 * @version 18.01.24
 */

namespace Bugo\LightPortal\Addons\BladeLayouts;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Utils\{BBCodeParser, Config, ErrorHandler, Lang, Theme, Utils};
use eftec\bladeone\BladeOne;
use Exception;

if (! defined('LP_NAME'))
	die('No direct access...');

class BladeLayouts extends Plugin
{
	public string $type = 'frontpage';

	public bool $saveable = false;

	private string $extension = '.blade.php';

	public function addSettings(array &$config_vars): void
	{
		Lang::$txt['lp_blade_layouts']['note'] = sprintf(
			Lang::$txt['lp_blade_layouts']['note'],
			$this->extension,
			Theme::$current->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . 'portal_layouts'
		);

		$config_vars['blade_layouts'][] = ['desc', 'note'];
		$config_vars['blade_layouts'][] = ['title', 'example'];
		$config_vars['blade_layouts'][] = ['callback', '_', $this->showExample()];
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
			$blade = new BladeOne(Theme::$current->settings['default_theme_dir'] . '/portal_layouts', Config::$cachedir);

			$layout = strstr(Config::$modSettings['lp_frontpage_layout'], '.', true) ?: Config::$modSettings['lp_frontpage_layout'];

			echo $blade->run($layout, $params);
		} catch (Exception $e) {
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
			'title' => 'BladeOne Blade Template Engine',
			'link' => 'https://github.com/EFTEC/BladeOne',
			'author' => 'Jorge Patricio Castro Castillo',
			'license' => [
				'name' => 'The MIT License',
				'link' => 'https://github.com/EFTEC/BladeOne/blob/master/LICENSE'
			]
		];
	}

	private function showExample(): string
	{
		return '<div class="roundframe">' . BBCodeParser::load()->parse('[php]' . file_get_contents(__DIR__. '/layouts/example' . $this->extension) . '[/php]') . '</div>';
	}
}
