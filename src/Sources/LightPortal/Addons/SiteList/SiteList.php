<?php

/**
 * SiteList.php
 *
 * @package SiteList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 02.06.24
 */

namespace Bugo\LightPortal\Addons\SiteList;

use Bugo\Compat\{Config, Theme, Utils};
use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Enums\VarType;

if (! defined('LP_NAME'))
	die('No direct access...');

class SiteList extends Plugin
{
	public string $type = 'frontpage';

	private string $mode = 'site_list_addon_mode';

	public function addSettings(array &$settings): void
	{
		$settings['site_list'][] = ['callback', 'urls', $this->showList()];
	}

	public function showList(): bool|string
	{
		$this->setTemplate();

		$urls = Utils::jsonDecode(Utils::$context['lp_site_list_plugin']['urls'] ?? '', true);

		Theme::addInlineJavaScript($this->getFromTemplate('site_list_handle_func', $urls ?? []));

		ob_start();

		callback_site_list_table();

		return ob_get_clean();
	}

	public function saveSettings(array &$settings): void
	{
		if (! isset($settings['urls']))
			return;

		$sites = [];

		if ($this->request()->has('url')) {
			foreach ($this->request('url') as $key => $value) {
				$sites[VarType::URL->filter($value)] = [
					VarType::URL->filter($this->request('image')[$key]),
					$this->request('title')[$key],
					$this->request('desc')[$key],
				];
			}
		}

		$settings['urls'] = json_encode($sites, JSON_UNESCAPED_UNICODE);
	}

	public function frontModes(array &$modes): void
	{
		$modes[$this->mode] = SiteArticle::class;

		Config::$modSettings['lp_frontpage_mode'] = $this->mode;
	}
}
