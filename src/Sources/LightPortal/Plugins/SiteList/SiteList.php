<?php declare(strict_types=1);

/**
 * @package SiteList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 01.10.25
 */

namespace Bugo\LightPortal\Plugins\SiteList;

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\VarType;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Utils\Traits\HasView;

use function Bugo\LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::FRONTPAGE)]
class SiteList extends Plugin
{
	use HasView;

	private string $mode = 'site_list_addon_mode';

	#[HookAttribute(PortalHook::addSettings)]
	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name][] = ['callback', 'urls', $this->view()];
	}

	#[HookAttribute(PortalHook::addLayerBelow)]
	public function addLayerBelow(): void
	{
		$urls = Utils::jsonDecode($this->context['urls'] ?? '', true);

		echo $this->view('handle_sites', ['urls' => $urls ?? []]);
	}

	#[HookAttribute(PortalHook::saveSettings)]
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

	#[HookAttribute(PortalHook::frontModes)]
	public function frontModes(Event $e): void
	{
		$e->args->modes[$this->mode] = SiteArticle::class;

		app()->add(SiteArticle::class);

		Config::$modSettings['lp_frontpage_mode'] = $this->mode;
	}
}
