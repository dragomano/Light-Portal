<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas;

use Bugo\Compat\{Config, Db, Lang, Theme, User, Utils};
use Bugo\LightPortal\AddonHandler;
use Bugo\LightPortal\Areas\Configs\{BasicConfig, ExtraConfig, FeedbackConfig, MiscConfig, PanelConfig};
use Bugo\LightPortal\Areas\Exports\{BlockExport, CategoryExport, PageExport, PluginExport, TagExport};
use Bugo\LightPortal\Areas\Imports\{BlockImport, CategoryImport, PageImport, PluginImport, TagImport};
use Bugo\LightPortal\Enums\{Hook, PortalHook};
use Bugo\LightPortal\Utils\{CacheTrait, Icon, RequestTrait, SafeRequireTrait, SMFHookTrait};

use function array_keys;
use function array_merge;
use function array_search;
use function array_slice;
use function call_user_func;
use function count;
use function extension_loaded;
use function phpversion;
use function str_contains;

use const LP_NAME;
use const LP_VERSION;

if (! defined('SMF'))
	die('No direct access...');

final class ConfigArea
{
	use CacheTrait;
	use RequestTrait;
	use SMFHookTrait;
	use SafeRequireTrait;

	public function __invoke(): void
	{
		$this->applyHook(Hook::adminAreas);
		$this->applyHook(Hook::helpadmin);
	}

	public function adminAreas(array &$areas): void
	{
		Theme::loadCSSFile('light_portal/virtual-select.min.css');
		Theme::loadJavaScriptFile('light_portal/virtual-select.min.js');

		Theme::loadJavaScriptFile('light_portal/bundle.min.js', ['defer' => true]);
		Theme::loadJavaScriptFile('light_portal/admin.js', ['minimize' => true]);

		Lang::load('ManageSettings');

		$counter = array_search('layout', array_keys($areas), true) + 1;

		$areas = array_merge(
			array_slice($areas, 0, $counter, true),
			[
				'lp_portal' => [
					'title' => Lang::$txt['lp_portal'],
					'permission' => [
						'admin_forum',
						'light_portal_manage_pages_any',
						'light_portal_manage_pages_own',
					],
					'areas' => [
						'lp_settings' => [
							'label' => Lang::$txt['settings'],
							'function' => $this->settingAreas(...),
							'icon' => 'features',
							'permission' => [
								'admin_forum',
							],
							'subsections' => [
								'basic'    => [Icon::get('cog_spin') . Lang::$txt['mods_cat_features']],
								'extra'    => [Icon::get('pager') . Lang::$txt['lp_extra']],
								'panels'   => [Icon::get('panels') . Lang::$txt['lp_panels']],
								'misc'     => [Icon::get('tools') . Lang::$txt['lp_misc']],
								'feedback' => [Icon::get('comments') . Lang::$txt['lp_feedback']],
							]
						],
						'lp_blocks' => [
							'label' => Lang::$txt['lp_blocks'],
							'function' => $this->blockAreas(...),
							'icon' => 'packages',
							'amt' => Utils::$context['lp_quantities']['active_blocks'],
							'permission' => [
								'admin_forum',
							],
							'subsections' => [
								'main' => [Icon::get('main') . Lang::$txt['lp_blocks_manage']],
								'add'  => [Icon::get('plus') . Lang::$txt['lp_blocks_add']],
							]
						],
						'lp_pages' => [
							'label' => Lang::$txt['lp_pages'],
							'function' => $this->pageAreas(...),
							'icon' => 'reports',
							'amt' => $this->getPagesCount(),
							'permission' => [
								'admin_forum',
								'light_portal_manage_pages_any',
								'light_portal_manage_pages_own',
							],
							'subsections' => [
								'main' => [Icon::get('main') . Lang::$txt['lp_pages_manage']],
								'add'  => [Icon::get('plus') . Lang::$txt['lp_pages_add']],
							]
						],
						'lp_categories' => [
							'label' => Lang::$txt['lp_categories'],
							'function' => $this->categoryAreas(...),
							'icon' => 'boards',
							'amt' => Utils::$context['lp_quantities']['active_categories'],
							'permission' => [
								'admin_forum',
							],
							'subsections' => [
								'main' => [Icon::get('main') . Lang::$txt['lp_categories_manage']],
								'add'  => [Icon::get('plus') . Lang::$txt['lp_categories_add']],
							]
						],
						'lp_tags' => [
							'label' => Lang::$txt['lp_tags'],
							'function' => $this->tagAreas(...),
							'icon' => 'attachment',
							'amt' => Utils::$context['lp_quantities']['active_tags'],
							'permission' => [
								'admin_forum',
							],
							'subsections' => [
								'main' => [Icon::get('main') . Lang::$txt['lp_tags_manage']],
								'add'  => [Icon::get('plus') . Lang::$txt['lp_tags_add']],
							]
						],
						'lp_plugins' => [
							'label' => Lang::$txt['lp_plugins'],
							'function' => $this->pluginAreas(...),
							'icon' => 'modifications',
							'amt' => Utils::$context['lp_enabled_plugins']
								? count(Utils::$context['lp_enabled_plugins']) : 0,
							'permission' => [
								'admin_forum',
							],
							'subsections' => [
								'main' => [Icon::get('main') . Lang::$txt['lp_plugins_manage']]
							]
						]
					]
				]
			],
			array_slice($areas, $counter, count($areas), true)
		);

		if (Utils::$context['user']['is_admin']) {
			$areas['lp_portal']['areas']['lp_blocks']['subsections'] += [
				'export' => [Icon::get('export') . Lang::$txt['lp_blocks_export']],
				'import' => [Icon::get('import') . Lang::$txt['lp_blocks_import']],
			];

			$areas['lp_portal']['areas']['lp_pages']['subsections'] += [
				'export' => [Icon::get('export') . Lang::$txt['lp_pages_export']],
				'import' => [Icon::get('import') . Lang::$txt['lp_pages_import']],
			];

			$areas['lp_portal']['areas']['lp_categories']['subsections'] += [
				'export' => [Icon::get('export') . Lang::$txt['lp_categories_export']],
				'import' => [Icon::get('import') . Lang::$txt['lp_categories_import']],
			];

			$areas['lp_portal']['areas']['lp_tags']['subsections'] += [
				'export' => [Icon::get('export') . Lang::$txt['lp_tags_export']],
				'import' => [Icon::get('import') . Lang::$txt['lp_tags_import']],
			];

			if (extension_loaded('zip')) {
				$areas['lp_portal']['areas']['lp_plugins']['subsections'] += [
					'export' => [Icon::get('export') . Lang::$txt['lp_plugins_export']],
					'import' => [Icon::get('import') . Lang::$txt['lp_plugins_import']],
				];
			}
		}

		AddonHandler::getInstance()->run(PortalHook::updateAdminAreas, [&$areas['lp_portal']['areas']]);
	}

