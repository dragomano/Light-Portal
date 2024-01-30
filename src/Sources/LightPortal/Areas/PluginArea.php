<?php

declare(strict_types=1);

/**
 * PluginArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\{Config, Icon, Lang, Theme, User, Utils};
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
		Lang::load('ManageMaintenance');

		Theme::loadTemplate('LightPortal/ManagePlugins');

		Utils::$context['sub_template'] = 'manage_plugins';

		Theme::loadExtCSS('https://cdn.jsdelivr.net/combine/npm/@vueform/multiselect@2/themes/default.min.css,npm/@vueform/toggle@2/themes/default.min.css');

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_plugins_manage'];
		Utils::$context['post_url']   = Config::$scripturl . '?action=admin;area=lp_plugins;save';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => sprintf(Lang::$txt['lp_plugins_manage_description'], 'https://github.com/dragomano/Light-Portal/wiki/How-to-create-a-plugin'),
		];

		Utils::$context['lp_plugins'] = $this->getEntityList('plugin');

		$this->extendPluginList();

		Utils::$context['lp_plugins_extra'] = Lang::$txt['lp_plugins'] . ' (' . count(Utils::$context['lp_plugins']) . ')';

		$this->handleToggle();

		$config_vars = [];

		// You can add settings for your plugins
		$this->hook('addSettings', [&$config_vars], Utils::$context['lp_plugins']);

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
			Utils::$context['lp_enabled_plugins'] = array_filter(Utils::$context['lp_enabled_plugins'], fn($item) => $item !== Utils::$context['lp_plugins'][$plugin_id]);
		} else {
			Utils::$context['lp_enabled_plugins'][] = Utils::$context['lp_plugins'][$plugin_id];
		}

		sort(Utils::$context['lp_enabled_plugins']);

		Config::updateModSettings(['lp_enabled_plugins' => implode(',', array_unique(array_intersect(Utils::$context['lp_enabled_plugins'], Utils::$context['lp_plugins'])))]);

		$this->updateAssetMtime(Utils::$context['lp_plugins'][$plugin_id]);

		$this->cache()->flush();

		exit(json_encode(['success' => true]));
	}

	private function handleSave(array $config_vars): void
	{
		if ($this->request()->hasNot('save'))
			return;

		User::$me->checkSession();

		$plugin_name = $this->request('plugin_name');
		$plugin_options = [];

		foreach ($config_vars[$plugin_name] as $var) {
			if ($this->request()->has($var[1])) {
				if ($var[0] === 'check') {
					$plugin_options[$var[1]] = $this->filterVar($this->request($var[1]), 'bool');
				} elseif ($var[0] === 'int') {
					$plugin_options[$var[1]] = $this->filterVar($this->request($var[1]), 'int');
				} elseif ($var[0] === 'float') {
					$plugin_options[$var[1]] = $this->filterVar($this->request($var[1]), 'float');
				} elseif ($var[0] === 'url') {
					$plugin_options[$var[1]] = $this->filterVar($this->request($var[1]), 'url');
				} elseif ($var[0] === 'multiselect') {
					$plugin_options[$var[1]] = ltrim(implode(',', $this->request($var[1])), ',');
				} else {
					$plugin_options[$var[1]] = $this->request($var[1]);
				}
			}
		}

		// You can do additional actions after settings saving
		$this->hook('saveSettings', [&$plugin_options], Utils::$context['lp_plugins']);

		$this->repository->changeSettings($plugin_name, $plugin_options);

		exit(json_encode(['success' => true]));
	}

	private function prepareAddonList(array $config_vars): void
	{
		Utils::$context['all_lp_plugins'] = array_map(function ($item) use ($config_vars) {
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
				if (isset(Utils::$context['lp_can_donate'][$item])) {
					Utils::$context['lp_loaded_addons'][$snake_name]['type'] = Utils::$context['lp_can_donate'][$item]['type'] ?? 'other';
					$special = 'can_donate';
				}

				if (isset(Utils::$context['lp_can_download'][$item])) {
					Utils::$context['lp_loaded_addons'][$snake_name]['type'] = Utils::$context['lp_can_download'][$item]['type'] ?? 'other';
					$special = 'can_download';
				}
			}

			return [
				'name'        => $item,
				'snake_name'  => $snake_name,
				'desc'        => Lang::$txt['lp_' . $snake_name]['description'] ?? '',
				'author'      => $author ?? '',
				'link'        => $link ?? '',
				'status'      => in_array($item, Utils::$context['lp_enabled_plugins']) ? 'on' : 'off',
				'types'       => $this->getTypes($snake_name),
				'special'     => $special ?? '',
				'settings'    => $config_vars[$snake_name] ?? [],
				'composer'    => $composer,
				'saveable'    => $saveable ?? true,
			];
		}, Utils::$context['lp_plugins']);
	}

	private function prepareAddonChart(): void
	{
		if ($this->request()->hasNot('chart'))
			return;

		$typeCount = [];
		foreach (Utils::$context['all_lp_plugins'] as $plugin) {
			$types = [...array_keys($plugin['types'])];
			foreach ($types as $type) {
				$key = array_search($type, Lang::$txt['lp_plugins_types']);

				if ($key === false)
					$key = 7;

				$typeCount[$key] ??= 0;
				$typeCount[$key]++;
			}
		}

		if (empty($typeCount))
			return;

		Utils::$context['lp_addon_chart'] = true;

		ksort($typeCount);

		Utils::$context['insert_after_template'] .= /** @lang text */
			'
		<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.js"></script>
		<script>
			new Chart("addon_chart", {
				type: "pie",
				data: {
					labels: ["' . implode('", "', Utils::$context['lp_plugin_types']) . '"],
					datasets: [{
						data: [' . implode(', ', $typeCount) . '],
						backgroundColor: ["#667d99", "#5f2c8c", "#48bf83", "#9354ca", "#91ae26", "#ef564f", "#d68b4f", "#2361ad", "#ac7bd6", "#a39d47", "#2a7750", "#c61a12", "#414141"]
					}]
				},
				options: {
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
			'plugins'           => Lang::$txt['lp_plugins'],
			'apply_filter'      => Lang::$txt['apply_filter'],
			'all'               => Lang::$txt['all'],
			'lp_active_only'    => Lang::$txt['lp_active_only'],
			'lp_plugins_desc'   => Lang::$txt['lp_plugins_desc'],
			'lp_can_donate'     => Lang::$txt['lp_can_donate'],
			'lp_can_download'   => Lang::$txt['lp_can_download'],
			'lp_caution'        => Lang::$txt['lp_caution'],
			'lp_block_note'     => Lang::$txt['lp_block_note'],
			'not_applicable'    => Lang::$txt['not_applicable'],
			'settings'          => Lang::$txt['settings'],
			'settings_saved'    => Lang::$txt['settings_saved'],
			'find_close'        => Lang::$txt['find_close'],
			'save'              => Lang::$txt['save'],
			'no_matches'        => Lang::$txt['no_matches'],
			'search'            => Lang::$txt['search'],
			'remove'            => Lang::$txt['remove'],
			'no'                => Lang::$txt['no'],
			'lp_plugins_select' => Lang::$txt['lp_plugins_select'],
		];

		$contextData = [
			'locale'  => Lang::$txt['lang_dictionary'],
			'postUrl' => Utils::$context['post_url'],
			'charset' => Utils::$context['character_set'],
			'user'    => Utils::$context['user'],
			'rtl'     => Utils::$context['right_to_left'],
		];

		$pluginsData = [
			'list'     => Utils::$context['all_lp_plugins'],
			'types'    => Utils::$context['lp_plugin_types'],
			'donate'   => Utils::$context['lp_can_donate'] ?? [],
			'download' => Utils::$context['lp_can_download'] ?? [],
		];

		// Add additional data
		$all_plugins = array_keys(Utils::$context['lp_loaded_addons']);

		foreach ($all_plugins as $plugin) {
			if (isset(Lang::$txt['lp_' . $plugin]))
				$txtData['lp_' . $plugin] = Lang::$txt['lp_' . $plugin];

			if (! empty(Utils::$context['lp_' . $plugin . '_plugin']))
				$contextData['lp_' . $plugin] = Utils::$context['lp_' . $plugin . '_plugin'];
		}

		Utils::$context['lp_json']['txt']      = json_encode($txtData);
		Utils::$context['lp_json']['context']  = json_encode($contextData);
		Utils::$context['lp_json']['plugins']  = json_encode($pluginsData);
		Utils::$context['lp_json']['icons']    = json_encode(Icon::all());
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
		Utils::$context['lp_can_donate'] = [];
		Utils::$context['lp_can_download'] = [];

		if (($xml = $this->cache()->get('custom_addon_list', 259200)) === null) {
			$addon_list = $this->fetchWebData(LP_PLUGIN_LIST);

			if (empty($addon_list))
				return;

			$xml = Utils::jsonDecode($addon_list, true);

			$this->cache()->put('custom_addon_list', $xml, 259200);
		}

		if (empty($xml) || ! is_array($xml))
			return;

		foreach ($xml['donate'] as $addon) {
			Utils::$context['lp_plugins'][] = $addon['name'];
			Utils::$context['lp_can_donate'][$addon['name']] = $addon;
		}

		foreach ($xml['download'] as $addon) {
			Utils::$context['lp_plugins'][] = $addon['name'];
			Utils::$context['lp_can_download'][$addon['name']] = $addon;
		}

		Utils::$context['lp_plugins'] = array_keys(array_flip(Utils::$context['lp_plugins']));

		asort(Utils::$context['lp_plugins']);
	}

	private function getTypes(string $snake_name): array
	{
		if (empty($snake_name) || empty($type = Utils::$context['lp_loaded_addons'][$snake_name]['type'] ?? ''))
			return [Lang::$txt['not_applicable'] => ''];

		$types = explode(' ', $type);
		if (isset($types[1])) {
			$all_types = [];
			foreach ($types as $t) {
				$all_types[Utils::$context['lp_plugin_types'][$t]] = $this->getTypeClass($t);
			}

			return $all_types;
		}

		return [Utils::$context['lp_plugin_types'][$type] => $this->getTypeClass($type)];
	}

	private function getTypeClass(string $type): string
	{
		return ' lp_type_' . $type;
	}
}
