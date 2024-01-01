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
 * @version 17.12.23
 */

namespace Bugo\LightPortal\Addons\PlatesLayouts;

use Bugo\LightPortal\Addons\Plugin;
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
		$this->txt['lp_plates_layouts']['note'] = sprintf(
			$this->txt['lp_plates_layouts']['note'],
			$this->extension,
			$this->settings['default_theme_dir'] . DIRECTORY_SEPARATOR . 'portal_layouts'
		);

		$config_vars['plates_layouts'][] = ['desc', 'note'];
		$config_vars['plates_layouts'][] = ['title', 'example'];
		$config_vars['plates_layouts'][] = ['callback', '_', $this->showExample()];
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
			$templates = new Engine($this->settings['default_theme_dir'] . '/portal_layouts', 'tpl.php');
			$templates->registerFunction('debug', fn(array $data) => parse_bbc('[code]' . print_r($data, true) . '[/code]'));

			$layout = strstr($this->modSettings['lp_frontpage_layout'], '.', true) ?: $this->modSettings['lp_frontpage_layout'];

			echo $templates->render($layout, $params);
		} catch (TemplateNotFound $e) {
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
		return '<div class="roundframe">' . $this->parseBbc('[php]' . file_get_contents(__DIR__. '/layouts/example.tpl.php') . '[/php]') . '</div>';
	}
}
