<?php

namespace Bugo\LightPortal;

/**
 * Settings.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Settings
{
	/**
	 * Make a section with the mod settings in the admin panel
	 *
	 * Формируем раздел с настройками мода в админке
	 *
	 * @param array $admin_areas
	 * @return void
	 */
	public function adminAreas(array &$admin_areas)
	{
		global $txt, $context;

		loadCSSFile('light_portal/slimselect.min.css');
		loadJavaScriptFile('light_portal/slimselect.min.js');

		loadJavaScriptFile('https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2/dist/alpine.min.js', array('external' => true, 'defer' => true));
		loadJavaScriptFile('light_portal/admin.js', array('minimize' => true));

		loadLanguage('ManageSettings');

		$counter = array_search('layout', array_keys($admin_areas)) + 1;

		$admin_areas = array_merge(
			array_slice($admin_areas, 0, (int) $counter, true),
			array(
				'lp_portal' => array(
					'title' => $txt['lp_portal'],
					'permission' => array('admin_forum', 'light_portal_manage_own_blocks', 'light_portal_manage_own_pages'),
					'areas' => array(
						'lp_settings' => array(
							'label' => $txt['settings'],
							'function' => array($this, 'settingAreas'),
							'icon' => 'features',
							'permission' => array('admin_forum'),
							'subsections' => array(
								'basic'      => array('<i class="fas fa-cog fa-spin"></i> ' . $txt['mods_cat_features']),
								'extra'      => array('<i class="fas fa-pager"></i> ' . $txt['lp_extra']),
								'categories' => array('<i class="fas fa-folder"></i> ' . $txt['lp_categories']),
								'panels'     => array('<i class="fas fa-columns"></i> ' . $txt['lp_panels']),
								'misc'       => array('<i class="fas fa-tools"></i> ' . $txt['lp_misc'])
							)
						),
						'lp_blocks' => array(
							'label' => $txt['lp_blocks'],
							'function' => array($this, 'blockAreas'),
							'icon' => 'modifications',
							'amt' => $context['lp_num_active_blocks'],
							'permission' => array('admin_forum', 'light_portal_manage_own_blocks'),
							'subsections' => array(
								'main' => array('<i class="fas fa-tasks"></i> ' . $txt['lp_blocks_manage']),
								'add'  => array('<i class="fas fa-plus fa-spin"></i> ' . $txt['lp_blocks_add'])
							)
						),
						'lp_pages' => array(
							'label' => $txt['lp_pages'],
							'function' => array($this, 'pageAreas'),
							'icon' => 'reports',
							'amt' => $context['lp_num_active_pages'],
							'permission' => array('admin_forum', 'light_portal_manage_own_pages'),
							'subsections' => array(
								'main' => array('<i class="fas fa-tasks"></i> ' . $txt['lp_pages_manage']),
								'add'  => array('<i class="fas fa-plus fa-spin"></i> ' . $txt['lp_pages_add'])
							)
						),
						'lp_plugins' => array(
							'label' => $txt['lp_plugins'],
							'function' => array($this, 'pluginAreas'),
							'icon' => 'maintain',
							'amt' => count($context['lp_enabled_plugins']),
							'permission' => array('admin_forum'),
							'subsections' => array(
								'main' => array('<i class="fas fa-tasks"></i> ' . $txt['lp_plugins_manage'])
							)
						)
					)
				)
			),
			array_slice($admin_areas, $counter, count($admin_areas), true)
		);

		if ($context['user']['is_admin']) {
			$admin_areas['lp_portal']['areas']['lp_blocks']['subsections'] += array(
				'export' => array('<i class="fas fa-file-export"></i> ' . $txt['lp_blocks_export']),
				'import' => array('<i class="fas fa-file-import"></i> ' . $txt['lp_blocks_import'])
			);

			$admin_areas['lp_portal']['areas']['lp_pages']['subsections'] += array(
				'export' => array('<i class="fas fa-file-export"></i> ' . $txt['lp_pages_export']),
				'import' => array('<i class="fas fa-file-import"></i> ' . $txt['lp_pages_import'])
			);
		}

		Addons::run('addAdminAreas', array(&$admin_areas));
	}

	/**
	 * Easy access to the mod settings via a quick search in the admin panel
	 *
	 * Легкий доступ к настройкам мода через быстрый поиск в админке
	 *
	 * @param array $language_files
	 * @param array $include_files
	 * @param array $settings_search
	 * @return void
	 */
	public function adminSearch(array &$language_files, array &$include_files, array &$settings_search)
	{
		$settings_search[] = array(array($this, 'basic'), 'area=lp_settings;sa=basic');
		$settings_search[] = array(array($this, 'extra'), 'area=lp_settings;sa=extra');
		$settings_search[] = array(array($this, 'panels'), 'area=lp_settings;sa=panels');
		$settings_search[] = array(array($this, 'misc'), 'area=lp_settings;sa=misc');
	}

	/**
	 * List of tabs with settings
	 *
	 * Список вкладок с настройками
	 *
	 * @return void
	 */
	public function settingAreas()
	{
		global $context, $txt, $smcFunc, $scripturl, $modSettings;

		isAllowedTo('admin_forum');

		$subActions = array(
			'basic'      => array($this, 'basic'),
			'extra'      => array($this, 'extra'),
			'categories' => array($this, 'categories'),
			'panels'     => array($this, 'panels'),
			'misc'       => array($this, 'misc')
		);

		db_extend();

		// Tabs
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => '<a href="https://dragomano.github.io/Light-Portal/" target="_blank" rel="noopener"><span class="main_icons help"></span></a> ' . LP_NAME,
			'tabs' => array(
				'basic' => array(
					'description' => sprintf($txt['lp_base_info'], LP_VERSION, phpversion(), $smcFunc['db_title'], $smcFunc['db_get_version']())
				),
				'extra' => array(
					'description' => $txt['lp_extra_info']
				),
				'categories' => array(
					'description' => $txt['lp_categories_info']
				),
				'panels' => array(
					'description' => sprintf($txt['lp_panels_info'], LP_NAME, 'https://evgenyrodionov.github.io/flexboxgrid2/')
				),
				'misc' => array(
					'description' => $txt['lp_misc_info']
				)
			)
		);

		$this->loadGeneralSettingParameters($subActions, 'basic');

		if (Helpers::request()->has('getDebugInfo'))
			$this->generateDumpFile();

		if (!isset($context['settings_title']))
			return;

		$context['settings_title'] .= '<span class="floatright" x-data>
			<a @mouseover="$event.target.style.color = \'yellow\'" @mouseout="$event.target.style.color = \'white\'" @click="location.href = location.href + \';getDebugInfo\'" title="' . $txt['lp_debug_info'] . '"><i class="fas fa-info-circle"></i></a>
		</span>';
	}

	/**
	 * Output general settings
	 *
	 * Выводим общие настройки
	 *
	 * @param bool $return_config
	 * @return array|void
	 */
	public function basic(bool $return_config = false)
	{
		global $context, $txt, $scripturl, $modSettings, $boardurl, $settings;

		$this->prepareAliasList();

		$context['page_title'] = $context['settings_title'] = $txt['lp_base'];
		$context['post_url']   = $scripturl . '?action=admin;area=lp_settings;sa=basic;save';

		$context['permissions_excluded']['light_portal_manage_own_blocks'] = [-1, 0];
		$context['permissions_excluded']['light_portal_manage_own_pages']  = [-1, 0];
		$context['permissions_excluded']['light_portal_approve_pages']     = [-1, 0];

		$context['lp_all_categories']       = Helpers::getAllCategories();
		$context['lp_frontpage_categories'] = !empty($modSettings['lp_frontpage_categories']) ? explode(',', $modSettings['lp_frontpage_categories']) : [0];
		$context['lp_frontpage_layout']     = FrontPage::getLayouts();

		$txt['select_boards_from_list'] = $txt['lp_select_boards_from_list'];

		// Initial settings
		$add_settings = [];
		if (!isset($modSettings['lp_frontpage_title']))
			$add_settings['lp_frontpage_title'] = $context['forum_name'];
		if (!isset($modSettings['lp_frontpage_alias']))
			$add_settings['lp_frontpage_alias'] = 'home';
		if (!isset($modSettings['lp_show_num_views_and_comments']))
			$add_settings['lp_show_num_views_and_comments'] = 1;
		if (!isset($modSettings['lp_frontpage_article_sorting']))
			$add_settings['lp_frontpage_article_sorting'] = 1;
		if (!isset($modSettings['lp_num_items_per_page']))
			$add_settings['lp_num_items_per_page'] = 10;
		if (!isset($modSettings['lp_standalone_url']))
			$add_settings['lp_standalone_url'] = $boardurl . '/portal.php';
		if (!isset($modSettings['lp_prohibit_php']))
			$add_settings['lp_prohibit_php'] = 1;
		if (!empty($add_settings))
			updateSettings($add_settings);

		$config_vars = array(
			array(
				'select',
				'lp_frontpage_mode',
				array_combine(
					array(0, 'chosen_page', 'all_pages', 'chosen_pages', 'all_topics', 'chosen_topics', 'chosen_boards'),
					$txt['lp_frontpage_mode_set']
				)
			),
			array('text', 'lp_frontpage_title', '80" placeholder="' . $context['forum_name'] . ' - ' . $txt['lp_portal']),
			array('select', 'lp_frontpage_alias', [], 'subtext' => $txt['lp_frontpage_alias_subtext']),
			array('callback', 'frontpage_categories'),
			array('boards', 'lp_frontpage_boards'),
			array('large_text', 'lp_frontpage_pages', 'subtext' => $txt['lp_frontpage_pages_subtext']),
			array('large_text', 'lp_frontpage_topics', 'subtext' => $txt['lp_frontpage_topics_subtext']),
			array('check', 'lp_show_images_in_articles', 'help' => 'lp_show_images_in_articles_help'),
			array('text', 'lp_image_placeholder', '80" placeholder="' . $txt['lp_example'] . $settings['default_images_url'] . '/smflogo.svg'),
			array('select', 'lp_frontpage_time_format', $txt['lp_frontpage_time_format_set']),
			array('text', 'lp_frontpage_custom_time_format', 'help' => 'lp_frontpage_custom_time_format_help'),
			array('check', 'lp_show_teaser'),
			array('check', 'lp_show_author', 'help' => 'lp_show_author_help'),
			array('check', 'lp_show_num_views_and_comments'),
			array('check', 'lp_frontpage_order_by_num_replies'),
			array('select', 'lp_frontpage_article_sorting', $txt['lp_frontpage_article_sorting_set'], 'help' => 'lp_frontpage_article_sorting_help'),
			array('select', 'lp_frontpage_layout', $context['lp_frontpage_layout']),
			array('select', 'lp_frontpage_num_columns', $txt['lp_frontpage_num_columns_set']),
			array('select', 'lp_show_pagination', $txt['lp_show_pagination_set']),
			array('check', 'lp_use_simple_pagination'),
			array('int', 'lp_num_items_per_page', 'min' => 1),
			array('title', 'lp_standalone_mode_title'),
			array('check', 'lp_standalone_mode', 'label' => $txt['lp_action_on']),
			array(
				'text',
				'lp_standalone_url',
				'80" placeholder="' . $txt['lp_example'] . $boardurl . '/portal.php',
				'help' => 'lp_standalone_url_help'
			),
			array(
				'text',
				'lp_standalone_mode_disabled_actions',
				'80" placeholder="' . $txt['lp_example'] . 'mlist,calendar',
				'subtext' => $txt['lp_standalone_mode_disabled_actions_subtext'],
				'help' => 'lp_standalone_mode_disabled_actions_help'
			),
			array('title', 'edit_permissions'),
			array('check', 'lp_prohibit_php', 'invalid' => true),
			array('permissions', 'light_portal_view', 'help' => 'permissionhelp_light_portal_view'),
			array('permissions', 'light_portal_manage_own_blocks', 'help' => 'permissionhelp_light_portal_manage_own_blocks'),
			array('permissions', 'light_portal_manage_own_pages', 'help' => 'permissionhelp_light_portal_manage_own_pages'),
			array('permissions', 'light_portal_approve_pages', 'help' => 'permissionhelp_light_portal_approve_pages')
		);

		Addons::run('addBasicSettings', array(&$config_vars));

		if ($return_config)
			return $config_vars;

		$this->checkNewVersion();

		loadTemplate('LightPortal/ManageSettings');

		$context['template_layers'][] = 'lp_basic_settings';

		// Save
		if (Helpers::request()->has('save')) {
			checkSession();

			if (Helpers::post()->isEmpty('lp_frontpage_mode'))
				Helpers::post()->put('lp_standalone_url', 0);

			if (Helpers::post()->filled('lp_image_placeholder'))
				Helpers::post()->put('lp_image_placeholder', Helpers::validate(Helpers::post('lp_image_placeholder'), 'url'));

			if (Helpers::post()->filled('lp_standalone_url'))
				Helpers::post()->put('lp_standalone_url', Helpers::validate(Helpers::post('lp_standalone_url'), 'url'));

			$frontpage_categories = [];
			if (Helpers::post()->filled('lp_frontpage_categories')) {
				foreach (Helpers::post('lp_frontpage_categories') as $id => $dummy)
					if (isset($context['lp_all_categories'][$id]))
						$frontpage_categories[] = $id;
			}

			Helpers::post()->put('lp_frontpage_categories', !empty($frontpage_categories) ? implode(',', $frontpage_categories) : '');

			$save_vars = $config_vars;
			$save_vars[] = ['text', 'lp_frontpage_categories'];
			$save_vars[] = ['text', 'lp_frontpage_alias'];

			Addons::run('addBasicSaveSettings', array(&$save_vars));

			saveDBSettings($save_vars);
			$_SESSION['adm-save'] = true;
			Helpers::cache()->flush();

			redirectexit('action=admin;area=lp_settings;sa=basic');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Output page and block settings
	 *
	 * Выводим настройки страниц и блоков
	 *
	 * @param bool $return_config
	 * @return array|void
	 */
	public function extra(bool $return_config = false)
	{
		global $context, $txt, $scripturl, $modSettings;

		$context['page_title'] = $context['settings_title'] = $txt['lp_extra'];
		$context['post_url']   = $scripturl . '?action=admin;area=lp_settings;sa=extra;save';

		$txt['lp_show_comment_block_set']['none']    = $txt['lp_show_comment_block_set'][0];
		$txt['lp_show_comment_block_set']['default'] = $txt['lp_show_comment_block_set'][1];

		unset($txt['lp_show_comment_block_set'][0], $txt['lp_show_comment_block_set'][1]);
		asort($txt['lp_show_comment_block_set']);

		$txt['lp_fa_source_title'] .= ' <img class="floatright" src="https://data.jsdelivr.com/v1/package/npm/@fortawesome/fontawesome-free/badge?style=rounded" alt="">';

		// Initial settings
		$add_settings = [];
		if (!isset($modSettings['lp_num_comments_per_page']))
			$add_settings['lp_num_comments_per_page'] = 12;
		if (!empty($add_settings))
			updateSettings($add_settings);

		$config_vars = array(
			array('check', 'lp_show_page_permissions', 'subtext' => $txt['lp_show_page_permissions_subtext']),
			array('check', 'lp_show_tags_on_page'),
			array('check', 'lp_show_items_as_articles'),
			array('check', 'lp_show_related_pages'),
			array('select', 'lp_show_comment_block', $txt['lp_show_comment_block_set']),
			array('callback', 'disabled_bbc_in_comments'),
			array('int', 'lp_time_to_change_comments', 'postinput' => $txt['manageposts_minutes']),
			array('int', 'lp_num_comments_per_page'),
			array('select', 'lp_page_editor_type_default', $context['lp_page_types']),
			array('select', 'lp_permissions_default', $txt['lp_permissions']),
			array('check', 'lp_hide_blocks_in_admin_section'),
			array('title', 'lp_schema_org'),
			array('select', 'lp_page_og_image', $txt['lp_page_og_image_set']),
			array('text', 'lp_page_itemprop_address', 80),
			array('text', 'lp_page_itemprop_phone', 80),

			// FA source
			array('title', 'lp_fa_source_title'),
			array(
				'select',
				'lp_fa_source',
				array(
					'none'      => $txt['no'],
					'css_cdn'   => $txt['lp_fa_source_css_cdn'],
					'js_cdn'    => $txt['lp_fa_source_js_cdn'],
					'css_local' => $txt['lp_fa_source_css_local'],
					'js_local'  => $txt['lp_fa_source_js_local'],
					'custom'    => $txt['lp_fa_custom']
				),
				'onchange' => 'document.getElementById(\'lp_fa_custom\').disabled = this.value != \'custom\';'
			),
			array(
				'text',
				'lp_fa_custom',
				'disabled' => isset($modSettings['lp_fa_source']) && $modSettings['lp_fa_source'] != 'custom',
				'size' => 75
			),
		);

		Addons::run('addExtraSettings', array(&$config_vars));

		if ($return_config)
			return $config_vars;

		loadTemplate('LightPortal/ManageSettings');

		$context['template_layers'][] = 'lp_extra_settings';

		$this->prepareTagsInComments();

		// Save
		if (Helpers::request()->has('save')) {
			checkSession();

			if (Helpers::post()->filled('lp_fa_custom'))
				Helpers::post()->put('lp_fa_custom', Helpers::validate(Helpers::post('lp_fa_custom'), 'url'));

			// Clean up the tags
			$bbcTags = [];
			$parse_tags = parse_bbc(false);

			foreach ($parse_tags as $tag) {
				$bbcTags[] = $tag['tag'];
			}

			if (Helpers::post()->has('lp_disabled_bbc_in_comments_enabledTags') === false) {
				Helpers::post()->put('lp_disabled_bbc_in_comments_enabledTags', []);
			} elseif (!is_array(Helpers::post('lp_disabled_bbc_in_comments_enabledTags'))) {
				Helpers::post()->put('lp_disabled_bbc_in_comments_enabledTags', array(Helpers::post('lp_disabled_bbc_in_comments_enabledTags')));
			}

			Helpers::post()->put('lp_enabled_bbc_in_comments', implode(',', Helpers::post('lp_disabled_bbc_in_comments_enabledTags')));
			Helpers::post()->put('lp_disabled_bbc_in_comments', implode(',', array_diff($bbcTags, Helpers::post('lp_disabled_bbc_in_comments_enabledTags'))));

			$save_vars = $config_vars;
			$save_vars[] = ['text', 'lp_enabled_bbc_in_comments'];
			$save_vars[] = ['text', 'lp_disabled_bbc_in_comments'];

			Addons::run('addExtraSaveSettings', array(&$save_vars));

			saveDBSettings($save_vars);
			$_SESSION['adm-save'] = true;
			Helpers::cache()->flush();

			redirectexit('action=admin;area=lp_settings;sa=extra');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Output category settings
	 *
	 * Выводим настройки рубрик
	 *
	 * @return void
	 */
	public function categories()
	{
		global $context, $txt;

		loadTemplate('LightPortal/ManageSettings');

		$context['page_title'] = $txt['lp_categories'];

		$category = new Lists\Category;

		$context['lp_categories'] = $category->getList();

		unset($context['lp_categories'][0]);

		if (Helpers::request()->has('actions')) {
			$data = Helpers::request()->json();

			if (!empty($data['update_priority']))
				$category->updatePriority($data['update_priority']);

			if (!empty($data['new_name']))
				$category->add($data['new_name'], $data['new_desc'] ?? '');

			if (!empty($data['name']))
				$category->updateName((int) $data['item'], $data['name']);

			if (!empty($data['desc']))
				$category->updateDescription((int) $data['item'], $data['desc']);

			if (!empty($data['del_item']))
				$category->remove([(int) $data['del_item']]);

			exit;
		}

		$context['sub_template'] = 'lp_category_settings';
	}

	/**
	 * Output panel settings
	 *
	 * Выводим настройки панелей
	 *
	 * @param bool $return_config
	 * @return array|void
	 */
	public function panels(bool $return_config = false)
	{
		global $context, $txt, $scripturl, $modSettings;

		loadTemplate('LightPortal/ManageSettings');

		addInlineCss('
		dl.settings {
			overflow: hidden;
		}');

		$context['page_title'] = $context['settings_title'] = $txt['lp_panels'];
		$context['post_url']   = $scripturl . '?action=admin;area=lp_settings;sa=panels;save';

		// Initial settings | Первоначальные настройки
		$add_settings = [];
		if (!isset($modSettings['lp_swap_left_right']))
			$add_settings['lp_swap_left_right'] = !empty($txt['lang_rtl']);
		if (!isset($modSettings['lp_header_panel_width']))
			$add_settings['lp_header_panel_width'] = 12;
		if (!isset($modSettings['lp_left_panel_width']))
			$add_settings['lp_left_panel_width'] = json_encode($context['lp_left_panel_width']);
		if (!isset($modSettings['lp_right_panel_width']))
			$add_settings['lp_right_panel_width'] = json_encode($context['lp_right_panel_width']);
		if (!isset($modSettings['lp_footer_panel_width']))
			$add_settings['lp_footer_panel_width'] = 12;
		if (!isset($modSettings['lp_left_panel_sticky']))
			$add_settings['lp_left_panel_sticky'] = 1;
		if (!isset($modSettings['lp_right_panel_sticky']))
			$add_settings['lp_right_panel_sticky'] = 1;
		if (!empty($add_settings))
			updateSettings($add_settings);

		$context['lp_left_right_width_values']    = [2, 3, 4];
		$context['lp_header_footer_width_values'] = [6, 8, 10, 12];

		$config_vars = array(
			array('check', 'lp_swap_header_footer'),
			array('check', 'lp_swap_left_right'),
			array('check', 'lp_swap_top_bottom'),
			array('callback', 'panel_layout'),
			array('callback', 'panel_direction')
		);

		Addons::run('addPanelsSettings', array(&$config_vars));

		if ($return_config)
			return $config_vars;

		$context['sub_template'] = 'show_settings';

		if (Helpers::request()->has('save')) {
			checkSession();

			Helpers::post()->put('lp_left_panel_width', json_encode(Helpers::post('lp_left_panel_width')));
			Helpers::post()->put('lp_right_panel_width', json_encode(Helpers::post('lp_right_panel_width')));
			Helpers::post()->put('lp_panel_direction', json_encode(Helpers::post('lp_panel_direction')));

			$save_vars = $config_vars;

			$save_vars[] = ['int', 'lp_header_panel_width'];
			$save_vars[] = ['text', 'lp_left_panel_width'];
			$save_vars[] = ['text', 'lp_right_panel_width'];
			$save_vars[] = ['int', 'lp_footer_panel_width'];
			$save_vars[] = ['check', 'lp_left_panel_sticky'];
			$save_vars[] = ['check', 'lp_right_panel_sticky'];
			$save_vars[] = ['text', 'lp_panel_direction'];

			Addons::run('addPanelsSaveSettings', array(&$save_vars));

			saveDBSettings($save_vars);
			$_SESSION['adm-save'] = true;
			redirectexit('action=admin;area=lp_settings;sa=panels');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Output additional settings
	 *
	 * Выводим дополнительные настройки
	 *
	 * @param bool $return_config
	 * @return array|void
	 */
	public function misc(bool $return_config = false)
	{
		global $context, $txt, $scripturl, $modSettings, $smcFunc;

		$context['page_title'] = $txt['lp_misc'];
		$context['post_url']   = $scripturl . '?action=admin;area=lp_settings;sa=misc;save';

		// Initial settings
		$add_settings = [];
		if (!isset($modSettings['lp_cache_update_interval']))
			$add_settings['lp_cache_update_interval'] = LP_CACHE_TIME;
		if (!isset($modSettings['lp_portal_action']))
			$add_settings['lp_portal_action'] = LP_ACTION;
		if (!isset($modSettings['lp_page_param']))
			$add_settings['lp_page_param'] = LP_PAGE_PARAM;
		if (!empty($add_settings))
			updateSettings($add_settings);

		$config_vars = array(
			array('title', 'lp_debug_and_caching'),
			array('check', 'lp_show_debug_info', 'help' => 'lp_show_debug_info_help'),
			array('check', 'lp_show_cache_info', 'disabled' => empty($modSettings['lp_show_debug_info'])),
			array('int', 'lp_cache_update_interval', 'postinput' => $txt['seconds']),
			array('title', 'lp_compatibility_mode'),
			array('text', 'lp_portal_action', 'subtext' => $scripturl . '?action=<strong>' . LP_ACTION . '</strong>'),
			array('text', 'lp_page_param', 'subtext' => $scripturl . '?<strong>' . LP_PAGE_PARAM . '</strong>=somealias'),
			array('title', 'admin_maintenance'),
			array('check', 'lp_weekly_cleaning')
		);

		Addons::run('addMiscSettings', array(&$config_vars));

		if ($return_config)
			return $config_vars;

		$context['sub_template'] = 'show_settings';

		if (Helpers::request()->has('save')) {
			checkSession();

			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}background_tasks
				WHERE task_class = {string:task_class}',
				array(
					'task_class' => '\Bugo\LightPortal\Task'
				)
			);

			if (Helpers::post()->has('lp_weekly_cleaning')) {
				$smcFunc['db_insert']('insert',
					'{db_prefix}background_tasks',
					array('task_file' => 'string-255', 'task_class' => 'string-255', 'task_data' => 'string'),
					array('$sourcedir/LightPortal/Task.php', '\Bugo\LightPortal\Task', ''),
					array('id_task')
				);
			}

			$save_vars = $config_vars;

			Addons::run('addMiscSaveSettings', array(&$save_vars));

			saveDBSettings($save_vars);
			$_SESSION['adm-save'] = true;
			redirectexit('action=admin;area=lp_settings;sa=misc');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * The list of available areas to control the blocks
	 *
	 * Список доступных областей для управления блоками
	 *
	 * @return void
	 */
	public function blockAreas()
	{
		global $user_info;

		isAllowedTo('light_portal_manage_own_blocks');

		$subActions = array(
			'main' => array(new ManageBlocks, 'main'),
			'add'  => array(new ManageBlocks, 'add'),
			'edit' => array(new ManageBlocks, 'edit')
		);

		if ($user_info['is_admin']) {
			$subActions['export'] = array(new Impex\BlockExport, 'main');
			$subActions['import'] = array(new Impex\BlockImport, 'main');
		}

		Addons::run('addBlockAreas', array(&$subActions));

		$this->loadGeneralSettingParameters($subActions, 'main');
	}

	/**
	 * The list of available fields to control the pages
	 *
	 * Список доступных областей для управления страницами
	 *
	 * @return void
	 */
	public function pageAreas()
	{
		global $user_info;

		isAllowedTo('light_portal_manage_own_pages');

		$subActions = array(
			'main' => array(new ManagePages, 'main'),
			'add'  => array(new ManagePages, 'add'),
			'edit' => array(new ManagePages, 'edit')
		);

		if ($user_info['is_admin']) {
			$subActions['export'] = array(new Impex\PageExport, 'main');
			$subActions['import'] = array(new Impex\PageImport, 'main');
		}

		Addons::run('addPageAreas', array(&$subActions));

		$this->loadGeneralSettingParameters($subActions, 'main');
	}

	/**
	 * The list of available fields to control the plugins
	 *
	 * Список доступных областей для управления плагинами
	 *
	 * @return void
	 */
	public function pluginAreas()
	{
		isAllowedTo('admin_forum');

		$subActions = array(
			'main' => array(new ManagePlugins, 'main')
		);

		Addons::run('addPluginAreas', array(&$subActions));

		$this->loadGeneralSettingParameters($subActions, 'main');
	}

	/**
	 * Get the number of the last version
	 *
	 * Получаем номер последней версии LP
	 *
	 * @return string
	 */
	public function getLastVersion(): string
	{
		$data = fetch_web_data('https://api.github.com/repos/dragomano/light-portal/releases/latest');

		if (empty($data))
			return LP_VERSION;

		$data = json_decode($data, true);

		if (LP_RELEASE_DATE < $data['published_at'])
			return str_replace('v', '', $data['tag_name']);

		return LP_VERSION;
	}

	/**
	 * Check new version status
	 *
	 * Проверяем наличие новой версии
	 *
	 * @return void
	 */
	private function checkNewVersion()
	{
		global $context, $txt;

		// Check once a week | Проверяем раз в неделю
		if (version_compare(LP_VERSION, $new_version = Helpers::cache('last_version')->setLifeTime(604800)->setFallback(__CLASS__, 'getLastVersion'), '<')) {
			$context['settings_insert_above'] = '
			<div class="noticebox">
				' . $txt['lp_new_version_is_available'] . ' (<a class="bbc_link" href="https://custom.simplemachines.org/mods/index.php?mod=4244" target="_blank" rel="noopener">' . $new_version . '</a>)
			</div>';
		}
	}

	/**
	 * Calls the requested subaction if it does exist; otherwise, calls the default action
	 *
	 * Вызывает метод, если он существует; в противном случае вызывается метод по умолчанию
	 *
	 * @param array $subActions
	 * @param string|null $defaultAction
	 * @return void
	 */
	private function loadGeneralSettingParameters(array $subActions = [], string $defaultAction = null)
	{
		global $context;

		Helpers::require('ManageServer');

		$context['sub_template'] = 'show_settings';

		$defaultAction = $defaultAction ?: key($subActions);

		$subAction = Helpers::request()->has('sa') && isset($subActions[Helpers::request('sa')]) ? Helpers::request('sa') : $defaultAction;

		$context['sub_action'] = $subAction;

		call_helper($subActions[$subAction]);
	}

	/**
	 * @return void
	 */
	private function generateDumpFile()
	{
		global $context, $modSettings, $txt;

		$portal_settings = "lp_enabled_plugins = '" . implode(', ', $context['lp_enabled_plugins']) . "'" . PHP_EOL;
		foreach ($modSettings as $key => $value) {
			if (strpos($key, 'lp_') === 0 && isset($txt[$key]) && !empty($modSettings[$key])) {
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

	/**
	 * @return void
	 */
	private function prepareAliasList()
	{
		global $smcFunc;

		if (Helpers::request()->has('alias_list') === false)
			return;

		$data = Helpers::request()->json();

		if (empty($search = $data['search']))
			return;

		if (($items = Helpers::cache()->get('page_aliases_' . $search)) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT alias
				FROM {db_prefix}lp_pages
				WHERE alias LIKE lower({string:search})
				ORDER BY alias
				LIMIT 30',
				array(
					'search' => '%' . $search . '%'
				)
			);

			$items = [];
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				$items[] = $row['alias'];
			}

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put('page_aliases_' . $search, $items);
		}

		$results = [];
		foreach ($items as $item) {
			$results[] = [
				'text' => $item
			];
		}

		exit(json_encode($results));
	}

	/**
	 * @return void
	 */
	private function prepareTagsInComments()
	{
		global $modSettings, $context, $txt;

		$disabledBbc = empty($modSettings['lp_disabled_bbc_in_comments']) ? [] : explode(',', $modSettings['lp_disabled_bbc_in_comments']);
		$disabledBbc = array_merge($disabledBbc, explode(',', $modSettings['disabledBBC']));

		$temp = parse_bbc(false);
		$bbcTags = [];
		foreach ($temp as $tag)
			if (!isset($tag['require_parents']))
				$bbcTags[] = $tag['tag'];

		$bbcTags = array_unique($bbcTags);

		$context['bbc_sections'] = array(
			'title'        => $txt['enabled_bbc_select'],
			'disabled'     => $disabledBbc ?: [],
			'all_selected' => empty($disabledBbc),
			'columns'      => []
		);

		$sectionTags = array_diff($bbcTags, $context['legacy_bbc']);

		foreach ($sectionTags as $tag) {
			$context['bbc_sections']['columns'][] = array(
				'tag' => $tag
			);
		}
	}
}
