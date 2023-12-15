<?php

/**
 * BladeLayouts.php
 *
 * @package BladeLayouts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category addon
 * @version 15.12.23
 */

namespace Bugo\LightPortal\Addons\BladeLayouts;

use Bugo\LightPortal\Addons\Plugin;
use eftec\bladeone\BladeOne;
use Exception;

if (! defined('LP_NAME'))
	die('No direct access...');

class BladeLayouts extends Plugin
{
	public string $type = 'frontpage';

	private string $extension = '.blade.php';

	public function addSettings(array &$config_vars): void
	{
		$this->txt['lp_blade_layouts']['note'] = sprintf(
			$this->txt['lp_blade_layouts']['note'],
			$this->extension,
			$this->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . 'portal_layouts'
		);

		$config_vars['blade_layouts'][] = ['desc', 'note'];
		$config_vars['blade_layouts'][] = ['title', 'example'];
		$config_vars['blade_layouts'][] = ['callback', '_', $this->showExample()];
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
			$blade = new BladeOne($this->settings['default_theme_dir'] . '/portal_layouts', $this->cachedir);

			$layout = strstr($this->modSettings['lp_frontpage_layout'], '.', true) ?: $this->modSettings['lp_frontpage_layout'];

			echo $blade->run($layout, $params);
		} catch (Exception $e) {
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
		return '<div class="roundframe">' . $this->parseBbc('[php]' . file_get_contents(__DIR__. '/layouts/example.blade.php') . '[/php]') . '</div>';
	}
}
