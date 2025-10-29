<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Hooks;

use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Areas\BlockArea;
use LightPortal\Areas\CategoryArea;
use LightPortal\Areas\Configs\BasicConfig;
use LightPortal\Areas\Configs\ExtraConfig;
use LightPortal\Areas\Configs\FeedbackConfig;
use LightPortal\Areas\Configs\MiscConfig;
use LightPortal\Areas\Configs\PanelConfig;
use LightPortal\Areas\PageArea;
use LightPortal\Areas\PluginArea;
use LightPortal\Areas\TagArea;
use LightPortal\DataHandlers\Exports\BlockExport;
use LightPortal\DataHandlers\Exports\CategoryExport;
use LightPortal\DataHandlers\Exports\PageExport;
use LightPortal\DataHandlers\Exports\PluginExport;
use LightPortal\DataHandlers\Exports\TagExport;
use LightPortal\DataHandlers\Imports\BlockImport;
use LightPortal\DataHandlers\Imports\CategoryImport;
use LightPortal\DataHandlers\Imports\PageImport;
use LightPortal\DataHandlers\Imports\PluginImport;
use LightPortal\DataHandlers\Imports\TagImport;
use LightPortal\Enums\PortalHook;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasPortalSql;
use LightPortal\Utils\Traits\HasRequest;

use function LightPortal\app;

use const LP_NAME;
use const LP_VERSION;

class AdminAreas extends AbstractHook
{
	use HasCache;
	use HasPortalSql;
	use HasRequest;

	public function __invoke(array &$areas): void
	{
		Theme::loadCSSFile('light_portal/virtual-select.min.css');
		Theme::loadJavaScriptFile('light_portal/virtual-select.min.js');
		Theme::loadJavaScriptFile('light_portal/portal.js', ['minimize' => true]);

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
							'amt' => count(Setting::getEnabledPlugins()),
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

		$this->dispatcher->dispatch(PortalHook::extendAdminAreas, ['areas' => &$areas['lp_portal']['areas']]);
	}

	public function settingAreas(): void
	{
		User::$me->isAllowedTo('admin_forum');

		$areas = [
			'basic'    => [app(BasicConfig::class), 'show'],
			'extra'    => [app(ExtraConfig::class), 'show'],
			'panels'   => [app(PanelConfig::class), 'show'],
			'misc'     => [app(MiscConfig::class), 'show'],
			'feedback' => [app(FeedbackConfig::class), 'show'],
		];

		// Tabs
		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title' => LP_NAME,
			'description' => Str::html('img')
					->class('floatright')
					->setAttribute('src', 'https://user-images.githubusercontent.com/229402/143980485-16ba84b8-9d8d-4c06-abeb-af949d594f66.png')
					->setAttribute('alt', LP_NAME . ' logo') .
				Lang::getTxt('lp_base_info', [
					LP_VERSION,
					PHP_VERSION,
					$this->getPortalSql()->getAdapter()->getTitle(),
					$this->getPortalSql()->getAdapter()->getVersion(),
				]),
			'tabs' => [
				'basic' => [],
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
		User::$me->isAllowedTo('admin_forum');

		$areas = [
			'main'   => [app(BlockArea::class), 'main'],
			'add'    => [app(BlockArea::class), 'add'],
			'edit'   => [app(BlockArea::class), 'edit'],
			'export' => [app(BlockExport::class), 'main'],
			'import' => [app(BlockImport::class), 'main'],
		];

		$this->dispatcher->dispatch(PortalHook::extendBlockAreas, ['areas' => &$areas]);

		$this->callActionFromAreas($areas);
	}

	public function pageAreas(): void
	{
		User::$me->isAllowedTo(['light_portal_manage_pages_own', 'light_portal_manage_pages_any']);

		$areas = [
			'main'   => [app(PageArea::class), 'main'],
			'add'    => [app(PageArea::class), 'add'],
			'edit'   => [app(PageArea::class), 'edit'],
			'export' => [app(PageExport::class), 'main'],
			'import' => [app(PageImport::class), 'main'],
		];

		$this->dispatcher->dispatch(PortalHook::extendPageAreas, ['areas' => &$areas]);

		$this->callActionFromAreas($areas);
	}

	public function categoryAreas(): void
	{
		User::$me->isAllowedTo('admin_forum');

		$areas = [
			'main'   => [app(CategoryArea::class), 'main'],
			'add'    => [app(CategoryArea::class), 'add'],
			'edit'   => [app(CategoryArea::class), 'edit'],
			'export' => [app(CategoryExport::class), 'main'],
			'import' => [app(CategoryImport::class), 'main'],
		];

		$this->dispatcher->dispatch(PortalHook::extendCategoryAreas, ['areas' => &$areas]);

		$this->callActionFromAreas($areas);
	}

	public function tagAreas(): void
	{
		User::$me->isAllowedTo('admin_forum');

		$areas = [
			'main'   => [app(TagArea::class), 'main'],
			'add'    => [app(TagArea::class), 'add'],
			'edit'   => [app(TagArea::class), 'edit'],
			'export' => [app(TagExport::class), 'main'],
			'import' => [app(TagImport::class), 'main'],
		];

		$this->dispatcher->dispatch(PortalHook::extendTagAreas, ['areas' => &$areas]);

		$this->callActionFromAreas($areas);
	}

	public function pluginAreas(): void
	{
		User::$me->isAllowedTo('admin_forum');

		$areas = [
			'main' => [app(PluginArea::class), 'main'],
		];

		if (extension_loaded('zip')) {
			$areas['export'] = [app(PluginExport::class), 'main'];
			$areas['import'] = [app(PluginImport::class), 'main'];
		}

		$this->dispatcher->dispatch(PortalHook::extendPluginAreas, ['areas' => &$areas]);

		$this->callActionFromAreas($areas);
	}

	private function callActionFromAreas(array $areas = [], string $defaultAction = 'main'): void
	{
		$this->showDocsLink();

		Utils::$context['sub_template'] = 'show_settings';

		Utils::$context['sub_action'] = $this->request()->has('sa') && isset($areas[$this->request()->get('sa')])
			? $this->request()->get('sa')
			: $defaultAction;

		call_user_func($areas[Utils::$context['sub_action']]);
	}

	private function showDocsLink(): void
	{
		if (empty($this->request()->get('area')) || empty(Utils::$context['template_layers']))
			return;

		if (str_contains((string) $this->request()->get('area'), 'lp_')) {
			Theme::loadTemplate('LightPortal/ViewDocs');

			Utils::$context['template_layers'][] = 'lp_docs';
		}
	}

	private function getPagesCount(): int
	{
		return $this->request()->has('u') && ! User::$me->allowedTo('light_portal_manage_pages_any')
			? Utils::$context['lp_quantities']['my_pages']
			: Utils::$context['lp_quantities']['active_pages'];
	}
}
