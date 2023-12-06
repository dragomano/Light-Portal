<?php

/**
 * LayoutHelper.php
 *
 * @package LayoutHelper (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */

namespace Bugo\LightPortal\Addons\LayoutHelper;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class LayoutHelper extends Plugin
{
	public string $type = 'frontpage';

	private string $mode = 'layout_helper_addon_mode';

	public function addSettings(array &$config_vars): void
	{
		$config_vars['layout_helper'][] = ['check', 'show_template_switcher'];
		$config_vars['layout_helper'][] = ['check', 'fake_cards', 'subtext' => $this->txt['lp_layout_helper']['fake_cards_subtext']];
	}

	public function frontModes(array &$modes): void
	{
		if (empty($this->context['lp_layout_helper_plugin']['fake_cards']))
			return;

		$modes[$this->mode] = DemoArticle::class;

		$this->modSettings['lp_frontpage_mode'] = $this->mode;
	}

	public function frontCustomTemplate(array $layouts): void
	{
		if (empty($this->context['lp_layout_helper_plugin']['show_template_switcher']))
			return;

		$this->context['frontpage_layouts'] = $layouts;

		if ($this->session()->isEmpty('lp_frontpage_layout')) {
			$this->context['current_layout'] = $this->request('layout', $this->modSettings['lp_frontpage_layout'] ?? 'default.latte');
		} else {
			$this->context['current_layout'] = $this->request('layout', $this->session()->get('lp_frontpage_layout'));
		}

		$this->session()->put('lp_frontpage_layout', $this->context['current_layout']);

		$this->modSettings['lp_frontpage_layout'] = $this->session()->get('lp_frontpage_layout');

		$this->setTemplate()->withLayer('layout_switcher');
	}

	public function credits(array &$links): void
	{
		$links[] = [
			'title' => 'LoremFlickr',
			'link' => 'https://loremflickr.com',
			'author' => 'Babak Fakhamzadeh',
			'license' => [
				'name' => 'the GPL-2.0 License',
				'link' => 'https://github.com/MastaBaba/LoremFlickr/blob/master/LICENSE'
			]
		];
	}
}
