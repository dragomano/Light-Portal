<?php

declare(strict_types = 1);

/**
 * PluginArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Admin;

use \Bugo\LightPortal\{Addon, Helper};

if (! defined('SMF'))
	die('No direct access...');

final class PluginArea
{
	public function main()
	{
		global $context, $txt, $scripturl;

		loadLanguage('ManageMaintenance');
		loadTemplate('LightPortal/ManagePlugins');

		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/@eastdesire/jscolor@2/jscolor.min.js', array('external' => true));

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_plugins_manage'];
		$context['post_url']   = $scripturl . '?action=admin;area=lp_plugins;save';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => '<a href="https://dragomano.github.io/Light-Portal/" target="_blank" rel="noopener"><span class="main_icons help"></span></a> ' . LP_NAME,
			'description' => sprintf($txt['lp_plugins_manage_description'], 'https://github.com/dragomano/Light-Portal/wiki/How-to-create-an-addon')
		);

		$context['lp_plugins'] = Addon::getAll();

		$this->extendPluginList();

		asort($context['lp_plugins']);

		$context['lp_plugins_extra'] = $txt['lp_plugins'] . ' (' . count($context['lp_plugins']) . ')';

		// Toggle ON/OFF for plugins
		if (Helper::request()->has('toggle')) {
			$data = Helper::request()->json();
			$plugin_id = (int) $data['plugin'];

			if ($data['status'] === 'on') {
				$context['lp_enabled_plugins'] = array_filter($context['lp_enabled_plugins'], function ($item) use ($context, $plugin_id) {
					return $item !== $context['lp_plugins'][$plugin_id];
				});
			} else {
				$context['lp_enabled_plugins'][] = $context['lp_plugins'][$plugin_id];
			}

			sort($context['lp_enabled_plugins']);

			updateSettings(array('lp_enabled_plugins' => implode(',', array_unique(array_intersect($context['lp_enabled_plugins'], $context['lp_plugins'])))));

			exit;
		}

		$config_vars = [];

		// You can add settings for your plugins
		Addon::run('addSettings', array(&$config_vars), $context['lp_plugins']);

		// Saving of plugin settings
		if (Helper::request()->has('save')) {
			checkSession();

			$plugin_name = Helper::post('plugin_name');

			$plugin_options = [];
			foreach ($config_vars[$plugin_name] as $var) {
				$var[1] = 'lp_' . $plugin_name . '_addon_' . $var[1];

				if (Helper::post()->has($var[1])) {
					if ($var[0] === 'check') {
						$plugin_options[$var[1]] = (int) Helper::validate(Helper::post($var[1]), 'bool');
					} elseif ($var[0] === 'int') {
						$plugin_options[$var[1]] = Helper::validate(Helper::post($var[1]), 'int');
					} elseif ($var[0] === 'float') {
						$plugin_options[$var[1]] = Helper::validate(Helper::post($var[1]), 'float');
					} elseif ($var[0] === 'multicheck') {
						$plugin_options[$var[1]] = [];

						foreach (Helper::post($var[1]) as $key => $value) {
							$plugin_options[$var[1]][$key] = (int) Helper::validate($value, 'bool');
						}

						$plugin_options[$var[1]] = json_encode($plugin_options[$var[1]]);
					} elseif ($var[0] === 'url') {
						$plugin_options[$var[1]] = Helper::validate(Helper::post($var[1]), 'url');
					} elseif ($var[0] === 'select' && ! empty($var['multiple'])) {
						$plugin_options[$var[1]] = json_encode(Helper::post($var[1]));
					} else {
						$plugin_options[$var[1]] = Helper::post($var[1]);
					}
				}
			}

			// You can do additional actions after settings saving
			Addon::run('saveSettings', array(&$plugin_options), $context['lp_plugins']);

			if (! empty($plugin_options))
				updateSettings($plugin_options);

			exit;
		}

		$context['all_lp_plugins'] = array_map(function ($item) use ($txt, &$context, $config_vars) {
			$requires = [];
			$disables = [];
			$composer = false;

			$snake_name = Helper::getSnakeName($item);

			try {
				$className = '\Bugo\LightPortal\Addons\\' . $item . '\\' . $item;
				$addonClass = new \ReflectionClass($className);

				if ($addonClass->hasProperty('author'))
					$author = $addonClass->getProperty('author')->getValue(new $className);

				if ($addonClass->hasProperty('link'))
					$link = $addonClass->getProperty('link')->getValue(new $className);

				if ($addonClass->hasProperty('requires'))
					$requires = $addonClass->getProperty('requires')->getValue(new $className);

				if ($addonClass->hasProperty('disables'))
					$disables = $addonClass->getProperty('disables')->getValue(new $className);

				$composer = is_file(dirname($addonClass->getFileName()) . DIRECTORY_SEPARATOR . 'composer.json');
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
				'name'       => $item,
				'snake_name' => $snake_name,
				'desc'       => $txt['lp_' . $snake_name]['description'] ?? '',
				'author'     => $author ?? '',
				'link'       => $link ?? '',
				'status'     => in_array($item, $context['lp_enabled_plugins']) ? 'on' : 'off',
				'types'      => $this->getTypes($snake_name),
				'special'    => $special ?? '',
				'settings'   => $config_vars[$snake_name] ?? [],
				'requires'   => array_diff($requires, $context['lp_enabled_plugins']),
				'disables'   => array_intersect($disables, $context['lp_enabled_plugins']),
				'composer'   => $composer,
			];
		}, $context['lp_plugins']);

		$this->prepareAddonChart();

		// Sort plugin list
		$context['current_filter'] = Helper::post('filter', 'all');

		if (Helper::post()->has('filter')) {
			$context['all_lp_plugins'] = array_filter($context['all_lp_plugins'], function ($item) use ($context) {
				$filter = Helper::post('filter');

				if (! in_array($filter, array_keys($context['lp_plugin_types'])) || in_array($context['lp_plugin_types'][$filter], array_keys($item['types']))) {
					return true;
				}
			});
		}

		$context['sub_template'] = 'manage_plugins';
	}

	private function extendPluginList()
	{
		global $context, $user_info, $boardurl;

		$context['lp_can_donate']   = [];
		$context['lp_can_download'] = [];

		if (($xml = Helper::cache()->get('custom_addon_list', 259200)) === null) {
			$link = $user_info['ip'] === '127.0.0.1' ? $boardurl . '/addons.json' : 'https://dragomano.ru/addons.json';

			$addon_list = fetch_web_data($link);

			if (empty($addon_list))
				return;

			$xml = json_decode($addon_list, true);

			Helper::cache()->put('custom_addon_list', $xml, 259200);
		}

		if (empty($xml) || ! is_array($xml))
			return;

		if (! empty($xml['donate'])) {
			foreach ($xml['donate'] as $addon) {
				$context['lp_plugins'][] = $addon['name'];
				$context['lp_can_donate'][$addon['name']] = $addon;
			}
		}

		if (! empty($xml['download'])) {
			foreach ($xml['download'] as $addon) {
				$context['lp_plugins'][] = $addon['name'];
				$context['lp_can_download'][$addon['name']] = $addon;
			}
		}

		$context['lp_plugins'] = array_unique($context['lp_plugins']);
	}

	private function getTypeClass(string $type): string
	{
		return ' lp_type_' . $type;
	}

	private function getTypes(string $snake_name): array
	{
		global $context, $txt;

		if (empty($snake_name) || empty($type = $context['lp_' . $snake_name]['type'] ?? ''))
			return [$txt['not_applicable'] => ''];

		if (is_array($type)) {
			$all_types = [];
			foreach ($type as $t) {
				$all_types[$context['lp_plugin_types'][$t]] = $this->getTypeClass($t);
			}

			return $all_types;
		}

		return [$context['lp_plugin_types'][$type] => $this->getTypeClass($type)];
	}

	private function prepareAddonChart()
	{
		global $context, $txt;

		if (Helper::request()->has('chart') === false)
			return;

		$typeCount = [];
		foreach ($context['all_lp_plugins'] as $plugin) {
			$types = array_merge(array_keys($plugin['types']));
			foreach ($types as $type) {
				$key = array_search($type, $txt['lp_plugins_types']);

				if ($key === false)
					$key = 7;

				$typeCount[$key] ??= 0;
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
