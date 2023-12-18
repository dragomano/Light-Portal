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
 * @version 2.4
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
		$this->loadExtCSS('https://cdn.jsdelivr.net/combine/npm/@vueform/multiselect@2/themes/default.min.css,npm/@vueform/toggle@2/themes/default.min.css');

		$this->context['page_title'] = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_plugins_manage'];
		$this->context['post_url']   = $this->scripturl . '?action=admin;area=lp_plugins;save';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => sprintf($this->txt['lp_plugins_manage_description'], 'https://github.com/dragomano/Light-Portal/wiki/How-to-create-a-plugin'),
		];

		$this->context['lp_plugins'] = $this->getEntityList('plugin');

		$this->extendPluginList();

		$this->context['lp_plugins_extra'] = $this->txt['lp_plugins'] . ' (' . count($this->context['lp_plugins']) . ')';

		$this->handleToggle();

		$config_vars = [];

		// You can add settings for your plugins
		$this->hook('addSettings', [&$config_vars], $this->context['lp_plugins']);

		$this->handleSave($config_vars);

		$this->prepareAddonList($config_vars);

		$this->prepareAddonChart();

		$this->prepareJsonData();
	}

	private function handleToggle(): void
	{
		if ($this->request()->hasNot('toggle'))
			return;

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

		exit(json_encode(['success' => true]));
	}

	private function handleSave(array $config_vars): void
	{
		if ($this->request()->hasNot('save'))
			return;

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
				} elseif ($var[0] === 'url') {
					$plugin_options[$var[1]] = $this->validate($this->request($var[1]), 'url');
				} elseif ($var[0] === 'multiselect') {
					$plugin_options[$var[1]] = ltrim(implode(',', $this->request($var[1])), ',');
				} else {
					$plugin_options[$var[1]] = $this->request($var[1]);
				}
			}
		}

		// You can do additional actions after settings saving
		$this->hook('saveSettings', [&$plugin_options], $this->context['lp_plugins']);

		$this->repository->changeSettings($plugin_name, $plugin_options);

		exit(json_encode(['success' => true]));
	}

	private function prepareAddonList(array $config_vars): void
	{
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

				if ($addonClass->hasProperty('saveable'))
					$saveable = $addonClass->getProperty('saveable')->getValue(new $className);

				$composer = is_file(dirname($addonClass->getFileName()) . DIRECTORY_SEPARATOR . 'composer.json');
			} catch (ReflectionException) {
				if (isset($this->context['lp_can_donate'][$item])) {
					$this->context['lp_loaded_addons'][$snake_name]['type'] = $this->context['lp_can_donate'][$item]['type'] ?? 'other';
					$special = 'can_donate';
				}

				if (isset($this->context['lp_can_download'][$item])) {
					$this->context['lp_loaded_addons'][$snake_name]['type'] = $this->context['lp_can_download'][$item]['type'] ?? 'other';
					$special = 'can_download';
				}
			}

			return [
				'name'        => $item,
				'snake_name'  => $snake_name,
				'desc'        => $this->txt['lp_' . $snake_name]['description'] ?? '',
				'author'      => $author ?? '',
				'link'        => $link ?? '',
				'status'      => in_array($item, $this->context['lp_enabled_plugins']) ? 'on' : 'off',
				'types'       => $this->getTypes($snake_name),
				'special'     => $special ?? '',
				'settings'    => $config_vars[$snake_name] ?? [],
				'composer'    => $composer,
				'saveable'    => $saveable ?? true,
			];
		}, $this->context['lp_plugins']);
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
			const pageChart = document.querySelector("#addon_chart");
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

	private function prepareJsonData(): void
	{
		$txtData = [
			'plugins'           => $this->txt['lp_plugins'],
			'apply_filter'      => $this->txt['apply_filter'],
			'all'               => $this->txt['all'],
			'lp_plugins_desc'   => $this->txt['lp_plugins_desc'],
			'lp_can_donate'     => $this->txt['lp_can_donate'],
			'lp_can_download'   => $this->txt['lp_can_download'],
			'lp_caution'        => $this->txt['lp_caution'],
			'lp_block_note'     => $this->txt['lp_block_note'],
			'not_applicable'    => $this->txt['not_applicable'],
			'settings'          => $this->txt['settings'],
			'settings_saved'    => $this->txt['settings_saved'],
			'find_close'        => $this->txt['find_close'],
			'save'              => $this->txt['save'],
			'no_matches'        => $this->txt['no_matches'],
			'search'            => $this->txt['search'],
			'remove'            => $this->txt['remove'],
			'no'                => $this->txt['no'],
			'lp_plugins_select' => $this->txt['lp_plugins_select'],
		];

		$contextData = [
			'locale'  => $this->txt['lang_dictionary'],
			'postUrl' => $this->context['post_url'],
			'charset' => $this->context['character_set'],
			'user'    => $this->context['user'],
			'rtl'     => $this->context['right_to_left'],
		];

		$pluginsData = [
			'list'     => $this->context['all_lp_plugins'],
			'types'    => $this->context['lp_plugin_types'],
			'donate'   => $this->context['lp_can_donate'] ?? [],
			'download' => $this->context['lp_can_download'] ?? [],
		];

		// Add additional data
		$all_plugins = array_keys($this->context['lp_loaded_addons']);

		foreach ($all_plugins as $plugin) {
			if (isset($this->txt['lp_' . $plugin]))
				$txtData['lp_' . $plugin] = $this->txt['lp_' . $plugin];

			if (! empty($this->context['lp_' . $plugin . '_plugin']))
				$contextData['lp_' . $plugin] = $this->context['lp_' . $plugin . '_plugin'];
		}

		$this->context['lp_json']['txt']      = json_encode($txtData);
		$this->context['lp_json']['context']  = json_encode($contextData);
		$this->context['lp_json']['plugins']  = json_encode($pluginsData);
		$this->context['lp_json']['icons']    = json_encode($this->context['lp_icon_set']);
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
			$addon_list = $this->fetchWebData(LP_PLUGIN_LIST);

			if (empty($addon_list))
				return;

			$xml = $this->jsonDecode($addon_list);

			$this->cache()->put('custom_addon_list', $xml, 259200);
		}

		if (empty($xml) || ! is_array($xml))
			return;

		foreach ($xml['donate'] as $addon) {
			$this->context['lp_plugins'][] = $addon['name'];
			$this->context['lp_can_donate'][$addon['name']] = $addon;
		}

		foreach ($xml['download'] as $addon) {
			$this->context['lp_plugins'][] = $addon['name'];
			$this->context['lp_can_download'][$addon['name']] = $addon;
		}

		$this->context['lp_plugins'] = array_keys(array_flip($this->context['lp_plugins']));

		asort($this->context['lp_plugins']);
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
}
