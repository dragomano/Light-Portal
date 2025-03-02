<?php declare(strict_types=1);

/**
 * @package SiteList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 20.02.25
 */

namespace Bugo\LightPortal\Plugins\SiteList;

use Bugo\Compat\Config;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\VarType;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class SiteList extends Plugin
{
	public string $type = 'frontpage';

	private string $mode = 'site_list_addon_mode';

	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name][] = ['callback', 'urls', $this->showList()];
	}

	public function showList(): bool|string
	{
		$this->useTemplate();

		$urls = Utils::jsonDecode($this->context['urls'] ?? '', true);

		Theme::addInlineJavaScript($this->getFromTemplate('site_list_handle_func', $urls ?? []));

		ob_start();

		callback_site_list_table();

		return ob_get_clean();
	}

	public function saveSettings(Event $e): void
	{
		if (! isset($e->args->settings['urls']))
			return;

		$sites = [];

		if ($this->request()->has('url')) {
			foreach ($this->request()->get('url') as $key => $value) {
				$sites[VarType::URL->filter($value)] = [
					VarType::URL->filter($this->request()->get('image')[$key]),
					$this->request()->get('title')[$key],
					$this->request()->get('desc')[$key],
				];
			}
		}

		$e->args->settings['urls'] = json_encode($sites, JSON_UNESCAPED_UNICODE);
	}

	public function frontModes(Event $e): void
	{
		$e->args->modes[$this->mode] = SiteArticle::class;

		Config::$modSettings['lp_frontpage_mode'] = $this->mode;
	}
}
