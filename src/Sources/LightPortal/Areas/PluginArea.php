<?php

declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Areas;

use Bugo\Compat\{Config, Lang, Theme};
use Bugo\Compat\{User, Utils, WebFetchApi};
use Bugo\LightPortal\Args\SettingsArgs;
use Bugo\LightPortal\Enums\{PortalHook, VarType};
use Bugo\LightPortal\EventManager;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\PluginHandler;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Utils\{CacheTrait, EntityDataTrait, Icon};
use Bugo\LightPortal\Utils\{Language, RequestTrait, Setting, Str};
use ReflectionClass;
use ReflectionException;

use function array_filter;
use function array_flip;
use function array_intersect;
use function array_keys;
use function array_map;
use function array_search;
use function array_unique;
use function dirname;
use function explode;
use function implode;
use function in_array;
use function is_file;
use function json_encode;
use function ksort;
use function ltrim;
use function sort;
use function sprintf;
use function touch;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class PluginArea
{
	use CacheTrait;
	use EntityDataTrait;
	use RequestTrait;

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

		Theme::loadCSSFile(
			'https://cdn.jsdelivr.net/combine/npm/@vueform/multiselect@2/themes/default.min.css,npm/@vueform/toggle@2/themes/default.min.css',
			['external' => true]
		);

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_plugins_manage'];
		Utils::$context['post_url']   = Config::$scripturl . '?action=admin;area=lp_plugins;save';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => sprintf(
				Lang::$txt['lp_plugins_manage_description'],
				'https://github.com/dragomano/Light-Portal/wiki/How-to-create-a-plugin'
			),
		];

		Utils::$context['lp_plugins'] = $this->getEntityData('plugin');

		$this->extendPluginList();

		Utils::$context['lp_plugins_extra'] = Lang::$txt['lp_plugins'] . ' (' . count(Utils::$context['lp_plugins']) . ')';

		$this->handleToggle();

		$settings = [];

		PluginHandler::getInstance(Utils::$context['lp_plugins']);

		// You can add settings for your plugins
		EventManager::getInstance()->dispatch(PortalHook::addSettings, new Event(new SettingsArgs($settings)));

		$this->handleSave($settings);
		$this->prepareAddonList($settings);
		$this->prepareAddonChart();
		$this->prepareJsonData();
	}

	private function handleToggle(): void
	{
		if ($this->request()->hasNot('toggle'))
			return;

		$data = $this->request()->json();

		$pluginId = (int) $data['plugin'];

		$enabledPlugins = Setting::getEnabledPlugins();

		if ($data['status'] === 'on') {
			$enabledPlugins = array_filter(
				$enabledPlugins,
				static fn($item) => $item !== Utils::$context['lp_plugins'][$pluginId]
			);
		} else {
			$enabledPlugins[] = Utils::$context['lp_plugins'][$pluginId];
		}

		sort($enabledPlugins);

		Config::updateModSettings([
			'lp_enabled_plugins' => implode(
				',', array_unique(
					array_intersect($enabledPlugins, Utils::$context['lp_plugins'])
				)
			)
		]);

		$this->updateAssetMtime(Utils::$context['lp_plugins'][$pluginId]);

		$this->cache()->flush();

		exit(json_encode(['success' => true]));
	}

	private function handleSave(array $configVars): void
	{
		if ($this->request()->hasNot('save'))
			return;

		User::$me->checkSession();

		$name = $this->request('plugin_name');
		$settings = [];

		foreach ($configVars[$name] as $var) {
			if ($this->request()->has($var[1])) {
				if ($var[0] === 'check') {
					$settings[$var[1]] = VarType::BOOLEAN->filter($this->request($var[1]));
				} elseif ($var[0] === 'int') {
					$settings[$var[1]] = VarType::INTEGER->filter($this->request($var[1]));
				} elseif ($var[0] === 'float') {
					$settings[$var[1]] = VarType::FLOAT->filter($this->request($var[1]));
				} elseif ($var[0] === 'url') {
					$settings[$var[1]] = VarType::URL->filter($this->request($var[1]));
				} elseif ($var[0] === 'multiselect') {
					$settings[$var[1]] = ltrim(implode(',', $this->request($var[1])), ',');
				} else {
					$settings[$var[1]] = $this->request($var[1]);
				}
			}
		}

		// You can do additional actions after settings saving
		EventManager::getInstance()->dispatch(PortalHook::saveSettings, new Event(new SettingsArgs($settings)));

		$this->repository->changeSettings($name, $settings);

		exit(json_encode(['success' => true]));
	}

	private function prepareAddonList(array $configVars): void
	{
		Utils::$context['all_lp_plugins'] = array_map(function ($item) use ($configVars) {
			$composer = false;

			$snakeName = Str::getSnakeName($item);

			try {
				$className = '\Bugo\LightPortal\Plugins\\' . $item . '\\' . $item;
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
					Utils::$context['lp_loaded_addons'][$snakeName]['type'] = Utils::$context['lp_can_donate'][$item]['type'] ?? 'other';
					$special = 'can_donate';
				}

				if (isset(Utils::$context['lp_can_download'][$item])) {
					Utils::$context['lp_loaded_addons'][$snakeName]['type'] = Utils::$context['lp_can_download'][$item]['type'] ?? 'other';
					$special = 'can_download';
				}
			}

			return [
				'name'       => $item,
				'snake_name' => $snakeName,
				'desc'       => Lang::$txt['lp_' . $snakeName]['description'] ?? '',
				'author'     => $author ?? '',
				'link'       => $link ?? '',
				'status'     => in_array($item, Setting::getEnabledPlugins()) ? 'on' : 'off',
				'types'      => $this->getTypes($snakeName),
				'special'    => $special ?? '',
				'settings'   => $configVars[$snakeName] ?? [],
				'composer'   => $composer,
				'saveable'   => $saveable ?? true,
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
				$key = array_search($type, Lang::$txt['lp_plugins_types'], true);

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
						backgroundColor: [
							"#667d99", "#5f2c8c", "#48bf83", "#9354ca", "#91ae26", "#ef564f",
							"#d68b4f", "#2361ad", "#ac7bd6", "#a39d47", "#2a7750", "#c61a12", "#414141"
						]
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
			'lang'    => Language::getNameFromLocale(User::$info['language']),
		];

		$pluginsData = [
			'list'     => Utils::$context['all_lp_plugins'],
			'types'    => Utils::$context['lp_plugin_types'],
			'donate'   => Utils::$context['lp_can_donate'] ?? [],
			'download' => Utils::$context['lp_can_download'] ?? [],
		];

		// Add additional data
		$allPlugins = array_keys(Utils::$context['lp_loaded_addons'] ?? []);

		foreach ($allPlugins as $plugin) {
			if (isset(Lang::$txt['lp_' . $plugin]))
				$txtData['lp_' . $plugin] = Lang::$txt['lp_' . $plugin];

			if (! empty(Utils::$context['lp_' . $plugin . '_plugin']))
				$contextData['lp_' . $plugin] = Utils::$context['lp_' . $plugin . '_plugin'];
		}

		Utils::$context['lp_json'] = json_encode([
			'txt'     => $txtData,
			'context' => $contextData,
			'plugins' => $pluginsData,
			'icons'   => Icon::all(),
		]);
	}

	private function updateAssetMtime(string $plugin): void
	{
		$path = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR;

		$assets = [
			$path . 'style.css',
			$path . 'script.js',
		];

		foreach ($assets as $asset) {
			if (is_file($asset)) {
				touch($asset);
			}
		}
	}

	private function extendPluginList(): void
	{
		Utils::$context['lp_can_donate'] = [];
		Utils::$context['lp_can_download'] = [];

		if (($xml = $this->cache()->get('custom_addon_list', 259200)) === null) {
			$addonList = WebFetchApi::fetch(LP_PLUGIN_LIST);

			if (empty($addonList))
				return;

			$xml = Utils::jsonDecode($addonList, true);

			$this->cache()->put('custom_addon_list', $xml, 259200);
		}

		if (isset($xml[0])) {
			$xml = $xml[0];
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

		sort(Utils::$context['lp_plugins']);
	}

	private function getTypes(string $snakeName): array
	{
		if (empty($snakeName) || empty($type = Utils::$context['lp_loaded_addons'][$snakeName]['type'] ?? ''))
			return [Lang::$txt['not_applicable'] => ''];

		$types = explode(' ', (string) $type);
		if (isset($types[1])) {
			$allTypes = [];
			foreach ($types as $t) {
				$allTypes[Utils::$context['lp_plugin_types'][$t]] = $this->getTypeClass($t);
			}

			return $allTypes;
		}

		return [Utils::$context['lp_plugin_types'][$type] => $this->getTypeClass($type)];
	}

	private function getTypeClass(string $type): string
	{
		return ' lp_type_' . $type;
	}
}
