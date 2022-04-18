<?php

declare(strict_types=1);

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

namespace Bugo\LightPortal\Areas;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Addons\Ini;
use ReflectionClass;
use ReflectionException;

use function checkSession;
use function fetch_web_data;
use function smf_json_decode;
use function loadJavaScriptFile;
use function loadLanguage;
use function loadTemplate;
use function updateSettings;

if (! defined('SMF'))
	die('No direct access...');

final class PluginArea
{
	use Helper;

	public function main()
	{
		loadLanguage('ManageMaintenance');
		loadTemplate('LightPortal/ManagePlugins');

		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/@eastdesire/jscolor@2/jscolor.min.js', ['external' => true]);

		$this->context['page_title'] = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_plugins_manage'];
		$this->context['post_url'] = $this->scripturl . '?action=admin;area=lp_plugins;save';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => sprintf($this->txt['lp_plugins_manage_description'], 'https://github.com/dragomano/Light-Portal/wiki/How-to-create-an-addon'),
		];

		$this->context['lp_plugins'] = $this->getAllAddons();

		$this->extendPluginList();

		asort($this->context['lp_plugins']);

		$this->context['lp_plugins_extra'] = $this->txt['lp_plugins'] . ' (' . count($this->context['lp_plugins']) . ')';

		// Toggle ON/OFF for plugins
		if ($this->request()->has('toggle')) {
			$data = $this->request()->json();
			$plugin_id = (int) $data['plugin'];

			if ($data['status'] === 'on') {
				$this->context['lp_enabled_plugins'] = array_filter($this->context['lp_enabled_plugins'], fn($item) => $item !== $this->context['lp_plugins'][$plugin_id]);
			} else {
				$this->context['lp_enabled_plugins'][] = $this->context['lp_plugins'][$plugin_id];
			}

			sort($this->context['lp_enabled_plugins']);

			updateSettings(['lp_enabled_plugins' => implode(',', array_unique(array_intersect($this->context['lp_enabled_plugins'], $this->context['lp_plugins'])))]);

			$this->cache()->flush();

			exit;
		}

		$config_vars = [];

		// You can add settings for your plugins
		$this->hook('addSettings', [&$config_vars], $this->context['lp_plugins']);

		// Saving of plugin settings
		if ($this->request()->has('save')) {
			checkSession();

			$plugin_name = $this->post('plugin_name');

			$plugin_options = [];
			foreach ($config_vars[$plugin_name] as $var) {
				if ($this->post()->has($var[1])) {
					if ($var[0] === 'check') {
						//$plugin_options[$var[1]] = (int) $this->validate($this->post($var[1]), 'bool');
						$plugin_options[$var[1]] = $this->validate($this->post($var[1]), 'bool');
					} elseif ($var[0] === 'int') {
						$plugin_options[$var[1]] = $this->validate($this->post($var[1]), 'int');
					} elseif ($var[0] === 'float') {
						$plugin_options[$var[1]] = $this->validate($this->post($var[1]), 'float');
					} elseif ($var[0] === 'multicheck') {
						$plugin_options[$var[1]] = [];

						foreach ($this->post($var[1]) as $key => $value) {
							$plugin_options[$var[1]][$key] = (int) $this->validate($value, 'bool');
						}

						$plugin_options[$var[1]] = json_encode($plugin_options[$var[1]]);
					} elseif ($var[0] === 'url') {
						$plugin_options[$var[1]] = $this->validate($this->post($var[1]), 'url');
					} elseif ($var[0] === 'select' && ! empty($var['multiple'])) {
						$plugin_options[$var[1]] = json_encode($this->post($var[1]));
					} else {
						$plugin_options[$var[1]] = $this->post($var[1]);
					}
				}
			}

			// You can do additional actions after settings saving
			$this->hook('saveSettings', [&$plugin_options], $this->context['lp_plugins']);

			$this->updateSettings($plugin_name, $plugin_options);

			exit;
		}

		$this->context['all_lp_plugins'] = array_map(function ($item) use ($config_vars) {
			$composer = false;

			$snake_name = $this->getSnakeName($item);

			try {
				$className = '\Bugo\LightPortal\Addons\\' . $item . '\\' . $item;
				$addonClass = new ReflectionClass($className);

				if ($addonClass->hasProperty('author'))
					$author = $addonClass->getProperty('author')->getValue(new $className);

				if ($addonClass->hasProperty('link'))
					$link = $addonClass->getProperty('link')->getValue(new $className);

				$composer = is_file(dirname($addonClass->getFileName()) . DIRECTORY_SEPARATOR . 'composer.json');
			} catch (ReflectionException $e) {
				if (isset($this->context['lp_can_donate'][$item])) {
					$this->context['lp_loaded_addons'][$snake_name]['type'] = $this->context['lp_can_donate'][$item]['type'] ?? 'other';
					$special = $this->txt['lp_can_donate'];
				}

				if (isset($this->context['lp_can_download'][$item])) {
					$this->context['lp_loaded_addons'][$snake_name]['type'] = $this->context['lp_can_download'][$item]['type'] ?? 'other';
					$special = $this->txt['lp_can_download'];
				}
			}

			return [
				'name'       => $item,
				'snake_name' => $snake_name,
				'desc'       => $this->txt['lp_' . $snake_name]['description'] ?? '',
				'author'     => $author ?? '',
				'link'       => $link ?? '',
				'status'     => in_array($item, $this->context['lp_enabled_plugins']) ? 'on' : 'off',
				'types'      => $this->getTypes($snake_name),
				'special'    => $special ?? '',
				'settings'   => $config_vars[$snake_name] ?? [],
				'composer'   => $composer,
			];
		}, $this->context['lp_plugins']);

