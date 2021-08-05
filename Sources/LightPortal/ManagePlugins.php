<?php

namespace Bugo\LightPortal;

/**
 * ManagePlugins.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ManagePlugins
{
	/**
	 * Manage plugins
	 *
	 * Управление плагинами
	 *
	 * @return void
	 */
	public function main()
	{
		global $context, $txt, $scripturl;

		loadLanguage('ManageMaintenance');
		loadTemplate('LightPortal/ManagePlugins');

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_plugins_manage'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => '<a href="https://dragomano.github.io/Light-Portal/" target="_blank" rel="noopener"><span class="main_icons help"></span></a> ' . LP_NAME,
			'description' => sprintf($txt['lp_plugins_manage_description'], 'https://github.com/dragomano/Light-Portal/wiki/How-to-create-an-addon')
		);

		$context['lp_plugins'] = Addons::getAll();

		$this->addPluginsForSponsors();

		asort($context['lp_plugins']);

		$context['lp_plugins_extra'] = $txt['lp_plugins'] . ' (' . count($context['lp_plugins']) . ')';
		$context['post_url']         = $scripturl . '?action=admin;area=lp_plugins;save';

		$config_vars = [];

		// You can add settings for your plugins
		Addons::run('addSettings', array(&$config_vars), $context['lp_plugins']);

		$context['all_lp_plugins'] = array_map(function ($item) use ($txt, $context, $config_vars) {
			$donate = false;

			try {
				$className = __NAMESPACE__ . '\Addons\\' . $item . '\\' . $item;
				$addonClass = new \ReflectionClass($className);
				$comments = explode('* ', $addonClass->getDocComment());
			} catch (\ReflectionException $e) {
				$donate = true;
			}

			return [
				'name'       => $item,
				'snake_name' => $snake_name = Helpers::getSnakeName($item),
				'desc'       => $txt['lp_' . $snake_name]['description'] ?? '',
				'link'       => !empty($comments[3]) ? trim(explode(' ', $comments[3])[1]) : '',
				'author'     => !empty($comments[4]) ? trim(explode(' ', $comments[4])[1]) : '',
				'status'     => in_array($item, $context['lp_enabled_plugins']) ? 'on' : 'off',
				'types'      => $donate ? $txt['lp_sponsors_only'] : $this->getTypes($snake_name),
				'settings'   => $config_vars[$snake_name] ?? []
			];
		}, $context['lp_plugins']);

		// Sort plugin list
		$context['current_filter'] = Helpers::post('filter', 'all');

		if (Helpers::post()->has('filter')) {
			$context['all_lp_plugins'] = array_filter($context['all_lp_plugins'], function ($item) use ($context)
			{
				$filter = Helpers::post('filter');

				if (!in_array($filter, array_keys($context['lp_plugin_types'])) || strpos($item['types'], $context['lp_plugin_types'][$filter]) !== false) {
					return true;
				}
			});
		}

		$context['sub_template'] = 'manage_plugins';

		if (Helpers::request()->has('save')) {
			checkSession();

			$plugin_options = [];
			foreach ($config_vars as $plugin_name => $vars) {
				foreach ($vars as $var) {
					$var[1] = 'lp_' . $plugin_name . '_addon_' . $var[1];

					if (Helpers::post()->has($var[1])) {
						if ($var[0] == 'check') {
							$plugin_options[$var[1]] = (int) Helpers::validate(Helpers::post($var[1]), 'bool');
						} elseif ($var[0] == 'int') {
							$plugin_options[$var[1]] = Helpers::validate(Helpers::post($var[1]), 'int');
						} elseif ($var[0] == 'multicheck') {
							$plugin_options[$var[1]] = [];

							foreach (Helpers::post($var[1]) as $key => $value) {
								$plugin_options[$var[1]][$key] = (int) Helpers::validate($value, 'bool');
							}

							$plugin_options[$var[1]] = json_encode($plugin_options[$var[1]]);
						} elseif ($var[0] == 'url') {
							$plugin_options[$var[1]] = Helpers::validate(Helpers::post($var[1]), 'url');
						} else {
							$plugin_options[$var[1]] = Helpers::post($var[1]);
						}
					}
				}
			}

			if (!empty($plugin_options))
				updateSettings($plugin_options);

			// You can do additional actions after settings saving
			Addons::run('onSettingsSaving');

			exit(json_encode('ok'));
		}

		// Toggle plugins
		$data = Helpers::request()->json();

		if (isset($data['toggle_plugin'])) {
			$plugin_id = (int) $data['toggle_plugin'];

			if (in_array($context['lp_plugins'][$plugin_id], $context['lp_enabled_plugins'])) {
				$key = array_search($context['lp_plugins'][$plugin_id], $context['lp_enabled_plugins']);
				unset($context['lp_enabled_plugins'][$key]);
			} else {
				$context['lp_enabled_plugins'][] = $context['lp_plugins'][$plugin_id];
			}

			updateSettings(array('lp_enabled_plugins' => implode(',', $context['lp_enabled_plugins'])));

			exit(json_encode('ok'));
		}

		$prepared_vars = [];
		foreach ($config_vars as $plugin => $vars) {
			foreach ($vars as $var) {
				$var[1] = 'lp_' . $plugin . '_addon_' . $var[1];
				$prepared_vars[] = $var;
			}
		}

		prepareDBSettingContext($prepared_vars);
	}

	/**
	 * @return void
	 */
	private function addPluginsForSponsors()
	{
		global $context;

		$context['lp_plugins'] = array_merge(
			$context['lp_plugins'],
			array(
				'AvatarGenerator',
				'ExtUpload',
				'GoogleAmp',
				'Jodit',
				'PageScroll',
				'SiteList',
				'YandexTurbo'
			)
		);

		$context['lp_plugins'] = array_unique($context['lp_plugins']);
	}

	/**
	 * @param string $snake_name
	 * @return string
	 */
	private function getTypes(string $snake_name): string
	{
		global $txt, $context;

		if (empty($snake_name))
			return $txt['not_applicable'];

		$data = $context['lp_' . $snake_name]['type'] ?? '';

		if (empty($data))
			return $txt['not_applicable'];

		if (is_array($data)) {
			$all_types = [];
			foreach ($data as $type) {
				$all_types[] = $context['lp_plugin_types'][$type];
			}

			return implode(' + ', $all_types);
		}

		return $context['lp_plugin_types'][$data];
	}
}
