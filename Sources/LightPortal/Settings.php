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

		$this->checkNewVersion();

		$context['page_title'] = $context['settings_title'] = $txt['lp_base'];
		$context['post_url']   = $scripturl . '?action=admin;area=lp_settings;sa=basic;save';

		$context['permissions_excluded']['light_portal_manage_own_blocks'] = [-1, 0];
		$context['permissions_excluded']['light_portal_manage_own_pages']  = [-1, 0];
		$context['permissions_excluded']['light_portal_approve_pages']     = [-1, 0];

		$context['lp_all_categories']       = Helpers::getAllCategories();
		$context['lp_frontpage_categories'] = !empty($modSettings['lp_frontpage_categories']) ? explode(',', $modSettings['lp_frontpage_categories']) : [];
		$context['lp_frontpage_layout']     = FrontPage::getLayouts();

		loadTemplate('LightPortal/ManageSettings');

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
			array('text', 'lp_frontpage_alias', 80, 'subtext' => $txt['lp_frontpage_alias_subtext']),
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
			array('int', 'lp_num_items_per_page'),
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

		if ($return_config)
			return $config_vars;

		// Frontpage mode toggle
		$frontpage_mode_toggle = array('lp_frontpage_title', 'lp_frontpage_alias', 'lp_frontpage_categories', 'lp_frontpage_boards', 'lp_frontpage_pages', 'lp_frontpage_topics', 'lp_show_images_in_articles', 'lp_image_placeholder', 'lp_frontpage_time_format', 'lp_frontpage_custom_time_format', 'lp_show_teaser', 'lp_show_author', 'lp_show_num_views_and_comments', 'lp_frontpage_order_by_num_replies', 'lp_frontpage_article_sorting', 'lp_frontpage_layout', 'lp_frontpage_num_columns', 'lp_num_items_per_page');

		$frontpage_mode_toggle_dt = [];
		foreach ($frontpage_mode_toggle as $item) {
			$frontpage_mode_toggle_dt[] = 'setting_' . $item;
		}

		$frontpage_alias_toggle = array('lp_frontpage_title', 'lp_frontpage_categories', 'lp_frontpage_boards', 'lp_frontpage_pages', 'lp_frontpage_topics', 'lp_show_images_in_articles', 'lp_image_placeholder', 'lp_frontpage_time_format', 'lp_frontpage_custom_time_format', 'lp_show_teaser', 'lp_show_author', 'lp_show_num_views_and_comments','lp_frontpage_order_by_num_replies', 'lp_frontpage_article_sorting', 'lp_frontpage_layout', 'lp_frontpage_num_columns', 'lp_show_pagination', 'lp_use_simple_pagination', 'lp_num_items_per_page');

		$frontpage_alias_toggle_dt = [];
		foreach ($frontpage_alias_toggle as $item) {
			$frontpage_alias_toggle_dt[] = 'setting_' . $item;
		}

		$context['settings_post_javascript'] = '
		function toggleFrontpageMode() {
			let front_mode = $("#lp_frontpage_mode").val();
			let change_mode = front_mode > 0;
			let board_selector = $(".board_selector").parent("dd");

			$("#lp_standalone_mode").attr("disabled", front_mode == 0);

			if (front_mode == 0) {
				$("#lp_standalone_mode").prop("checked", false);
			}

			$("#' . implode(', #', $frontpage_mode_toggle) . '").closest("dd").toggle(change_mode);
			$("#' . implode(', #', $frontpage_mode_toggle_dt) . '").closest("dt").toggle(change_mode);
			board_selector.toggle(change_mode);

			let allow_change_title = !["0", "chosen_page"].includes(front_mode);

			$("#' . implode(', #', $frontpage_alias_toggle) . '").closest("dd").toggle(allow_change_title);
			$("#' . implode(', #', $frontpage_alias_toggle_dt) . '").closest("dt").toggle(allow_change_title);
			board_selector.toggle(allow_change_title);

			let allow_change_alias = front_mode == "chosen_page";

			$("#lp_frontpage_alias").closest("dd").toggle(allow_change_alias);
			$("#setting_lp_frontpage_alias").closest("dt").toggle(allow_change_alias);

			let allow_change_chosen_topics = front_mode == "chosen_topics";

			$("#lp_frontpage_topics").closest("dd").toggle(allow_change_chosen_topics);
			$("#setting_lp_frontpage_topics").closest("dt").toggle(allow_change_chosen_topics);

			let allow_change_chosen_pages = front_mode == "chosen_pages";

			$("#lp_frontpage_pages").closest("dd").toggle(allow_change_chosen_pages);
			$("#setting_lp_frontpage_pages").closest("dt").toggle(allow_change_chosen_pages);

			if (["chosen_topics", "all_pages", "chosen_pages"].includes(front_mode)) {
				let boards = $("#setting_lp_frontpage_boards").closest("dt");

				boards.hide();
				boards.next("dd").hide();
			}

			if (["all_topics", "chosen_topics", "chosen_boards", "chosen_pages"].includes(front_mode)) {
				let categories = $("#setting_lp_frontpage_categories").closest("dt");

				categories.hide();
				categories.next("dd").hide();
			}
		};

		toggleFrontpageMode();

		$("#lp_frontpage_mode").on("change", function () {
			toggleFrontpageMode();
			toggleTimeFormat();
		});';

		// Time format toggle
		$context['settings_post_javascript'] .= '
		function toggleTimeFormat() {
			let change_mode = $("#lp_frontpage_time_format").val() == 2;

			$("#lp_frontpage_custom_time_format").closest("dd").toggle(change_mode);
			$("#setting_lp_frontpage_custom_time_format").closest("dt").toggle(change_mode);
		};

		toggleTimeFormat();

		$("#lp_frontpage_time_format").on("change", function () {
			toggleTimeFormat()
		});';

		// Standalone mode toggle
		$standalone_mode_toggle = array('lp_standalone_url', 'lp_standalone_mode_disabled_actions');

		$standalone_mode_toggle_dt = [];
		foreach ($standalone_mode_toggle as $item) {
			$standalone_mode_toggle_dt[] = 'setting_' . $item;
		}

		$context['settings_post_javascript'] .= '
		function toggleStandaloneMode() {
			let change_mode = $("#lp_standalone_mode").prop("checked");

			$("#' . implode(', #', $standalone_mode_toggle) . '").closest("dd").toggle(change_mode);
			$("#' . implode(', #', $standalone_mode_toggle_dt) . '").closest("dt").toggle(change_mode);
		};

		toggleStandaloneMode();

		$("#lp_standalone_mode").on("click", function () {
			toggleStandaloneMode()
		});';

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
			saveDBSettings($save_vars);

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

		$modSettings['bbc_disabled_lp_disabled_bbc_in_comments'] = empty($modSettings['lp_disabled_bbc_in_comments']) ? [] : explode(',', $modSettings['lp_disabled_bbc_in_comments']);
		$modSettings['bbc_disabled_lp_disabled_bbc_in_comments'] = array_merge($modSettings['bbc_disabled_lp_disabled_bbc_in_comments'], explode(',', $modSettings['disabledBBC']));

		$txt['lp_show_comment_block_set']['none']    = $txt['lp_show_comment_block_set'][0];
		$txt['lp_show_comment_block_set']['default'] = $txt['lp_show_comment_block_set'][1];

		unset($txt['lp_show_comment_block_set'][0], $txt['lp_show_comment_block_set'][1]);

		$txt['lp_disabled_bbc_in_comments_subtext'] = sprintf($txt['lp_disabled_bbc_in_comments_subtext'], $scripturl . '?action=admin;area=featuresettings;sa=bbc;' . $context['session_var'] . '=' . $context['session_id'] . '#disabledBBC');

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
			array('bbc', 'lp_disabled_bbc_in_comments', 'subtext' => $txt['lp_disabled_bbc_in_comments_subtext']),
			array('int', 'lp_time_to_change_comments', 'postinput' => $txt['manageposts_minutes']),
			array('int', 'lp_num_comments_per_page'),
			array('select', 'lp_page_editor_type_default', $context['lp_page_types']),
			array('select', 'lp_permissions_default', $txt['lp_permissions']),
			array('check', 'lp_hide_blocks_in_admin_section'),
			array('title', 'lp_schema_org'),
			array('select', 'lp_page_og_image', $txt['lp_page_og_image_set']),
			array('text', 'lp_page_itemprop_address', 80),
			array('text', 'lp_page_itemprop_phone', 80)
		);

		if ($return_config)
			return $config_vars;

		// Show comment block toggle
		$show_comment_block_toggle = array('lp_disabled_bbc_in_comments', 'lp_time_to_change_comments', 'lp_num_comments_per_page');

		$show_comment_block_toggle_dt = [];
		foreach ($show_comment_block_toggle as $item) {
			$show_comment_block_toggle_dt[] = 'setting_' . $item;
		}

		addInlineJavaScript('
		function toggleShowCommentBlock() {
			let change_mode = $("#lp_show_comment_block").val() != "none";

			$("#' . implode(', #', $show_comment_block_toggle) . '").closest("dd").toggle(change_mode);
			$("#' . implode(', #', $show_comment_block_toggle_dt) . '").closest("dt").toggle(change_mode);

			if (change_mode && $("#lp_show_comment_block").val() != "default") {
				$("#lp_disabled_bbc_in_comments").closest("dd").hide();
				$("#setting_lp_disabled_bbc_in_comments").closest("dt").hide();
			}
		};

		toggleShowCommentBlock();

		$("#lp_show_comment_block").on("click", function () {
			toggleShowCommentBlock()
		});', true);

		// Save
		if (Helpers::request()->has('save')) {
			checkSession();

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
			saveDBSettings($save_vars);

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

		$context['sub_template'] = 'category_settings';
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

		Addons::run('addPanels', array(&$config_vars));

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
			saveDBSettings($save_vars);

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
		if (!isset($modSettings['lp_page_action']))
			$add_settings['lp_page_action'] = LP_PAGE_ACTION;
		if (!empty($add_settings))
			updateSettings($add_settings);

		$config_vars = array(
			array('title', 'lp_debug_and_caching'),
			array('check', 'lp_show_debug_info', 'help' => 'lp_show_debug_info_help'),
			array('check', 'lp_show_cache_info', 'disabled' => empty($modSettings['lp_show_debug_info'])),
			array('int', 'lp_cache_update_interval', 'postinput' => $txt['seconds']),
			array('title', 'lp_compatibility_mode'),
			array('text', 'lp_portal_action', 'preinput' => $scripturl . '?action='),
			array('text', 'lp_page_action', 'preinput' => $scripturl . '?', 'postinput' => '=somealias'),
			array('title', 'admin_maintenance'),
			array('check', 'lp_weekly_cleaning')
		);

		Addons::run('addMisc', array(&$config_vars));

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
			saveDBSettings($save_vars);

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
}
