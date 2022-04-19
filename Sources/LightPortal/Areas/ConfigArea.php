<?php declare(strict_types=1);

/**
 * ConfigArea.php
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

use Bugo\LightPortal\{
	Helper,
	Impex\BlockExport,
	Impex\BlockImport,
	Impex\PageExport,
	Impex\PageImport,
	Lists\Category,
	Repositories\PageRepository
};

use function addInlineCss;
use function call_helper;
use function checkSession;
use function db_extend;
use function fetch_web_data;
use function isAllowedTo;
use function loadCSSFile;
use function loadJavaScriptFile;
use function loadLanguage;
use function loadTemplate;
use function parse_bbc;
use function smf_json_decode;
use function prepareDBSettingContext;
use function redirectexit;
use function saveDBSettings;
use function updateSettings;

if (! defined('SMF'))
	die('No direct access...');

final class ConfigArea
{
	use Helper;

	public function adminAreas(array &$admin_areas)
	{
		loadCSSFile('https://cdn.jsdelivr.net/npm/virtual-select-plugin/dist/virtual-select.min.css', ['external' => true]);
		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/virtual-select-plugin/dist/virtual-select.min.js', ['external' => true]);

		loadCSSFile('https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.min.css', ['external' => true]);
		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js', ['external' => true]);

		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js', ['external' => true, 'defer' => true]);
		loadJavaScriptFile('light_portal/admin.js', ['minimize' => true]);

		loadLanguage('ManageSettings');

		$counter = array_search('layout', array_keys($admin_areas)) + 1;

		$admin_areas = array_merge(
			array_slice($admin_areas, 0, (int) $counter, true),
			[
				'lp_portal' => [
					'title' => $this->txt['lp_portal'],
					'permission' => ['admin_forum', 'light_portal_manage_own_blocks', 'light_portal_manage_own_pages'],
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
								'misc'       => [$this->context['lp_icon_set']['tools'] . $this->txt['lp_misc']]
							]
						],
						'lp_blocks' => [
							'label' => $this->txt['lp_blocks'],
							'function' => [$this, 'blockAreas'],
							'icon' => 'modifications',
							'amt' => $this->context['lp_num_active_blocks'],
							'permission' => ['admin_forum', 'light_portal_manage_own_blocks'],
							'subsections' => [
								'main' => [$this->context['lp_icon_set']['main'] . $this->txt['lp_blocks_manage']],
								'add'  => [$this->context['lp_icon_set']['plus'] . $this->txt['lp_blocks_add']]
							]
						],
						'lp_pages' => [
							'label' => $this->txt['lp_pages'],
							'function' => [$this, 'pageAreas'],
							'icon' => 'reports',
							'amt' => $this->context['lp_num_active_pages'],
							'permission' => ['admin_forum', 'light_portal_manage_own_pages'],
							'subsections' => [
								'main' => [$this->context['lp_icon_set']['main'] . $this->txt['lp_pages_manage']],
								'add'  => [$this->context['lp_icon_set']['plus'] . $this->txt['lp_pages_add']]
							]
						],
						'lp_plugins' => [
							'label' => $this->txt['lp_plugins'],
							'function' => [$this, 'pluginAreas'],
							'icon' => 'maintain',
							'amt' => $this->context['lp_enabled_plugins'] ? count($this->context['lp_enabled_plugins']) : 0,
							'permission' => ['admin_forum'],
							'subsections' => [
								'main' => [$this->context['lp_icon_set']['main'] . $this->txt['lp_plugins_manage']]
							]
						]
					]
				]
			],
			array_slice($admin_areas, $counter, count($admin_areas), true)
		);

		if ($this->context['user']['is_admin']) {
			$admin_areas['lp_portal']['areas']['lp_blocks']['subsections'] += [
				'export' => [$this->context['lp_icon_set']['export'] . $this->txt['lp_blocks_export']],
				'import' => [$this->context['lp_icon_set']['import'] . $this->txt['lp_blocks_import']]
			];

			$admin_areas['lp_portal']['areas']['lp_pages']['subsections'] += [
				'export' => [$this->context['lp_icon_set']['export'] . $this->txt['lp_pages_export']],
				'import' => [$this->context['lp_icon_set']['import'] . $this->txt['lp_pages_import']]
			];
		}

		$this->hook('addAdminAreas', [&$admin_areas]);
	}

	/**
	 * @hook integrate_admin_search
	 */
	public function adminSearch(array &$language_files, array &$include_files, array &$settings_search)
	{
		$settings_search[] = [[$this, 'panels'], 'area=lp_settings;sa=panels'];
		$settings_search[] = [[$this, 'misc'], 'area=lp_settings;sa=misc'];
	}

	public function helpadmin()
	{
		$this->txt['lp_standalone_url_help'] = sprintf($this->txt['lp_standalone_url_help'], $this->boardurl . '/portal.php', $this->scripturl);
	}

	/**
	 * List of tabs with settings
	 *
	 * Список вкладок с настройками
	 */
	public function settingAreas()
	{
		isAllowedTo('admin_forum');

		$subActions = [
			'basic'      => [$this, 'basic'],
			'extra'      => [$this, 'extra'],
			'categories' => [$this, 'categories'],
			'panels'     => [$this, 'panels'],
			'misc'       => [$this, 'misc']
		];

		db_extend();

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
				]
			]
		];

		$this->loadGeneralSettingParameters($subActions, 'basic');

		if ($this->request()->has('getDebugInfo'))
			$this->generateDumpFile();

		if (! isset($this->context['settings_title']))
			return;

		$this->context['settings_title'] .= '<span class="floatright" x-data>
			<a @mouseover="$event.target.style.color = \'yellow\'" @mouseout="$event.target.style.color = \'white\'" @click="location.href = location.href + \';getDebugInfo\'" title="' . $this->txt['lp_debug_info'] . '">' . $this->context['lp_icon_set']['info'] . '</a>
		</span>';
	}

	/**
	 * Output general settings
	 *
	 * Выводим общие настройки
	 */
	public function basic()
	{
		$this->prepareAliasList();

		$this->context['page_title'] = $this->context['settings_title'] = $this->txt['lp_base'];
		$this->context['post_url']   = $this->scripturl . '?action=admin;area=lp_settings;sa=basic;save';

		$this->context['permissions_excluded']['light_portal_manage_own_blocks'] = [-1, 0];
		$this->context['permissions_excluded']['light_portal_manage_own_pages']  = [-1, 0];
		$this->context['permissions_excluded']['light_portal_approve_pages']     = [-1, 0];

		$this->context['lp_all_categories']    = $this->getAllCategories();
		$this->context['lp_frontpage_layouts'] = $this->getFrontPageLayouts();

		// Initial settings
		$addSettings = [];
		if (! isset($this->modSettings['lp_frontpage_title']))
			$addSettings['lp_frontpage_title'] = $this->context['forum_name'];
		if (! isset($this->modSettings['lp_frontpage_alias']))
			$addSettings['lp_frontpage_alias'] = 'home';
		if (! isset($this->modSettings['lp_show_views_and_comments']))
			$addSettings['lp_show_views_and_comments'] = 1;
		if (! isset($this->modSettings['lp_frontpage_article_sorting']))
			$addSettings['lp_frontpage_article_sorting'] = 1;
		if (! isset($this->modSettings['lp_num_items_per_page']))
			$addSettings['lp_num_items_per_page'] = 10;
		if (! isset($this->modSettings['lp_standalone_url']))
			$addSettings['lp_standalone_url'] = $this->boardurl . '/portal.php';
		if (! isset($this->modSettings['lp_prohibit_php']))
			$addSettings['lp_prohibit_php'] = 1;
		if ($addSettings)
			updateSettings($addSettings);

		$this->context['lp_frontpage_modes'] = array_combine(
			[0, 'chosen_page', 'all_pages', 'chosen_pages', 'all_topics', 'chosen_topics', 'chosen_boards'],
			array_pad($this->txt['lp_frontpage_mode_set'], 7, LP_NEED_TRANSLATION)
		);

		$this->require('Subs-MessageIndex');
		$this->context['board_list'] = getBoardList();

		$config_vars = [
			['callback', 'frontpage_mode_settings'],
			['title', 'lp_standalone_mode_title'],
			['callback', 'standalone_mode_settings'],
			['title', 'edit_permissions'],
			['check', 'lp_prohibit_php', 'invalid' => true],
			['permissions', 'light_portal_view', 'help' => 'permissionhelp_light_portal_view'],
			['permissions', 'light_portal_manage_own_blocks', 'help' => 'permissionhelp_light_portal_manage_own_blocks'],
			['permissions', 'light_portal_manage_own_pages', 'help' => 'permissionhelp_light_portal_manage_own_pages'],
			['permissions', 'light_portal_approve_pages', 'help' => 'permissionhelp_light_portal_approve_pages']
		];

		$this->checkNewVersion();

		loadTemplate('LightPortal/ManageSettings');

		// Save
		if ($this->request()->has('save')) {
			checkSession();

			if ($this->post()->isNotEmpty('lp_image_placeholder'))
				$this->post()->put('lp_image_placeholder', $this->validate($this->post('lp_image_placeholder'), 'url'));

			if ($this->post()->isNotEmpty('lp_standalone_url'))
				$this->post()->put('lp_standalone_url', $this->validate($this->post('lp_standalone_url'), 'url'));

			$save_vars = $config_vars;
			$save_vars[] = ['text', 'lp_frontpage_mode'];
			$save_vars[] = ['text', 'lp_frontpage_title'];
			$save_vars[] = ['text', 'lp_frontpage_alias'];
			$save_vars[] = ['text', 'lp_frontpage_categories'];
			$save_vars[] = ['text', 'lp_frontpage_boards'];
			$save_vars[] = ['text', 'lp_frontpage_pages'];
			$save_vars[] = ['text', 'lp_frontpage_topics'];
			$save_vars[] = ['check', 'lp_show_images_in_articles'];
			$save_vars[] = ['text', 'lp_image_placeholder'];
			$save_vars[] = ['check', 'lp_show_teaser'];
			$save_vars[] = ['check', 'lp_show_author'];
			$save_vars[] = ['check', 'lp_show_views_and_comments'];
			$save_vars[] = ['check', 'lp_frontpage_order_by_replies'];
			$save_vars[] = ['int', 'lp_frontpage_article_sorting'];
			$save_vars[] = ['text', 'lp_frontpage_layout'];
			$save_vars[] = ['int', 'lp_frontpage_num_columns'];
			$save_vars[] = ['int', 'lp_show_pagination'];
			$save_vars[] = ['check', 'lp_use_simple_pagination'];
			$save_vars[] = ['int', 'lp_num_items_per_page'];
			$save_vars[] = ['check', 'lp_standalone_mode'];
			$save_vars[] = ['text', 'lp_standalone_url'];
			$save_vars[] = ['text', 'lp_disabled_actions'];

			saveDBSettings($save_vars);

			$this->session()->put('adm-save', true);
			$this->cache()->flush();

			redirectexit('action=admin;area=lp_settings;sa=basic');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Output page and block settings
	 *
	 * Выводим настройки страниц и блоков
	 */
	public function extra()
	{
		$this->context['page_title'] = $this->context['settings_title'] = $this->txt['lp_extra'];
		$this->context['post_url']   = $this->scripturl . '?action=admin;area=lp_settings;sa=extra;save';

		$this->txt['lp_show_comment_block_set']['none']    = $this->txt['lp_show_comment_block_set'][0];
		$this->txt['lp_show_comment_block_set']['default'] = $this->txt['lp_show_comment_block_set'][1];

		unset($this->txt['lp_show_comment_block_set'][0], $this->txt['lp_show_comment_block_set'][1]);
		asort($this->txt['lp_show_comment_block_set']);

		$this->txt['lp_fa_source_title'] .= ' <img class="floatright" src="https://data.jsdelivr.com/v1/package/npm/@fortawesome/fontawesome-free/badge?style=rounded" alt="">';

		// Initial settings
		$addSettings = [];
		if (! isset($this->modSettings['lp_num_comments_per_page']))
			$addSettings['lp_num_comments_per_page'] = 10;
		if (! isset($this->modSettings['lp_page_maximum_keywords']))
			$addSettings['lp_page_maximum_keywords'] = 10;
		if (! empty($addSettings))
			updateSettings($addSettings);

		$config_vars = [
			['check', 'lp_show_tags_on_page'],
			['select', 'lp_page_og_image', $this->txt['lp_page_og_image_set']],
			['check', 'lp_show_prev_next_links'],
			['check', 'lp_show_related_pages'],
			'',
			['callback', 'comment_settings'],
			'',
			['check', 'lp_show_items_as_articles'],
			['select', 'lp_page_editor_type_default', $this->context['lp_content_types']],
			['int', 'lp_page_maximum_keywords', 'min' => 1],
			['select', 'lp_permissions_default', $this->txt['lp_permissions']],
			['check', 'lp_hide_blocks_in_acp'],
			['title', 'lp_fa_source_title'],
			[
				'select',
				'lp_fa_source',
				[
					'none'      => $this->txt['no'],
					'css_cdn'   => $this->txt['lp_fa_source_css_cdn'],
					'css_local' => $this->txt['lp_fa_source_css_local'],
					'custom'    => $this->txt['lp_fa_custom']
				],
				'onchange' => 'document.getElementById(\'lp_fa_custom\').disabled = this.value !== \'custom\';'
			],
			[
				'text',
				'lp_fa_custom',
				'disabled' => isset($this->modSettings['lp_fa_source']) && $this->modSettings['lp_fa_source'] !== 'custom',
				'size' => 75
			],
		];

		loadTemplate('LightPortal/ManageSettings');

		$this->prepareTagsInComments();

		// Save
		if ($this->request()->has('save')) {
			checkSession();

			// Clean up the tags
			$parse_tags = (array) parse_bbc(false);
			$bbcTags = array_map(fn($tag): string => $tag['tag'], $parse_tags);

			if ($this->post()->has('lp_disabled_bbc_in_comments_enabledTags') === false) {
				$this->post()->put('lp_disabled_bbc_in_comments_enabledTags', '');
			} elseif (! is_array($this->post('lp_disabled_bbc_in_comments_enabledTags'))) {
				$this->post()->put('lp_disabled_bbc_in_comments_enabledTags', $this->post('lp_disabled_bbc_in_comments_enabledTags'));
			}

			$this->post()->put('lp_enabled_bbc_in_comments', $this->post('lp_disabled_bbc_in_comments_enabledTags'));
			$this->post()->put('lp_disabled_bbc_in_comments', implode(',', array_diff($bbcTags, explode(',', $this->post('lp_disabled_bbc_in_comments_enabledTags')))));

			if ($this->post()->isNotEmpty('lp_fa_custom'))
				$this->post()->put('lp_fa_custom', $this->validate($this->post('lp_fa_custom'), 'url'));

			$save_vars = $config_vars;
			$save_vars[] = ['text', 'lp_show_comment_block'];
			$save_vars[] = ['text', 'lp_enabled_bbc_in_comments'];
			$save_vars[] = ['text', 'lp_disabled_bbc_in_comments'];
			$save_vars[] = ['int', 'lp_time_to_change_comments'];
			$save_vars[] = ['int', 'lp_num_comments_per_page'];

			saveDBSettings($save_vars);

			$this->session()->put('adm-save', true);
			$this->cache()->flush();

			redirectexit('action=admin;area=lp_settings;sa=extra');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Output category settings
	 *
	 * Выводим настройки рубрик
	 */
	public function categories()
	{
		loadTemplate('LightPortal/ManageSettings');

		$this->context['page_title'] = $this->txt['lp_categories'];

		$this->context['lp_categories'] = (new Category)->getList();

		unset($this->context['lp_categories'][0]);

		if ($this->request()->has('actions')) {
			$data = $this->request()->json();

			if (isset($data['update_priority']))
				$this->updatePriority($data['update_priority']);

			if (isset($data['new_name']))
				$this->add($data['new_name'], $data['new_desc'] ?? '');

			if (isset($data['name']))
				$this->updateName((int) $data['item'], $data['name']);

			if (isset($data['desc']))
				$this->updateDescription((int) $data['item'], $data['desc']);

			if (isset($data['del_item']))
				$this->remove([(int) $data['del_item']]);

			exit;
		}

		$this->context['sub_template'] = 'lp_category_settings';
	}

	/**
	 * Output panel settings
	 *
	 * Выводим настройки панелей
	 *
	 * @return array|void
	 */
	public function panels(bool $return_config = false)
	{
		loadTemplate('LightPortal/ManageSettings');

		addInlineCss('
		dl.settings {
			overflow: hidden;
		}');

		$this->context['page_title'] = $this->context['settings_title'] = $this->txt['lp_panels'];
		$this->context['post_url']   = $this->scripturl . '?action=admin;area=lp_settings;sa=panels;save';

		// Initial settings | Первоначальные настройки
		$addSettings = [];
		if (! isset($this->modSettings['lp_swap_left_right']))
			$addSettings['lp_swap_left_right'] = (bool) $this->txt['lang_rtl'];
		if (! isset($this->modSettings['lp_header_panel_width']))
			$addSettings['lp_header_panel_width'] = 12;
		if (! isset($this->modSettings['lp_left_panel_width']))
			$addSettings['lp_left_panel_width'] = json_encode($this->context['lp_left_panel_width']);
		if (! isset($this->modSettings['lp_right_panel_width']))
			$addSettings['lp_right_panel_width'] = json_encode($this->context['lp_right_panel_width']);
		if (! isset($this->modSettings['lp_footer_panel_width']))
			$addSettings['lp_footer_panel_width'] = 12;
		if (! isset($this->modSettings['lp_left_panel_sticky']))
			$addSettings['lp_left_panel_sticky'] = 1;
		if (! isset($this->modSettings['lp_right_panel_sticky']))
			$addSettings['lp_right_panel_sticky'] = 1;
		if (! empty($addSettings))
			updateSettings($addSettings);

		$this->context['lp_left_right_width_values']    = [2, 3, 4];
		$this->context['lp_header_footer_width_values'] = [6, 8, 10, 12];

		$config_vars = [
			['check', 'lp_swap_header_footer'],
			['check', 'lp_swap_left_right'],
			['check', 'lp_swap_top_bottom'],
			['callback', 'panel_layout'],
			['callback', 'panel_direction']
		];

		if ($return_config)
			return $config_vars;

		$this->context['sub_template'] = 'show_settings';

		if ($this->request()->has('save')) {
			checkSession();

			$this->post()->put('lp_left_panel_width', json_encode($this->post('lp_left_panel_width')));
			$this->post()->put('lp_right_panel_width', json_encode($this->post('lp_right_panel_width')));
			$this->post()->put('lp_panel_direction', json_encode($this->post('lp_panel_direction')));

			$save_vars = $config_vars;

			$save_vars[] = ['int', 'lp_header_panel_width'];
			$save_vars[] = ['text', 'lp_left_panel_width'];
			$save_vars[] = ['text', 'lp_right_panel_width'];
			$save_vars[] = ['int', 'lp_footer_panel_width'];
			$save_vars[] = ['check', 'lp_left_panel_sticky'];
			$save_vars[] = ['check', 'lp_right_panel_sticky'];
			$save_vars[] = ['text', 'lp_panel_direction'];

			saveDBSettings($save_vars);

			$this->session()->put('adm-save', true);

			redirectexit('action=admin;area=lp_settings;sa=panels');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Output additional settings
	 *
	 * Выводим дополнительные настройки
	 *
	 * @return array|void
	 */
	public function misc(bool $return_config = false)
	{
		$this->context['page_title'] = $this->txt['lp_misc'];
		$this->context['post_url']   = $this->scripturl . '?action=admin;area=lp_settings;sa=misc;save';

		// Initial settings
		$addSettings = [];
		if (! isset($this->modSettings['lp_cache_update_interval']))
			$addSettings['lp_cache_update_interval'] = LP_CACHE_TIME;
		if (! isset($this->modSettings['lp_portal_action']))
			$addSettings['lp_portal_action'] = LP_ACTION;
		if (! isset($this->modSettings['lp_page_param']))
			$addSettings['lp_page_param'] = LP_PAGE_PARAM;
		if (! empty($addSettings))
			updateSettings($addSettings);

		$config_vars = [
			['title', 'lp_debug_and_caching'],
			['check', 'lp_show_debug_info', 'help' => 'lp_show_debug_info_help'],
			['int', 'lp_cache_update_interval', 'postinput' => $this->txt['seconds']],
			['title', 'lp_compatibility_mode'],
			['text', 'lp_portal_action', 'subtext' => $this->scripturl . '?action=<strong>' . LP_ACTION . '</strong>'],
			['text', 'lp_page_param', 'subtext' => $this->scripturl . '?<strong>' . LP_PAGE_PARAM . '</strong>=somealias'],
			['title', 'admin_maintenance'],
			['check', 'lp_weekly_cleaning']
		];

		if ($return_config)
			return $config_vars;

		$this->context['sub_template'] = 'show_settings';

		if ($this->request()->has('save')) {
			checkSession();

			$this->smcFunc['db_query']('', '
				DELETE FROM {db_prefix}background_tasks
				WHERE task_file LIKE {string:task_file}',
				[
					'task_file' => '%$sourcedir/LightPortal%'
				]
			);

			if ($this->post()->has('lp_weekly_cleaning')) {
				$this->smcFunc['db_insert']('insert',
					'{db_prefix}background_tasks',
					['task_file' => 'string-255', 'task_class' => 'string-255', 'task_data' => 'string'],
					['$sourcedir/LightPortal/Tasks/Maintainer.php', '\Bugo\LightPortal\Tasks\Maintainer', ''],
					['id_task']
				);
			}

			$save_vars = $config_vars;

			saveDBSettings($save_vars);

			$this->session()->put('adm-save', true);

			redirectexit('action=admin;area=lp_settings;sa=misc');
		}

		prepareDBSettingContext($config_vars);
	}

	public function blockAreas()
	{
		isAllowedTo('light_portal_manage_own_blocks');

		$subActions = [
			'main' => [new BlockArea, 'main'],
			'add'  => [new BlockArea, 'add'],
			'edit' => [new BlockArea, 'edit']
		];

		if ($this->user_info['is_admin']) {
			$subActions['export'] = [new BlockExport, 'main'];
			$subActions['import'] = [new BlockImport, 'main'];
		}

		$this->hook('addBlockAreas', [&$subActions]);

		$this->loadGeneralSettingParameters($subActions, 'main');
	}

	public function pageAreas()
	{
		isAllowedTo('light_portal_manage_own_pages');

		$subActions = [
			'main' => [new PageArea, 'main'],
			'add'  => [new PageArea, 'add'],
			'edit' => [new PageArea, 'edit']
		];

		if ($this->user_info['is_admin']) {
			$subActions['export'] = [new PageExport, 'main'];
			$subActions['import'] = [new PageImport, 'main'];
		}

		$this->hook('addPageAreas', [&$subActions]);

		$this->loadGeneralSettingParameters($subActions, 'main');
	}

	public function pluginAreas()
	{
		isAllowedTo('admin_forum');

		$subActions = [
			'main' => [new PluginArea, 'main']
		];

		$this->hook('addPluginAreas', [&$subActions]);

		$this->loadGeneralSettingParameters($subActions, 'main');
	}

	private function getLastVersion(): string
	{
		$data = fetch_web_data('https://api.github.com/repos/dragomano/light-portal/releases/latest');

		if (empty($data))
			return LP_VERSION;

		$data = smf_json_decode($data, true);

		if (LP_RELEASE_DATE < $data['published_at'])
			return str_replace('v', '', $data['tag_name']);

		return LP_VERSION;
	}

	private function checkNewVersion()
	{
		if (version_compare(LP_VERSION, $new_version = $this->getLastVersion(), '<')) {
			$this->context['settings_insert_above'] = '
			<div class="noticebox">
				' . $this->txt['lp_new_version_is_available'] . ' (<a class="bbc_link" href="https://custom.simplemachines.org/mods/index.php?mod=4244" target="_blank" rel="noopener">' . $new_version . '</a>)
			</div>';
		}
	}

	/**
	 * Calls the requested subaction if it does exist; otherwise, calls the default action
	 *
	 * Вызывает метод, если он существует; в противном случае вызывается метод по умолчанию
	 */
	private function loadGeneralSettingParameters(array $subActions = [], ?string $defaultAction = null)
	{
		$this->showDocsLink();

		$this->require('ManageServer');

		$this->context['sub_template'] = 'show_settings';

		$defaultAction = $defaultAction ?: key($subActions);

		$subAction = $this->request()->has('sa') && isset($subActions[$this->request('sa')]) ? $this->request('sa') : $defaultAction;

		$this->context['sub_action'] = $subAction;

		call_helper($subActions[$subAction]);
	}

	private function showDocsLink()
	{
		if (empty($this->request('area'))) return;

		if (! empty($this->context['template_layers']) && strpos($this->request('area'), 'lp_') !== false) {
			loadTemplate('LightPortal/ViewDebug');

			$this->context['template_layers'][] = 'docs';
		}
	}

	private function generateDumpFile()
	{
		$portal_settings = "lp_enabled_plugins = '" . implode(', ', $this->context['lp_enabled_plugins']) . "'" . PHP_EOL;
		foreach ($this->modSettings as $key => $value) {
			if (strpos((string) $key, 'lp_') === 0 && isset($this->txt[$key]) && $value) {
				$portal_settings .= $key . ' = ' . var_export($value, true) . PHP_EOL;
			}
		}

		if (ob_get_level())
			ob_end_clean();

		header('Content-disposition: attachment; filename=portal_settings.txt');
		header('Content-type: text/plain');

		echo $portal_settings;

		exit;
	}

	private function prepareAliasList()
	{
		if ($this->request()->has('alias_list') === false)
			return;

		$data = $this->request()->json();

		if (empty($search = $data['search']))
			return;

		$results = (new PageRepository)->getAll(0, 30, 'alias', 'INSTR(LOWER(p.alias), {string:string}) > 0', ['string' => $this->smcFunc['strtolower']($search)]);
		$results = array_column($results, 'alias');
		array_walk($results, function (&$item) {
			$item = ['value' => $item];
		});

		exit(json_encode($results));
	}

	private function prepareTagsInComments()
	{
		$disabledBbc = empty($this->modSettings['lp_disabled_bbc_in_comments']) ? [] : explode(',', $this->modSettings['lp_disabled_bbc_in_comments']);
		$disabledBbc = isset($this->modSettings['disabledBBC']) ? [...$disabledBbc, ...explode(',', $this->modSettings['disabledBBC'])] : $disabledBbc;

		$temp = parse_bbc(false);
		$bbcTags = [];
		foreach ($temp as $tag) {
			if (! isset($tag['require_parents']))
				$bbcTags[] = $tag['tag'];
		}

		$bbcTags = array_unique($bbcTags);

		$this->context['bbc_sections'] = [
			'title'        => $this->txt['enabled_bbc_select'],
			'disabled'     => $disabledBbc ?: [],
			'all_selected' => empty($disabledBbc),
			'columns'      => []
		];

		$sectionTags = array_diff($bbcTags, $this->context['legacy_bbc']);

		foreach ($sectionTags as $tag) {
			$this->context['bbc_sections']['columns'][] = [
				'tag' => $tag
			];
		}
	}

	private function updatePriority(array $categories)
	{
		if (empty($categories))
			return;

		$conditions = '';
		foreach ($categories as $priority => $item) {
			$conditions .= ' WHEN category_id = ' . $item . ' THEN ' . $priority;
		}

		if (empty($conditions))
			return;

		$this->smcFunc['db_query']('', /** @lang text */ '
			UPDATE {db_prefix}lp_categories
			SET priority = CASE ' . $conditions . ' ELSE priority END
			WHERE category_id IN ({array_int:categories})',
			[
				'categories' => $categories
			]
		);

		$this->context['lp_num_queries']++;
	}

	private function add(string $name, string $desc = '')
	{
		if (empty($name))
			return;

		loadTemplate('LightPortal/ManageSettings');

		$result['error'] = true;

		$item = (int) $this->smcFunc['db_insert']('',
			'{db_prefix}lp_categories',
			[
				'name'        => 'string',
				'description' => 'string',
				'priority'    => 'int'
			],
			[
				$name,
				$desc,
				$this->getPriority()
			],
			['category_id'],
			1
		);

		$this->context['lp_num_queries']++;

		if ($item) {
			ob_start();

			show_single_category($item, ['name' => $name, 'desc' => $desc]);

			$new_cat = ob_get_clean();

			$result = [
				'success' => true,
				'section' => $new_cat,
				'item'    => $item
			];
		}

		$this->cache()->forget('all_categories');

		exit(json_encode($result));
	}

	private function updateName(int $item, string $value)
	{
		if (empty($item))
			return;

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_categories
			SET name = {string:name}
			WHERE category_id = {int:item}',
			[
				'name' => $value,
				'item' => $item
			]
		);

		$this->context['lp_num_queries']++;
	}

	private function updateDescription(int $item, string $value)
	{
		if (empty($item))
			return;

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_categories
			SET description = {string:desc}
			WHERE category_id = {int:item}',
			[
				'desc' => $value,
				'item' => $item
			]
		);

		$this->context['lp_num_queries']++;
	}

	private function remove(array $items)
	{
		if (empty($items))
			return;

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_categories
			WHERE category_id IN ({array_int:items})',
			[
				'items' => $items
			]
		);

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_pages
			SET category_id = {int:category}
			WHERE category_id IN ({array_int:items})',
			[
				'category' => 0,
				'items'    => $items
			]
		);

		$this->context['lp_num_queries'] += 2;

		$this->cache()->flush();
	}

	private function getPriority(): int
	{
		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT MAX(priority) + 1
			FROM {db_prefix}lp_categories',
			[]
		);

		[$priority] = $this->smcFunc['db_fetch_row']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return (int) $priority;
	}
}
