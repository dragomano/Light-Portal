<?php

/**
 * @package MainMenu (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 13.11.24
 */

namespace Bugo\LightPortal\Plugins\MainMenu;

use Bugo\Compat\{Config, User, Utils};
use Bugo\LightPortal\Enums\Hook;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Utils\Language;

use const LP_ACTION;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class MainMenu extends Plugin
{
	public string $type = 'other';

	public function init(): void
	{
		$this->applyHook(Hook::menuButtons);
	}

	public function menuButtons(array &$buttons): void
	{
		$this->prepareVariables();

		if (! empty(Utils::$context['lp_main_menu_addon_portal_langs'][User::$info['language']])) {
			$buttons[LP_ACTION]['title'] = Utils::$context['lp_main_menu_addon_portal_langs'][User::$info['language']];
		}

		if (! empty(Utils::$context['lp_main_menu_addon_forum_langs'][User::$info['language']])) {
			$buttons[empty(Config::$modSettings['lp_standalone_mode']) ? 'home' : 'forum']['title'] = Utils::$context['lp_main_menu_addon_forum_langs'][User::$info['language']];
		}
	}

	public function frontLayouts(): void
	{
		if (
			! empty(Utils::$context['lp_main_menu_addon_portal_langs'][User::$info['language']])
			&& ! empty(Utils::$context['linktree'][1])
		) {
			Utils::$context['linktree'][1]['name'] = Utils::$context['lp_main_menu_addon_portal_langs'][User::$info['language']];
		}
	}

	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name][] = ['callback', 'items', $this->showList()];
	}

	public function showList(): bool|string
	{
		Language::prepareList();

		$this->prepareVariables();
		$this->setTemplate();

		ob_start();

		callback_main_menu_table();

		return ob_get_clean();
	}

	public function saveSettings(Event $e): void
	{
		if (! isset($e->args->settings['items']))
			return;

		$portalLangs = $forumLangs = [];

		if ($this->request()->has('portal_item_langs')) {
			foreach ($this->request('portal_item_langs') as $lang => $val) {
				if (! empty($val))
					$portalLangs[$lang] = $val;
			}
		}

		if ($this->request()->has('forum_item_langs')) {
			foreach ($this->request('forum_item_langs') as $lang => $val) {
				if (! empty($val)) {
					$forumLangs[$lang] = $val;
				}
			}
		}

		$e->args->settings['portal_langs'] = json_encode($portalLangs, JSON_UNESCAPED_UNICODE);
		$e->args->settings['forum_langs']  = json_encode($forumLangs, JSON_UNESCAPED_UNICODE);
	}

	private function prepareVariables(): void
	{
		Utils::$context['lp_main_menu_addon_portal_langs'] = Utils::jsonDecode(
			$this->context['portal_langs'] ?? '', true
		);

		Utils::$context['lp_main_menu_addon_forum_langs']  = Utils::jsonDecode(
			$this->context['forum_langs'] ?? '', true
		);
	}
}
