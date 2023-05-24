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
 * @version 22.05.23
 */

namespace Bugo\LightPortal\Addons\BladeLayouts;

use Bugo\LightPortal\Addons\Plugin;
use eftec\bladeone\BladeOne;
use Exception;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class BladeLayouts extends Plugin
{
	public string $type = 'frontpage';

	public function addSettings(array &$config_vars): void
	{
		$this->addDefaultValues([
			'template' => 'example',
		]);

		$config_vars['blade_layouts'][] = [
			'select', 'template', $this->getLayouts(),
			'subtext' => sprintf(
				$this->txt['lp_blade_layouts']['template_subtext'],
				'.blade.php',
				$this->settings['default_theme_dir'] . '/custom_frontpage_layouts'
			)
		];
	}

	public function frontCustomTemplate(): void
	{
		require_once __DIR__ . '/vendor/autoload.php';

		$templates = [
			__DIR__ . '/layouts/',
			$this->settings['default_theme_dir'] . '/custom_frontpage_layouts'
		];

		$params = [
			'txt'         => $this->txt,
			'context'     => $this->context,
			'modSettings' => $this->modSettings,
		];

		ob_start();

		try {
			$blade = new BladeOne($templates, empty($this->modSettings['cache_enable']) ? null : $this->cachedir);
			$blade->setAuth($this->context['user']['is_logged'] ? $this->context['user']['name'] : null);
			echo $blade->run($this->context['lp_blade_layouts_plugin']['template'], $params);
		} catch (Exception $e) {
			$this->fatalError($e->getMessage());
		}

		$this->context['lp_layout'] = ob_get_clean();

		$this->modSettings['lp_frontpage_layout'] = '';
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

	private function getLayouts(): array
	{
		$layouts = glob(__DIR__ . '/layouts/*.blade.php');
		$customs = glob($this->settings['default_theme_dir'] . '/custom_frontpage_layouts/*.blade.php');
		$layouts = array_merge($layouts, $customs);

		$results = [];
		foreach ($layouts as $layout) {
			$item = str_replace('.blade.php', '', basename($layout));
			$results[$item] = ucfirst($item);
		}

		return $results;
	}
}