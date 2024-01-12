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

use Bugo\LightPortal\{
	Areas\Config\BasicConfig,
	Areas\Config\CategoryConfig,
	Areas\Config\ExtraConfig,
	Areas\Config\FeedbackConfig,
	Areas\Config\MiscConfig,
	Areas\Config\PanelConfig,
	Areas\Export\BlockExport,
	Areas\Export\PageExport,
	Areas\Export\PluginExport,
	Areas\Import\BlockImport,
	Areas\Import\PageImport,
	Areas\Import\PluginImport,
	Helper,
};

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
					'title' => $this->txt['lp_portal'],
					'permission' => ['admin_forum', 'light_portal_manage_pages_any', 'light_portal_manage_pages_own'],
					'areas' => [
						'lp_settings' => [
							'label' => $this->txt['settings'],
							'function' => [$this, 'settingAreas'],
							'icon' => 'features',
							'permission' => ['admin_forum'],
							'subsections' => [
								'basic'      => [$this->context['lp_icon_set']['cog_spin'] . $this->txt['mods_cat_features']],
								'extra'      => [$this->context['lp_icon_set']['pager'] . $this->txt['lp_extra']],
								'categories' => [$this->context['lp_icon_set']['sections'] . $this->txt['lp_categories']],
								'panels'     => [$this->context['lp_icon_set']['panels'] . $this->txt['lp_panels']],
								'misc'       => [$this->context['lp_icon_set']['tools'] . $this->txt['lp_misc']],
								'feedback'   => [$this->context['lp_icon_set']['comments'] . $this->txt['lp_feedback']]
							]
						],
						'lp_blocks' => [
							'label' => $this->txt['lp_blocks'],
							'function' => [$this, 'blockAreas'],
							'icon' => 'packages',
							'amt' => $this->context['lp_quantities']['active_blocks'],
							'permission' => ['admin_forum'],
							'subsections' => [
								'main' => [$this->context['lp_icon_set']['main'] . $this->txt['lp_blocks_manage']],
								'add'  => [$this->context['lp_icon_set']['plus'] . $this->txt['lp_blocks_add']]
							]
						],
						'lp_pages' => [
							'label' => $this->txt['lp_pages'],
							'function' => [$this, 'pageAreas'],
							'icon' => 'reports',
							'amt' => $this->request()->has('u') && ! $this->context['allow_light_portal_manage_pages_any'] ? $this->context['lp_quantities']['my_pages'] : $this->context['lp_quantities']['active_pages'],
							'permission' => ['admin_forum', 'light_portal_manage_pages_any', 'light_portal_manage_pages_own'],
							'subsections' => [
								'main' => [$this->context['lp_icon_set']['main'] . $this->txt['lp_pages_manage']],
								'add'  => [$this->context['lp_icon_set']['plus'] . $this->txt['lp_pages_add']]
							]
						],
						'lp_plugins' => [
							'label' => $this->txt['lp_plugins'],
							'function' => [$this, 'pluginAreas'],
							'icon' => 'modifications',
							'amt' => $this->context['lp_enabled_plugins'] ? count($this->context['lp_enabled_plugins']) : 0,
							'permission' => ['admin_forum'],
							'subsections' => [
								'main' => [$this->context['lp_icon_set']['main'] . $this->txt['lp_plugins_manage']]
							]
						]
					]
				]
			],
			array_slice($areas, $counter, count($areas), true)
		);

		if ($this->context['user']['is_admin']) {
			$areas['lp_portal']['areas']['lp_blocks']['subsections'] += [
				'export' => [$this->context['lp_icon_set']['export'] . $this->txt['lp_blocks_export']],
				'import' => [$this->context['lp_icon_set']['import'] . $this->txt['lp_blocks_import']]
			];

			$areas['lp_portal']['areas']['lp_pages']['subsections'] += [
				'export' => [$this->context['lp_icon_set']['export'] . $this->txt['lp_pages_export']],
				'import' => [$this->context['lp_icon_set']['import'] . $this->txt['lp_pages_import']]
			];

			if (extension_loaded('zip'))
				$areas['lp_portal']['areas']['lp_plugins']['subsections'] += [
					'export' => [$this->context['lp_icon_set']['export'] . $this->txt['lp_plugins_export']],
					'import' => [$this->context['lp_icon_set']['import'] . $this->txt['lp_plugins_import']]
				];
		}

		$this->hook('updateAdminAreas', [&$areas['lp_portal']['areas']]);
	}

	/**
	 * @hook integrate_helpadmin
	 */
	public function helpadmin(): void
	{
		$this->txt['lp_standalone_url_help'] = sprintf($this->txt['lp_standalone_url_help'], $this->boardurl . '/portal.php', $this->scripturl);
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
		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title' => LP_NAME,
			'tabs' => [
				'basic' => [
					'description' => '<img class="floatright" src="https://user-images.githubusercontent.com/229402/143980485-16ba84b8-9d8d-4c06-abeb-af949d594f66.png" alt="Light Portal logo">' . sprintf($this->txt['lp_base_info'], LP_VERSION, phpversion(), $this->smcFunc['db_title'], $this->smcFunc['db_get_version']())
				],
				'extra' => [
					'description' => $this->txt['lp_extra_info']
				],
				'categories' => [
					'description' => $this->txt['lp_categories_info']
				],
				'panels' => [
					'description' => sprintf($this->txt['lp_panels_info'], LP_NAME, 'https://evgenyrodionov.github.io/flexboxgrid2/')
				],
				'misc' => [
					'description' => $this->txt['lp_misc_info']
				],
				'feedback' => [
					'description' => $this->txt['lp_feedback_info']
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

		if ($this->user_info['is_admin']) {
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

		if ($this->user_info['is_admin']) {
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

		if ($this->user_info['is_admin'] && extension_loaded('zip')) {
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

		$this->context['sub_template'] = 'show_settings';

		$this->context['sub_action'] = $this->request()->has('sa') && isset($areas[$this->request('sa')]) ? $this->request('sa') : $defaultAction;

		$this->callHelper($areas[$this->context['sub_action']]);
	}

	private function showDocsLink(): void
	{
		if (empty($this->request('area'))) return;

		if (! empty($this->context['template_layers']) && str_contains($this->request('area'), 'lp_')) {
			$this->loadTemplate('LightPortal/ViewDebug');

			$this->context['template_layers'][] = 'docs';
		}
	}
}