		$this->prepareAddonChart();

		// Sort plugin list
		$this->context['current_filter'] = $this->post('filter', 'all');

		if ($this->post()->has('filter')) {
			$filter = $this->post('filter');
			$this->context['all_lp_plugins'] = array_filter(
				$this->context['all_lp_plugins'],
				fn($item) => ! in_array($filter, array_keys($this->context['lp_plugin_types'])) || in_array($this->context['lp_plugin_types'][$filter], array_keys($item['types']))
			);
		}

		$this->context['sub_template'] = 'manage_plugins';
	}

	private function extendPluginList()
	{
		$this->context['lp_can_donate'] = [];
		$this->context['lp_can_download'] = [];

		if (($xml = $this->cache()->get('custom_addon_list', 259200)) === null) {
			$link = 'https://dragomano.ru/addons.json';

			$addon_list = fetch_web_data($link);

			if (empty($addon_list))
				return;

			$xml = smf_json_decode($addon_list, true);

			$this->cache()->put('custom_addon_list', $xml, 259200);
		}

		if (empty($xml) || ! is_array($xml))
			return;

		if ($xml['donate']) {
			foreach ($xml['donate'] as $addon) {
				$this->context['lp_plugins'][] = $addon['name'];
				$this->context['lp_can_donate'][$addon['name']] = $addon;
			}
		}

		if ($xml['download']) {
			foreach ($xml['download'] as $addon) {
				$this->context['lp_plugins'][] = $addon['name'];
				$this->context['lp_can_download'][$addon['name']] = $addon;
			}
		}

		$this->context['lp_plugins'] = array_unique($this->context['lp_plugins']);
	}

	private function getTypes(string $snake_name): array
	{
		if (empty($snake_name) || empty($type = $this->context['lp_loaded_addons'][$snake_name]['type'] ?? ''))
			return [$this->txt['not_applicable'] => ''];

		$types = explode(' ', $type);
		if (isset($types[1])) {
			$all_types = [];
			foreach ($types as $t) {
				$all_types[$this->context['lp_plugin_types'][$t]] = $this->getTypeClass($t);
			}

			return $all_types;
		}

		return [$this->context['lp_plugin_types'][$type] => $this->getTypeClass($type)];
	}

	private function getTypeClass(string $type): string
	{
		return ' lp_type_' . $type;
	}

	private function prepareAddonChart()
	{
		if ($this->request()->has('chart') === false)
			return;

		$typeCount = [];
		foreach ($this->context['all_lp_plugins'] as $plugin) {
			$types = array_merge(array_keys($plugin['types']));
			foreach ($types as $type) {
				$key = array_search($type, $this->txt['lp_plugins_types']);

				if ($key === false)
					$key = 7;

				$typeCount[$key] ??= 0;
				$typeCount[$key]++;
			}
		}

		if (empty($typeCount))
			return;

		$this->context['lp_addon_chart'] = true;

		ksort($typeCount);

		$this->context['insert_after_template'] .= '
		<script src="https://cdn.jsdelivr.net/npm/chart.js@3/dist/chart.min.js"></script>
		<script>
			const pageChart = document.querySelector("#addonChart");
			new Chart(pageChart, {
				type: "pie",
				data: {
					labels: ["' . implode('", "', $this->context['lp_plugin_types']) . '"],
					datasets: [{
						data: [' . implode(', ', $typeCount) . '],
						backgroundColor: ["#667d99", "#5f2c8c", "#48bf83", "#9354ca", "#91ae26", "#ef564f", "#d68b4f", "#2361ad", "#ac7bd6", "#a39d47", "#2a7750", "#c61a12", "#414141"]
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

	private function updateSettings(string $plugin_name, array $options = [])
	{
		if (empty($options))
			return;

		$settings = new Ini(dirname(__DIR__) . '/Addons/settings.ini');
		$settings->removeSection($plugin_name);
		$settings->addSection($plugin_name);
		$settings->addValues($plugin_name, $options);
		$settings->write();
	}
}
