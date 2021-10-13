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
 * @version 1.9
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

		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/@eastdesire/jscolor@2/jscolor.min.js', array('external' => true));

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_plugins_manage'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => '<a href="https://dragomano.github.io/Light-Portal/" target="_blank" rel="noopener"><span class="main_icons help"></span></a> ' . LP_NAME,
			'description' => sprintf($txt['lp_plugins_manage_description'], 'https://github.com/dragomano/Light-Portal/wiki/How-to-create-an-addon')
		);

		$context['lp_plugins'] = Addons::getAll();

		$this->extendPluginList();

		asort($context['lp_plugins']);

		$context['lp_plugins_extra'] = $txt['lp_plugins'] . ' (' . count($context['lp_plugins']) . ')';
		$context['post_url']         = $scripturl . '?action=admin;area=lp_plugins;save';

		// Toggle ON/OFF for plugins
		if (Helpers::request()->has('toggle')) {
			$data = Helpers::request()->json();
			$plugin_id = (int) $data['toggle_plugin'];

			if ($key = array_search($context['lp_plugins'][$plugin_id], $context['lp_enabled_plugins'])) {
				unset($context['lp_enabled_plugins'][$key]);
			} else {
				$context['lp_enabled_plugins'][] = $context['lp_plugins'][$plugin_id];
			}

			updateSettings(array('lp_enabled_plugins' => implode(',', array_intersect($context['lp_enabled_plugins'], $context['lp_plugins']))));

			exit;
		}

		$config_vars = [];

		// You can add settings for your plugins
		Addons::run('addSettings', array(&$config_vars), $context['lp_plugins']);

		// Saving of plugin settings
		if (Helpers::request()->has('save')) {
			checkSession();

			$plugin_name = Helpers::post('plugin_name');

			$plugin_options = [];
			foreach ($config_vars[$plugin_name] as $var) {
				$var[1] = 'lp_' . $plugin_name . '_addon_' . $var[1];

				if (Helpers::post()->has($var[1])) {
					if ($var[0] == 'check') {
						$plugin_options[$var[1]] = (int) Helpers::validate(Helpers::post($var[1]), 'bool');
					} elseif ($var[0] == 'int') {
						$plugin_options[$var[1]] = Helpers::validate(Helpers::post($var[1]), 'int');
					} elseif ($var[0] == 'float') {
						$plugin_options[$var[1]] = Helpers::validate(Helpers::post($var[1]), 'float');
					} elseif ($var[0] == 'multicheck') {
						$plugin_options[$var[1]] = [];

						foreach (Helpers::post($var[1]) as $key => $value) {
							$plugin_options[$var[1]][$key] = (int) Helpers::validate($value, 'bool');
						}

						$plugin_options[$var[1]] = json_encode($plugin_options[$var[1]]);
					} elseif ($var[0] == 'url') {
						$plugin_options[$var[1]] = Helpers::validate(Helpers::post($var[1]), 'url');
					} elseif ($var[0] == 'select' && !empty($var['multiple'])) {
						$plugin_options[$var[1]] = json_encode(Helpers::post($var[1]));
					} else {
						$plugin_options[$var[1]] = Helpers::post($var[1]);
					}
				}
			}

			// You can do additional actions after settings saving
			Addons::run('saveSettings', array(&$plugin_options), $context['lp_plugins']);

			if (!empty($plugin_options))
				updateSettings($plugin_options);

			exit;
		}

		$context['all_lp_plugins'] = array_map(function ($item) use ($txt, &$context, $config_vars) {
			$requires = [];

			$snake_name = Helpers::getSnakeName($item);

			try {
				$className = __NAMESPACE__ . '\Addons\\' . $item . '\\' . $item;
				$addonClass = new \ReflectionClass($className);

				if ($addonClass->hasProperty('author'))
					$author = $addonClass->getProperty('author')->getValue(new $className);

				if ($addonClass->hasProperty('link'))
					$link = $addonClass->getProperty('link')->getValue(new $className);

				if ($addonClass->hasProperty('requires'))
					$requires = $addonClass->getProperty('requires')->getValue(new $className);
			} catch (\ReflectionException $e) {
				if (isset($context['lp_can_donate'][$item])) {
					$context['lp_' . $snake_name]['type'] = $context['lp_can_donate'][$item]['type'] ?? 'other';
					$special = $txt['lp_can_donate'];
				}

				if (isset($context['lp_can_download'][$item])) {
					$context['lp_' . $snake_name]['type'] = $context['lp_can_download'][$item]['type'] ?? 'other';
					$special = $txt['lp_can_download'];
				}
			}

			return [
				'name'        => $item,
				'snake_name'  => $snake_name,
				'label_class' => $this->getLabelClass($snake_name),
				'desc'        => $txt['lp_' . $snake_name]['description'] ?? '',
				'author'      => $author ?? '',
				'link'        => $link ?? '',
				'status'      => in_array($item, $context['lp_enabled_plugins']) ? 'on' : 'off',
				'type'        => $this->getType($snake_name),
				'special'     => $special ?? '',
				'settings'    => $config_vars[$snake_name] ?? [],
				'requires'    => array_diff($requires, $context['lp_enabled_plugins'])
			];
		}, $context['lp_plugins']);

		$this->prepareAddonChart();

		// Sort plugin list
		$context['current_filter'] = Helpers::post('filter', 'all');

		if (Helpers::post()->has('filter')) {
			$context['all_lp_plugins'] = array_filter($context['all_lp_plugins'], function ($item) use ($context) {
				$filter = Helpers::post('filter');

				if (!in_array($filter, array_keys($context['lp_plugin_types'])) || strpos($item['type'], $context['lp_plugin_types'][$filter]) !== false) {
					return true;
				}
			});
		}

		$context['sub_template'] = 'manage_plugins';
	}

	/**
	 * @return void
	 */
	private function extendPluginList()
	{
		global $context, $boardurl;

		$context['lp_can_donate']   = [];
		$context['lp_can_download'] = [];

		if (($xml = Helpers::cache()->get('custom_addon_list', 259200)) === null) {
			$link = Helpers::server('SERVER_ADDR') === '127.0.0.1' ? $boardurl . '/addons.json' : 'https://dragomano.ru/addons.json';

			$addon_list = fetch_web_data($link);

			if (empty($addon_list))
				return;

			$xml = json_decode($addon_list, true);

			Helpers::cache()->put('custom_addon_list', $xml, 259200);
		}

		if (empty($xml) || !is_array($xml))
			return;

		if (!empty($xml['donate'])) {
			foreach ($xml['donate'] as $addon) {
				$context['lp_plugins'][] = $addon['name'];
				$context['lp_can_donate'][$addon['name']] = $addon;
			}
		}

		if (!empty($xml['download'])) {
			foreach ($xml['download'] as $addon) {
				$context['lp_plugins'][] = $addon['name'];
				$context['lp_can_download'][$addon['name']] = $addon;
			}
		}

		$context['lp_plugins'] = array_unique($context['lp_plugins']);
	}

	/**
	 * @param string $snake_name
	 * @return string
	 */
	private function getLabelClass(string $snake_name): string
	{
		global $context;

		if (empty($context['lp_' . $snake_name]) || empty($type = $context['lp_' . $snake_name]['type']))
			return '';

		$type = is_array($type) ? implode('_', $type) : $type;

		return ' lp_type_' . $type;
	}

	/**
	 * @param string $snake_name
	 * @return string
	 */
	private function getType(string $snake_name): string
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

	/**
	 * @return void
	 */
	private function prepareAddonChart()
	{
		global $context, $txt;

		if (Helpers::request()->has('chart') === false)
			return;

		$typeCount = [];
		foreach ($context['all_lp_plugins'] as $plugin) {
			$types = explode(' + ', $plugin['type']);
			foreach ($types as $type) {
				$key = array_search($type, $txt['lp_plugins_types']);

				if ($key === false)
					$key = 7;

				if (!isset($typeCount[$key]))
					$typeCount[$key] = 0;

				$typeCount[$key]++;
			}
		}

		if (empty($typeCount))
			return;

		$context['lp_addon_chart'] = true;

		ksort($typeCount);

		$context['insert_after_template'] .= '
		<script src="https://cdn.jsdelivr.net/npm/chart.js@3/dist/chart.min.js"></script>
		<script>
			const pageChart = document.querySelector("#addonChart");
			new Chart(pageChart, {
				type: "pie",
				data: {
					labels: ["' . implode('", "', $context['lp_plugin_types']) . '"],
					datasets: [{
						data: [' . implode(', ', $typeCount) . '],
						backgroundColor: ["#667d99", "#48bf83", "#9354ca", "#91ae26", "#ef564f", "#d68b4f", "#4b93d1", "#414141", "#8597ad", "#52647a"]
					}]
				},
				options: {
					responsive: true,
					plugins: {
						legend: {
							position: "top"
						}
					}
				}
			});
		</script>';
	}
}
