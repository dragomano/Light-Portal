<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Areas;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\Compat\WebFetch\WebFetchApi;
use LightPortal\Enums\PluginType;
use LightPortal\Enums\PortalHook;
use LightPortal\Enums\Status;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Lists\PluginList;
use LightPortal\Repositories\BlockRepositoryInterface;
use LightPortal\Repositories\PluginRepositoryInterface;
use LightPortal\UI\TemplateLoader;
use LightPortal\Utils\DateTime;
use LightPortal\Utils\Icon;
use LightPortal\Utils\InputFilter;
use LightPortal\Utils\Language;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasResponse;

use function LightPortal\app;

use const LP_NAME;
use const LP_VERSION;

if (! defined('SMF'))
	die('No direct access...');

final readonly class PluginArea
{
	use HasCache;
	use HasRequest;
	use HasResponse;

	public function __construct(
		private PluginRepositoryInterface $repository,
		private EventDispatcherInterface $dispatcher,
		private InputFilter $inputFilter
	) {}

	public function main(): void
	{
		Lang::load('ManageMaintenance');

		Utils::$context['page_title'] = __('lp_portal') . ' - ' . __('lp_plugins_manage');

		Utils::$context['post_url'] = Config::$scripturl . '?action=admin;area=lp_plugins;save';

		Utils::$context['lp_plugins_api_endpoint'] = Config::$scripturl . '?action=admin;area=lp_plugins;api';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => sprintf(
				__('lp_plugins_manage_description'),
				'https://github.com/dragomano/Light-Portal/wiki/How-to-create-a-plugin'
			),
		];

		Utils::$context['lp_plugins'] = app(PluginList::class)();

		$this->extendPluginList();

		Utils::$context['lp_plugins_extra'] = __('lp_plugins') . ' (' . count(Utils::$context['lp_plugins']) . ')';

		$this->handleToggle();

		$settings = [];

		$this->dispatcher
			->withPlugins(Utils::$context['lp_plugins'])
			->dispatch(PortalHook::addSettings, ['settings' => &$settings]);

		$this->handleSave($settings);
		$this->prepareAddonList($settings);
		$this->prepareAddonChart();
		$this->handleApi();

		TemplateLoader::fromFile('admin/plugin_index');
	}

	private function handleToggle(): void
	{
		if ($this->request()->hasNot('toggle'))
			return;

		$data           = $this->request()->json();
		$pluginId       = (int) $data['plugin'];
		$pluginName     = Utils::$context['lp_plugins'][$pluginId] ?? '';
		$enabledPlugins = Setting::getEnabledPlugins();

		if ($data['status'] === 'on') {
			$enabledPlugins = array_filter(
				$enabledPlugins,
				static fn($item) => $item !== $pluginName
			);
		} else {
			$enabledPlugins[] = $pluginName;
		}

		sort($enabledPlugins);

		Config::updateModSettings([
			'lp_enabled_plugins' => implode(
				',', array_unique(
					array_intersect($enabledPlugins, Utils::$context['lp_plugins'])
				)
			)
		]);

		$this->removeAssets();
		$this->handleBlockPluginToggle($pluginName, $data);

		$this->cache()->flush();
		$this->response()->exit(['success' => true]);
	}

	private function handleSave(array $configVars): void
	{
		if ($this->request()->hasNot('save'))
			return;

		User::$me->checkSession();

		$name = $this->request()->get('plugin_name');

		$settings = $this->inputFilter->filter($configVars[$name]);

		$this->dispatcher
			->withPlugins(Utils::$context['lp_plugins'])
			->dispatch(PortalHook::saveSettings, ['settings' => &$settings]);

		$this->repository->changeSettings($name, $settings);

		$this->response()->exit(['success' => true]);
	}

	private function handleBlockPluginToggle(string $pluginName, array $data): void
	{
		$snakeName     = Str::getSnakeName($pluginName);
		$pluginData    = Utils::$context['lp_loaded_addons'][$snakeName] ?? [];
		$isBlockPlugin = ($pluginData['type'] ?? '') === PluginType::BLOCK->name();

		if ($data['status'] === 'on' && $isBlockPlugin) {
			app(BlockRepositoryInterface::class)->updateStatusByType($snakeName, Status::INACTIVE->value);
		}
	}

	private function prepareAddonList(array $configVars): void
	{
		Utils::$context['all_lp_plugins'] = array_map(function ($item) use ($configVars) {
			$snakeName  = Str::getSnakeName($item);
			$pluginData = Utils::$context['lp_loaded_addons'][$snakeName] ?? [];

			$version  = $this->getVersion($item);
			$plugin   = Utils::$context['lp_download'][$item] ?? Utils::$context['lp_donate'][$item] ?? [];
			$outdated = DateTime::dateCompare($version, $plugin['version'] ?? '') ? __('lp_plugin_outdated') : null;

			if ($outdated) {
				$pluginData = [];
				$configVars[$snakeName] = [];
			}

			if ($pluginData === []) {
				if (isset(Utils::$context['lp_donate'][$item])) {
					Utils::$context['lp_loaded_addons'][$snakeName]['type'] = Utils::$context['lp_donate'][$item]['type'];
					$special = 'can_donate';
				}

				if (isset(Utils::$context['lp_download'][$item])) {
					Utils::$context['lp_loaded_addons'][$snakeName]['type'] = Utils::$context['lp_download'][$item]['type'];
					$special = 'can_download';
				}
			}

			return [
				'name'           => $item,
				'version'        => $version,
				'outdated'       => $outdated,
				'snakeName'      => $snakeName,
				'desc'           => $outdated ?? __('lp_' . $snakeName)['description'] ?? '',
				'status'         => in_array($item, Setting::getEnabledPlugins()) ? 'on' : 'off',
				'types'          => $this->getTypes($snakeName),
				'special'        => $special ?? '',
				'settings'       => $configVars[$snakeName] ?? [],
				'showSaveButton' => $pluginData['showSaveButton'] ?? false,
			];
		}, Utils::$context['lp_plugins']);
	}

	private function getVersion(string $item): string
	{
		$file = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . $item . '.php';

		if (! is_file($file)) {
			return '';
		}

		$docBlock = file_get_contents($file, length: 400);

		$version = '';
		if ($docBlock && preg_match('/@version\s+([0-9]+\.[0-9]+\.[0-9]+)/', $docBlock, $matches)) {
			$version = $matches[1];
		}

		return $version;
	}

	private function prepareAddonChart(): void
	{
		if ($this->request()->hasNot('chart'))
			return;

		$typeCount = [];
		foreach (Utils::$context['all_lp_plugins'] as $plugin) {
			$types = [...array_keys($plugin['types'])];
			foreach ($types as $type) {
				$key = array_search($type, __('lp_plugins_types'), true);

				if ($key === false) {
					$key = PluginType::OTHER->name();
				}

				$typeCount[$key] ??= 0;
				$typeCount[$key]++;
			}
		}

		if (empty($typeCount))
			return;

		Utils::$context['lp_addon_chart'] = true;

		ksort($typeCount);

		Utils::$context['insert_after_template'] .= /** @lang text */ '
		<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.js"></script>
		<script>
			new Chart("addon_chart", {
				type: "pie",
				data: {
					labels: ["' . implode('", "', Utils::$context['lp_plugin_types']) . '"],
					datasets: [{
						data: [' . implode(', ', $typeCount) . '],
						backgroundColor: ["' . implode('", "', PluginType::colors()) . '"]
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

	private function handleApi(): void
	{
		if ($this->request()->hasNot('api')) {
			return;
		}

		$this->response()->exit($this->preparedData());
	}

	private function preparedData(): array
	{
		$txtData = [
			'plugins'           => __('lp_plugins'),
			'apply_filter'      => __('apply_filter'),
			'list_view'         => __('lp_list_view'),
			'card_view'         => __('lp_card_view'),
			'all'               => __('all'),
			'lp_active_only'    => __('lp_active_only'),
			'lp_plugins_desc'   => __('lp_plugins_desc'),
			'lp_can_donate'     => __('lp_can_donate'),
			'lp_can_download'   => __('lp_can_download'),
			'lp_caution'        => __('lp_caution'),
			'lp_block_note'     => __('lp_block_note'),
			'not_applicable'    => __('not_applicable'),
			'settings'          => __('settings'),
			'settings_saved'    => __('settings_saved'),
			'find_close'        => __('find_close'),
			'save'              => __('save'),
			'no_options'        => __('lp_plugins_no_options'),
			'no_matches'        => __('no_matches'),
			'search'            => __('search'),
			'remove'            => __('remove'),
			'no'                => __('no'),
			'lp_plugins_select' => __('lp_plugins_select'),
		];

		$contextData = [
			'locale'  => __('lang_dictionary'),
			'postUrl' => Utils::$context['post_url'],
			'charset' => Utils::$context['character_set'],
			'user'    => Utils::$context['user'],
			'rtl'     => Utils::$context['right_to_left'],
			'lang'    => Language::getNameFromLocale(User::$me->language),
		];

		$pluginsData = [
			'list'     => Utils::$context['all_lp_plugins'],
			'types'    => Utils::$context['lp_plugin_types'],
			'donate'   => Utils::$context['lp_donate'] ?? [],
			'download' => Utils::$context['lp_download'] ?? [],
		];

		$allPlugins = array_keys(Utils::$context['lp_loaded_addons'] ?? []);

		foreach ($allPlugins as $plugin) {
			if (Lang::txtExists('lp_' . $plugin)) {
				$txtData['lp_' . $plugin] = __('lp_' . $plugin);
			}

			if (! empty(Utils::$context['lp_' . $plugin . '_plugin'])) {
				$contextData['lp_' . $plugin] = Utils::$context['lp_' . $plugin . '_plugin'];
			}
		}

		return [
			'txt'     => $txtData,
			'context' => $contextData,
			'plugins' => $pluginsData,
			'icons'   => Icon::all(),
		];
	}

	private function removeAssets(): void
	{
		$cssFile = Theme::$current->settings['default_theme_dir'] . '/css/light_portal/plugins.css';
		$jsFile  = Theme::$current->settings['default_theme_dir'] . '/scripts/light_portal/plugins.js';

		if (is_file($cssFile)) {
			@unlink($cssFile);
		}

		if (is_file($jsFile)) {
			@unlink($jsFile);
		}
	}

	private function extendPluginList(): void
	{
		Utils::$context['lp_donate'] = Utils::$context['lp_download'] = [];

		$cacheTTL = 3 * 24 * 60 * 60;

		if (($xml = $this->cache()->get('custom_addon_list', $cacheTTL)) === null) {
			$addonList = WebFetchApi::fetch(LP_PLUGIN_LIST);

			if (empty($addonList))
				return;

			$xml = Utils::jsonDecode($addonList, true);

			$this->cache()->put('custom_addon_list', $xml, $cacheTTL);
		}

		if (isset($xml[0])) {
			$xml = $xml[0];
		}

		if (empty($xml) || ! is_array($xml))
			return;

		if (empty($xml['version']) || $xml['version'] !== LP_VERSION)
			return;

		foreach ($xml['donate'] as $addon) {
			Utils::$context['lp_plugins'][] = $addon['name'];
			Utils::$context['lp_donate'][$addon['name']] = $addon;
		}

		foreach ($xml['download'] as $addon) {
			Utils::$context['lp_plugins'][] = $addon['name'];
			Utils::$context['lp_download'][$addon['name']] = $addon;
		}

		Utils::$context['lp_plugins'] = array_keys(array_flip(Utils::$context['lp_plugins']));

		sort(Utils::$context['lp_plugins']);
	}

	private function getTypes(string $snakeName): array
	{
		if (empty($snakeName) || empty($type = Utils::$context['lp_loaded_addons'][$snakeName]['type'] ?? '')) {
			return [__('not_applicable') => ''];
		}

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