	/**
	 * @hook integrate_helpadmin
	 */
	public function helpadmin(): void
	{
		Lang::$txt['lp_standalone_url_help'] = Lang::getTxt('lp_standalone_url_help', [
			Config::$boardurl . '/portal.php',
			Config::$scripturl
		]);

		Lang::$txt['lp_menu_separate_subsection_title_help'] = Lang::getTxt('lp_menu_separate_subsection_title_help', [
			'<var>{lp_pages}</var>',
			'<var>$txt[`lp_pages`]</var>',
		]);
	}

	/**
	 * List of tabs with settings
	 *
	 * Список вкладок с настройками
	 */
	public function settingAreas(): void
	{
		User::mustHavePermission('admin_forum');

		$areas = [
			'basic'    => [new BasicConfig(), 'show'],
			'extra'    => [new ExtraConfig(), 'show'],
			'panels'   => [new PanelConfig(), 'show'],
			'misc'     => [new MiscConfig(), 'show'],
			'feedback' => [new FeedbackConfig(), 'show'],
		];

		Db::extend();

		// Tabs
		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title' => LP_NAME,
			'tabs' => [
				'basic' => [
					'description' => '<img
						class="floatright"
						src="https://user-images.githubusercontent.com/229402/143980485-16ba84b8-9d8d-4c06-abeb-af949d594f66.png"
						alt="' . LP_NAME . ' logo"
					>' . Lang::getTxt('lp_base_info', [
						LP_VERSION,
						phpversion(),
						Utils::$smcFunc['db_title'],
						Db::$db->get_version(),
					])
				],
				'extra' => [
					'description' => Lang::$txt['lp_extra_info']
				],
				'panels' => [
					'description' => Lang::getTxt('lp_panels_info', [
						LP_NAME,
						'https://evgenyrodionov.github.io/flexboxgrid2/',
					])
				],
				'misc' => [
					'description' => Lang::$txt['lp_misc_info']
				],
				'feedback' => [
					'description' => Lang::$txt['lp_feedback_info']
				],
			]
		];

		$this->callActionFromAreas($areas, 'basic');
	}

	public function blockAreas(): void
	{
		User::mustHavePermission('admin_forum');

		$areas = [
			'main'   => [new BlockArea(), 'main'],
			'add'    => [new BlockArea(), 'add'],
			'edit'   => [new BlockArea(), 'edit'],
			'export' => [new BlockExport(), 'main'],
			'import' => [new BlockImport(), 'main'],
		];

		AddonHandler::getInstance()->run(PortalHook::updateBlockAreas, [&$areas]);

		$this->callActionFromAreas($areas);
	}

	public function pageAreas(): void
	{
		User::mustHavePermission(['light_portal_manage_pages_own', 'light_portal_manage_pages_any']);

		$areas = [
			'main'   => [new PageArea(), 'main'],
			'add'    => [new PageArea(), 'add'],
			'edit'   => [new PageArea(), 'edit'],
			'export' => [new PageExport(), 'main'],
			'import' => [new PageImport(), 'main'],
		];

		AddonHandler::getInstance()->run(PortalHook::updatePageAreas, [&$areas]);

		$this->callActionFromAreas($areas);
	}

	public function categoryAreas(): void
	{
		User::mustHavePermission('admin_forum');

		$areas = [
			'main'   => [new CategoryArea(), 'main'],
			'add'    => [new CategoryArea(), 'add'],
			'edit'   => [new CategoryArea(), 'edit'],
			'export' => [new CategoryExport(), 'main'],
			'import' => [new CategoryImport(), 'main'],
		];

		AddonHandler::getInstance()->run(PortalHook::updateCategoryAreas, [&$areas]);

		$this->callActionFromAreas($areas);
	}

	public function tagAreas(): void
	{
		User::mustHavePermission('admin_forum');

		$areas = [
			'main'   => [new TagArea(), 'main'],
			'add'    => [new TagArea(), 'add'],
			'edit'   => [new TagArea(), 'edit'],
			'export' => [new TagExport(), 'main'],
			'import' => [new TagImport(), 'main'],
		];

		AddonHandler::getInstance()->run(PortalHook::updateTagAreas, [&$areas]);

		$this->callActionFromAreas($areas);
	}

	public function pluginAreas(): void
	{
		User::mustHavePermission('admin_forum');

		$areas = [
			'main' => [new PluginArea(), 'main'],
		];

		if (extension_loaded('zip')) {
			$areas['export'] = [new PluginExport(), 'main'];
			$areas['import'] = [new PluginImport(), 'main'];
		}

		AddonHandler::getInstance()->run(PortalHook::updatePluginAreas, [&$areas]);

		$this->callActionFromAreas($areas);
	}

	/**
	 * Calls the requested sub_action if it does exist; otherwise, calls the default action
	 *
	 * Вызывает метод, если он существует; в противном случае вызывается метод по умолчанию
	 */
	private function callActionFromAreas(array $areas = [], string $defaultAction = 'main'): void
	{
		$this->showDocsLink();

		$this->cache()->flush();

		$this->require('ManageServer');

		Utils::$context['sub_template'] = 'show_settings';

		Utils::$context['sub_action'] = $this->request()->has('sa') && isset($areas[$this->request('sa')])
			? $this->request('sa') : $defaultAction;

		call_user_func($areas[Utils::$context['sub_action']]);
	}

	private function showDocsLink(): void
	{
		if (empty($this->request('area')) || empty(Utils::$context['template_layers']))
			return;

		if (str_contains((string) $this->request('area'), 'lp_')) {
			Theme::loadTemplate('LightPortal/ViewDebug');

			Utils::$context['template_layers'][] = 'docs';
		}
	}

	private function getPagesCount(): int
	{
		return $this->request()->has('u') && ! Utils::$context['allow_light_portal_manage_pages_any']
			? Utils::$context['lp_quantities']['my_pages']
			: Utils::$context['lp_quantities']['active_pages'];
	}
}
