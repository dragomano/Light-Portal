<?php declare(strict_types=1);

/**
 * @package SiteList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 04.11.25
 */

namespace LightPortal\Plugins\SiteList;

use Bugo\Compat\Utils;
use LightPortal\Enums\PluginType;
use LightPortal\Enums\VarType;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\Plugin;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\SettingsFactory;
use LightPortal\Utils\Traits\HasView;

use function LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::FRONTPAGE)]
class SiteList extends Plugin
{
	use HasView;

	private string $mode = 'site_list_addon_mode';

	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name] = SettingsFactory::make()->custom('urls', $this->view())->toArray();
	}

	public function addLayerBelow(): void
	{
		$urls = Utils::jsonDecode($this->context['urls'] ?? '', true);

		echo $this->view('handle_sites', ['urls' => $urls ?? []]);
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

		app()->add(SiteArticle::class);

		$e->args->currentMode = $this->mode;
	}
}
