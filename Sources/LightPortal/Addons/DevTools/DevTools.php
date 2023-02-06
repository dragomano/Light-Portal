<?php

/**
 * DevTools.php
 *
 * @package DevTools (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 11.05.22
 */

namespace Bugo\LightPortal\Addons\DevTools;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class DevTools extends Plugin
{
	public string $type = 'frontpage';

	private string $mode = 'dev_tools_addon_mode';

	public function addSettings(array &$config_vars)
	{
		$config_vars['dev_tools'][] = ['check', 'show_template_switcher'];
		$config_vars['dev_tools'][] = ['check', 'fake_cards', 'subtext' => $this->txt['lp_dev_tools']['fake_cards_subtext']];
	}

	public function frontModes(array &$modes)
	{
		if (empty($this->context['lp_dev_tools_plugin']['fake_cards']))
			return;

		$modes[$this->mode] = DemoArticle::class;

		$this->modSettings['lp_frontpage_mode'] = $this->mode;
	}

	public function frontCustomTemplate(array $layouts)
	{
		if (empty($this->context['lp_dev_tools_plugin']['show_template_switcher']))
			return;

		$this->context['frontpage_layouts'] = $layouts;

		$this->context['current_layout'] = $this->request('layout', $this->modSettings['lp_frontpage_layout'] ?? 'articles');

		$this->setTemplate('show_' . $this->context['current_layout'])->withLayer('layout_switcher');
	}

	public function credits(array &$links)
	{
		$links[] = [
			'title' => 'Lorem Picsum',
			'link' => 'https://picsum.photos',
			'author' => 'David Marby & Nijiko Yonskai',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/DMarby/picsum-photos/blob/main/LICENSE.md'
			]
		];
	}
}
