<?php

/**
 * PluginMaker.php
 *
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.09.23
 */

namespace Bugo\LightPortal\Addons\PluginMaker;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class PluginMaker extends Plugin
{
	public string $type ='other';

	public function init(): void
	{
		$this->context['lp_plugin_option_types'] = array_combine(
			['text', 'url', 'color', 'int', 'float', 'check', 'multicheck', 'select'],
			$this->txt['lp_plugin_maker']['option_type_set']
		);
	}

	public function addAdminAreas(array &$admin_areas): void
	{
		$admin_areas['lp_portal']['areas']['lp_plugins']['subsections'] = array_merge(
			['main' => $admin_areas['lp_portal']['areas']['lp_plugins']['subsections']['main']],
			['add'  => [$this->context['lp_icon_set']['plus'] . $this->txt['lp_plugin_maker']['add']]],
			$admin_areas['lp_portal']['areas']['lp_plugins']['subsections']
		);
	}

	public function addPluginAreas(array &$subActions): void
	{
		$subActions['add'] = [new Handler, 'add'];
	}

	public function credits(array &$links): void
	{
		$links[] = [
			'title' => 'Nette PHP Generator',
			'link' => 'https://github.com/nette/php-generator',
			'author' => 'David Grudl',
			'license' => [
				'name' => 'the New BSD License',
				'link' => 'https://github.com/nette/php-generator/blob/master/license.md'
			]
		];
	}
}
