<?php

/**
 * MainMenu.php
 *
 * @package MainMenu (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 01.01.21
 */

namespace Bugo\LightPortal\Addons\MainMenu;

use Bugo\LightPortal\Addons\Plugin;

/**
 * Generated by PluginMaker
 */
class MainMenu extends Plugin
{
	public string $type = 'other';

	public function init()
	{
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons#', false, __FILE__);
		add_integration_function('integrate_current_action', __CLASS__ . '::currentAction#', false, __FILE__);
	}

	public function menuButtons(array &$buttons)
	{
		$this->context['lp_main_menu_addon_items'] = empty($this->modSettings['lp_main_menu_addon_items']) ? [] : json_decode($this->modSettings['lp_main_menu_addon_items'], true);

		if (empty($this->context['lp_main_menu_addon_items']))
			return;

		$pages = [];

		foreach ($this->context['lp_main_menu_addon_items'] as $item) {
			$alias = strtr(parse_url($item['url'], PHP_URL_QUERY), ['=' => '_']);

			$pages['portal_' . $alias] = [
				'title' => $this->getTranslatedTitle($item['langs']),
				'href'  => $item['url'],
				'icon'  => empty($item['unicode']) ? null : ('" style="display: none"></span><span class="portal_menu_icons fas fa-portal_' . $alias),
				'show'  => $this->canViewItem($item['access'])
			];

			addInlineCss('
			.fa-portal_' . $alias . '::before {
				content: "\\' . $item['unicode'] . '";
			}');
		}

		$counter = -1;
		foreach (array_keys($buttons) as $area) {
			$counter++;

			if ($area === 'admin')
				break;
		}

		$buttons = array_merge(
			array_slice($buttons, 0, $counter, true),
			$pages,
			array_slice($buttons, $counter, null, true)
		);
	}

	public function currentAction(string &$current_action)
	{
		if (empty($this->context['canonical_url']) || empty($this->context['lp_main_menu_addon_items']))
			return;

		if ($this->request()->url() === $this->context['canonical_url'] && in_array($this->context['canonical_url'], array_column($this->context['lp_main_menu_addon_items'], 'url'))) {
			$current_action = 'portal_action_' . $current_action;

			if ($this->request()->isEmpty('action') && $this->request()->notEmpty(LP_PAGE_PARAM)) {
				$current_action = 'portal_page_' . $this->request(LP_PAGE_PARAM);
			}
		}
	}

	public function addSettings(array &$config_vars)
	{
		$config_vars['main_menu'][] = ['callback', 'items', [$this, 'showList']];
	}

	public function showList()
	{
		$this->prepareForumLanguages();

		$this->loadTemplate();

		callback_main_menu_table();
	}

	public function saveSettings(array &$plugin_options)
	{
		if (! isset($plugin_options['lp_main_menu_addon_items']))
			return;

		$items = $langs = [];

		if ($this->post()->has('url')) {
			foreach ($this->post('url') as $key => $value) {
				foreach ($this->post('langs') as $lang => $val) {
					if (! empty($val[$key]))
						$langs[$key][$lang] = $val[$key];
				}

				$items[] = [
					'url'     => $this->validate($value, 'url'),
					'unicode' => $this->validate($this->post('unicode')[$key]),
					'langs'   => $langs[$key],
					'access'  => $this->validate($this->post('access')[$key], 'int')
				];
			}
		}

		$plugin_options['lp_main_menu_addon_items'] = json_encode($items, JSON_UNESCAPED_UNICODE);
	}
}
