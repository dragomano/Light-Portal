<?php

/**
 * BootstrapIcons.php
 *
 * @package BootstrapIcons (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.05.23
 */

namespace Bugo\LightPortal\Addons\BootstrapIcons;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by Plugin Maker
 */
class BootstrapIcons extends Plugin
{
	public string $type = 'icons';

	private string $prefix = 'bi bi-';

	public function init()
	{
		$this->loadExtCSS('https://cdn.jsdelivr.net/npm/bootstrap-icons@1/font/bootstrap-icons.min.css', ['seed' => false]);
	}

	public function prepareIconList(array &$all_icons)
	{
		if (($icons = $this->cache()->get('all_bi_icons', 30 * 24 * 60 * 60)) === null) {
			$content = file_get_contents('https://cdn.jsdelivr.net/npm/bootstrap-icons@1/font/bootstrap-icons.json');
			$json = array_flip($this->jsonDecode($content, true));

			$icons = [];
			foreach ($json as $icon) {
				$icons[] = $this->prefix . $icon;
			}

			$this->cache()->put('all_bi_icons', $icons, 30 * 24 * 60 * 60);
		}

		$all_icons = array_merge($all_icons, $icons);
	}

	public function credits(array &$links)
	{
		$links[] = [
			'title' => 'Bootstrap Icons',
			'link' => 'https://github.com/twbs/icons',
			'author' => 'The Bootstrap Authors',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/twbs/icons/blob/main/LICENSE.md'
			]
		];
	}
}
