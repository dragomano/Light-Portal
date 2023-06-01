<?php

declare(strict_types=1);

/**
 * PluginArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.2
 */

namespace Bugo\LightPortal\Areas;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Repositories\PluginRepository;
use ReflectionClass;
use ReflectionException;

if (! defined('SMF'))
	die('No direct access...');

final class PluginArea
{
	use Helper;

	private PluginRepository $repository;

	public function __construct()
	{
		$this->repository = new PluginRepository();
	}

	public function main(): void
	{
		$this->loadLanguage('ManageMaintenance');
		$this->loadTemplate('LightPortal/ManagePlugins', 'manage_plugins');
		$this->loadJavaScriptFile('light_portal/jscolor.min.js', ['minimize' => true]);

		$this->context['page_title'] = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_plugins_manage'];
		$this->context['post_url'] = $this->scripturl . '?action=admin;area=lp_plugins;save';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => sprintf($this->txt['lp_plugins_manage_description'], 'https://github.com/dragomano/Light-Portal/wiki/How-to-create-a-plugin'),
		];

		$this->context['lp_plugins'] = $this->getEntityList('plugin');

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

			$this->updateSettings(['lp_enabled_plugins' => implode(',', array_unique(array_intersect($this->context['lp_enabled_plugins'], $this->context['lp_plugins'])))]);

			$this->updateAssetMtime($this->context['lp_plugins'][$plugin_id]);

			$this->cache()->flush();

			exit;
		}

		$config_vars = [];

		// You can add settings for your plugins
		$this->hook('addSettings', [&$config_vars], $this->context['lp_plugins']);

		// Saving of plugin settings
		if ($this->request()->has('save')) {
			$this->checkSession();

			$plugin_name = $this->request('plugin_name');

			$plugin_options = [];
			foreach ($config_vars[$plugin_name] as $var) {
				if ($this->request()->has($var[1])) {
					if ($var[0] === 'check') {
						$plugin_options[$var[1]] = $this->validate($this->request($var[1]), 'bool');
					} elseif ($var[0] === 'int') {
						$plugin_options[$var[1]] = $this->validate($this->request($var[1]), 'int');
					} elseif ($var[0] === 'float') {
						$plugin_options[$var[1]] = $this->validate($this->request($var[1]), 'float');
					} elseif ($var[0] === 'multicheck') {
						$plugin_options[$var[1]] = [];

						foreach ($this->request($var[1]) as $key => $value) {
							$plugin_options[$var[1]][$key] = (int) $this->validate($value, 'bool');
						}

						$plugin_options[$var[1]] = json_encode($plugin_options[$var[1]]);
					} elseif ($var[0] === 'url') {
						$plugin_options[$var[1]] = $this->validate($this->request($var[1]), 'url');
					} elseif ($var[0] === 'select' && ! empty($var['multiple'])) {
						$plugin_options[$var[1]] = json_encode($this->request($var[1]));
					} else {
						$plugin_options[$var[1]] = $this->request($var[1]);
					}
				}
			}

			// You can do additional actions after settings saving
			$this->hook('saveSettings', [&$plugin_options], $this->context['lp_plugins']);

			$this->repository->changeSettings($plugin_name, $plugin_options);

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
			} catch (ReflectionException) {
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
				'caution'    => $this->txt['lp_' . $snake_name]['caution'] ?? '',
				'note'       => $this->txt['lp_' . $snake_name]['note'] ?? '',
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
		$this->context['current_filter'] = $this->request('filter', 'all');

		if ($this->request()->has('filter')) {
			$filter = $this->request('filter');
			$this->context['all_lp_plugins'] = array_filter(
				$this->context['all_lp_plugins'],
				fn($item) => ! in_array($filter, array_keys($this->context['lp_plugin_types'])) || in_array($this->context['lp_plugin_types'][$filter], array_keys($item['types']))
			);
		}
	}

	private function updateAssetMtime(string $plugin): void
	{
		$path = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR;

		$assets = [
			$path . 'style.css',
			$path . 'script.js',
		];

		foreach ($assets as $asset) {
			if (is_file($asset))
				touch($asset);
		}
	}

	private function extendPluginList(): void
	{
		$this->context['lp_can_donate'] = [];
		$this->context['lp_can_download'] = [];

		if (($xml = $this->cache()->get('custom_addon_list', 259200)) === null) {
			$link = 'https://api.jsonserve.com/SOCqve';

			$addon_list = $this->fetchWebData($link);

			if (empty($addon_list))
				return;

			$xml = $this->jsonDecode($addon_list, true);

			$this->cache()->put('custom_addon_list', $xml, 259200);
		}

		if (empty($xml) || ! is_array($xml))
			return;

		if (isset($xml['donate'])) {
			foreach ($xml['donate'] as $addon) {
				$this->context['lp_plugins'][] = $addon['name'];
				$this->context['lp_can_donate'][$addon['name']] = $addon;
			}
		}

		if (isset($xml['download'])) {
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

	private function prepareAddonChart(): void
	{
		if ($this->request()->hasNot('chart'))
			return;

		$typeCount = [];
		foreach ($this->context['all_lp_plugins'] as $plugin) {
			$types = [...array_keys($plugin['types'])];
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
		<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.js"></script>
		<script>
			const pageChart = document.querySelector("#addonChart");
			const myChart = new Chart(pageChart, {
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
}
