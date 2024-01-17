<?php declare(strict_types=1);

/**
 * ConfigArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas;

use Bugo\LightPortal\Areas\Config\{BasicConfig, CategoryConfig, ExtraConfig};
use Bugo\LightPortal\Areas\Config\{FeedbackConfig, MiscConfig, PanelConfig};
use Bugo\LightPortal\Areas\Export\{BlockExport, PageExport, PluginExport};
use Bugo\LightPortal\Areas\Import\{BlockImport, PageImport, PluginImport};
use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\{Config, Lang, User, Utils};

if (! defined('SMF'))
	die('No direct access...');

final class ConfigArea
{
	use Helper;

	public function adminAreas(array &$areas): void
	{
		$this->loadCSSFile('light_portal/virtual-select.min.css');
		$this->loadJSFile('light_portal/virtual-select.min.js');

		$this->loadJSFile('light_portal/bundle.min.js', ['defer' => true]);
		$this->loadJSFile('light_portal/admin.js', ['minimize' => true]);

		$this->loadLanguage('ManageSettings');

		$counter = array_search('layout', array_keys($areas)) + 1;

		$areas = array_merge(
			array_slice($areas, 0, $counter, true),
			[
				'lp_portal' => [
					'title' => Lang::$txt['lp_portal'],
					'permission' => ['admin_forum', 'light_portal_manage_pages_any', 'light_portal_manage_pages_own'],
					'areas' => [
						'lp_settings' => [
							'label' => Lang::$txt['settings'],
							'function' => [$this, 'settingAreas'],
							'icon' => 'features',
							'permission' => ['admin_forum'],
							'subsections' => [
								'basic'      => [Utils::$context['lp_icon_set']['cog_spin'] . Lang::$txt['mods_cat_features']],
								'extra'      => [Utils::$context['lp_icon_set']['pager'] . Lang::$txt['lp_extra']],
								'categories' => [Utils::$context['lp_icon_set']['sections'] . Lang::$txt['lp_categories']],
								'panels'     => [Utils::$context['lp_icon_set']['panels'] . Lang::$txt['lp_panels']],
								'misc'       => [Utils::$context['lp_icon_set']['tools'] . Lang::$txt['lp_misc']],
								'feedback'   => [Utils::$context['lp_icon_set']['comments'] . Lang::$txt['lp_feedback']]
							]
						],
						'lp_blocks' => [
							'label' => Lang::$txt['lp_blocks'],
							'function' => [$this, 'blockAreas'],
							'icon' => 'packages',
							'amt' => Utils::$context['lp_quantities']['active_blocks'],
							'permission' => ['admin_forum'],
							'subsections' => [
								'main' => [Utils::$context['lp_icon_set']['main'] . Lang::$txt['lp_blocks_manage']],
								'add'  => [Utils::$context['lp_icon_set']['plus'] . Lang::$txt['lp_blocks_add']]
							]
						],
						'lp_pages' => [
							'label' => Lang::$txt['lp_pages'],
							'function' => [$this, 'pageAreas'],
							'icon' => 'reports',
							'amt' => $this->request()->has('u') && ! Utils::$context['allow_light_portal_manage_pages_any'] ? Utils::$context['lp_quantities']['my_pages'] : Utils::$context['lp_quantities']['active_pages'],
							'permission' => ['admin_forum', 'light_portal_manage_pages_any', 'light_portal_manage_pages_own'],
							'subsections' => [
								'main' => [Utils::$context['lp_icon_set']['main'] . Lang::$txt['lp_pages_manage']],
								'add'  => [Utils::$context['lp_icon_set']['plus'] . Lang::$txt['lp_pages_add']]
							]
						],
						'lp_plugins' => [
							'label' => Lang::$txt['lp_plugins'],
							'function' => [$this, 'pluginAreas'],
							'icon' => 'modifications',
							'amt' => Utils::$context['lp_enabled_plugins'] ? count(Utils::$context['lp_enabled_plugins']) : 0,
							'permission' => ['admin_forum'],
							'subsections' => [
								'main' => [Utils::$context['lp_icon_set']['main'] . Lang::$txt['lp_plugins_manage']]
							]
						]
					]
				]
			],
			array_slice($areas, $counter, count($areas), true)
		);

		if (Utils::$context['user']['is_admin']) {
			$areas['lp_portal']['areas']['lp_blocks']['subsections'] += [
				'export' => [Utils::$context['lp_icon_set']['export'] . Lang::$txt['lp_blocks_export']],
				'import' => [Utils::$context['lp_icon_set']['import'] . Lang::$txt['lp_blocks_import']]
			];

			$areas['lp_portal']['areas']['lp_pages']['subsections'] += [
				'export' => [Utils::$context['lp_icon_set']['export'] . Lang::$txt['lp_pages_export']],
				'import' => [Utils::$context['lp_icon_set']['import'] . Lang::$txt['lp_pages_import']]
			];

			if (extension_loaded('zip'))
				$areas['lp_portal']['areas']['lp_plugins']['subsections'] += [
					'export' => [Utils::$context['lp_icon_set']['export'] . Lang::$txt['lp_plugins_export']],
					'import' => [Utils::$context['lp_icon_set']['import'] . Lang::$txt['lp_plugins_import']]
				];
		}

		$this->hook('updateAdminAreas', [&$areas['lp_portal']['areas']]);
	}

	/**
	 * @hook integrate_helpadmin
	 */
	public function helpadmin(): void
	{
		Lang::$txt['lp_standalone_url_help'] = sprintf(Lang::$txt['lp_standalone_url_help'], Config::$boardurl . '/portal.php', Config::$scripturl);
	}

	/**
	 * List of tabs with settings
	 *
	 * Список вкладок с настройками
	 */
	public function settingAreas(): void
	{
		$this->middleware('admin_forum');

		$areas = [
			'basic'      => [new BasicConfig, 'show'],
			'extra'      => [new ExtraConfig, 'show'],
			'categories' => [new CategoryConfig, 'show'],
			'panels'     => [new PanelConfig, 'show'],
			'misc'       => [new MiscConfig, 'show'],
			'feedback'   => [new FeedbackConfig, 'show'],
		];

		$this->dbExtend();

		// Tabs
		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title' => LP_NAME,
			'tabs' => [
				'basic' => [
					'description' => '<img class="floatright" src="https://user-images.githubusercontent.com/229402/143980485-16ba84b8-9d8d-4c06-abeb-af949d594f66.png" alt="Light Portal logo">' . sprintf(Lang::$txt['lp_base_info'], LP_VERSION, phpversion(), Utils::$smcFunc['db_title'], Utils::$smcFunc['db_get_version']())
				],
				'extra' => [
					'description' => Lang::$txt['lp_extra_info']
				],
				'categories' => [
					'description' => Lang::$txt['lp_categories_info']
				],
				'panels' => [
					'description' => sprintf(Lang::$txt['lp_panels_info'], LP_NAME, 'https://evgenyrodionov.github.io/flexboxgrid2/')
				],
				'misc' => [
					'description' => Lang::$txt['lp_misc_info']
				],
				'feedback' => [
					'description' => Lang::$txt['lp_feedback_info']
				]
			]
		];

		$this->callActionFromAreas($areas, 'basic');
	}

	public function blockAreas(): void
	{
		$this->middleware('admin_forum');

		$areas = [
			'main' => [new BlockArea, 'main'],
			'add'  => [new BlockArea, 'add'],
			'edit' => [new BlockArea, 'edit']
		];

		if (User::$info['is_admin']) {
			$areas['export'] = [new BlockExport, 'main'];
			$areas['import'] = [new BlockImport, 'main'];
		}

		$this->hook('updateBlockAreas', [&$areas]);

		$this->callActionFromAreas($areas);
	}

	public function pageAreas(): void
	{
		$this->middleware(['light_portal_manage_pages_own', 'light_portal_manage_pages_any']);

		$areas = [
			'main' => [new PageArea, 'main'],
			'add'  => [new PageArea, 'add'],
			'edit' => [new PageArea, 'edit']
		];

		if (User::$info['is_admin']) {
			$areas['export'] = [new PageExport, 'main'];
			$areas['import'] = [new PageImport, 'main'];
		}

		$this->hook('updatePageAreas', [&$areas]);

		$this->callActionFromAreas($areas);
	}

	public function pluginAreas(): void
	{
		$this->middleware('admin_forum');

		$areas = [
			'main' => [new PluginArea, 'main']
		];

		if (User::$info['is_admin'] && extension_loaded('zip')) {
			$areas['export'] = [new PluginExport, 'main'];
			$areas['import'] = [new PluginImport, 'main'];
		}

		$this->hook('updatePluginAreas', [&$areas]);

		$this->callActionFromAreas($areas);
	}

	/**
	 * Calls the requested subaction if it does exist; otherwise, calls the default action
	 *
	 * Вызывает метод, если он существует; в противном случае вызывается метод по умолчанию
	 */
	private function callActionFromAreas(array $areas = [], string $defaultAction = 'main'): void
	{
		$this->showDocsLink();

		$this->require('ManageServer');

		Utils::$context['sub_template'] = 'show_settings';

		Utils::$context['sub_action'] = $this->request()->has('sa') && isset($areas[$this->request('sa')]) ? $this->request('sa') : $defaultAction;

		$this->callHelper($areas[Utils::$context['sub_action']]);
	}

	private function showDocsLink(): void
	{
		if (empty($this->request('area'))) return;

		if (! empty(Utils::$context['template_layers']) && str_contains($this->request('area'), 'lp_')) {
			$this->loadTemplate('LightPortal/ViewDebug');

			Utils::$context['template_layers'][] = 'docs';
		}
	}
}
