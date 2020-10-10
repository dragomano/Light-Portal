<?php

namespace Bugo\LightPortal;

/**
 * Settings.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.1
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
	public static function adminAreas(array &$admin_areas)
	{
		global $sourcedir, $txt, $context;

		require_once($sourcedir . '/ManageSettings.php');
		loadLanguage('ManageSettings');

		// Looking for the "Forum" section... | Ищем раздел "Форум"...
		$counter = array_search('layout', array_keys($admin_areas)) + 1;

		// ... and add a "Portal" section from the right | ... и добавляем справа раздел "Портал"
		$admin_areas = array_merge(
			array_slice($admin_areas, 0, (int) $counter, true),
			array(
				'lp_portal' => array(
					'title' => $txt['lp_portal'],
					'permission' => array('admin_forum', 'light_portal_manage_blocks', 'light_portal_manage_own_pages'),
					'areas' => array(
						'lp_settings' => array(
							'label' => $txt['settings'],
							'function' => function () {
								self::settingArea();
							},
							'icon' => 'features',
							'permission' => array('admin_forum'),
							'subsections' => array(
								'basic'   => array($txt['mods_cat_features']),
								'extra'   => array($txt['lp_extra']),
								'panels'  => array($txt['lp_panels']),
								'plugins' => array($txt['lp_plugins'])
							)
						),
						'lp_blocks' => array(
							'label' => $txt['lp_blocks'],
							'function' => function () {
								self::blockArea();
							},
							'icon' => 'modifications',
							'amt' => count($context['lp_active_blocks']),
							'permission' => array('admin_forum', 'light_portal_manage_blocks'),
							'subsections' => array(
								'main' => array($txt['lp_blocks_manage']),
								'add'  => array($txt['lp_blocks_add'])
							)
						),
						'lp_pages' => array(
							'label' => $txt['lp_pages'],
							'function' => function () {
								self::pageArea();
							},
							'icon' => 'posts',
							'amt' => $context['lp_num_active_pages'],
							'permission' => array('admin_forum', 'light_portal_manage_own_pages'),
							'subsections' => array(
								'main' => array($txt['lp_pages_manage']),
								'add'  => array($txt['lp_pages_add'])
							)
						)
					)
				)
			),
			array_slice($admin_areas, $counter, count($admin_areas), true)
		);

		if ($context['user']['is_admin']) {
			$admin_areas['lp_portal']['areas']['lp_blocks']['subsections'] += array(
				'export' => array($txt['lp_blocks_export']),
				'import' => array($txt['lp_blocks_import'])
			);

			$admin_areas['lp_portal']['areas']['lp_pages']['subsections'] += array(
				'export' => array($txt['lp_pages_export']),
				'import' => array($txt['lp_pages_import'])
			);
		}
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
	public static function adminSearch(array &$language_files, array &$include_files, array &$settings_search)
	{
		$settings_search[] = array(__CLASS__ . '::basic', 'area=lp_settings;sa=basic');
		$settings_search[] = array(__CLASS__ . '::extra', 'area=lp_settings;sa=extra');
		$settings_search[] = array(__CLASS__ . '::panels', 'area=lp_settings;sa=panels');
	}

	/**
	 * List of tabs with settings
	 *
	 * Список вкладок с настройками
	 *
	 * @return void
	 */
	public static function settingArea()
	{
		global $context, $txt, $smcFunc;

		isAllowedTo('admin_forum');

		$subActions = array(
			'basic'   => 'Settings::basic',
			'extra'   => 'Settings::extra',
			'panels'  => 'Settings::panels',
			'plugins' => 'Settings::plugins'
		);

		db_extend();

		// Tabs
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => LP_NAME,
			'tabs' => array(
				'basic' => array(
					'description' => sprintf($txt['lp_base_info'], LP_VERSION, phpversion(), $smcFunc['db_title'], $smcFunc['db_get_version']())
				),
				'extra' => array(
					'description' => $txt['lp_extra_info']
				),
				'panels' => array(
					'description' => sprintf($txt['lp_panels_info'], LP_NAME, 'https://evgenyrodionov.github.io/flexboxgrid2/')
				),
				'plugins' => array(
					'description' => sprintf($txt['lp_plugins_info'], 'https://github.com/dragomano/Light-Portal/wiki/How-to-create-an-addon')
				)
			)
		);

		self::loadGeneralSettingParameters($subActions, 'basic');
	}

	/**
	 * Output general settings
	 *
	 * Выводим общие настройки
	 *
	 * @param bool $return_config
	 * @return array|void
	 */
	public static function basic(bool $return_config = false)
	{
		global $sourcedir, $context, $txt, $scripturl, $modSettings, $settings, $boardurl;

		require_once($sourcedir . '/ManageServer.php');

		addInlineJavaScript('
		$("p.information").removeClass("information").toggleClass("infobox");', true);

		self::checkNewVersion();

		$context['page_title'] = $context['settings_title'] = $txt['lp_base'];
		$context['post_url']   = $scripturl . '?action=admin;area=lp_settings;sa=basic;save';

		$context['permissions_excluded']['light_portal_manage_blocks']    = [-1, 0];
		$context['permissions_excluded']['light_portal_manage_own_pages'] = [-1, 0];
		$context['permissions_excluded']['light_portal_approve_pages']    = [-1, 0];

		$txt['lp_manage_permissions'] = '<p class="errorbox">' . $txt['lp_manage_permissions'] . '</p>';

		// Initial settings | Первоначальные настройки
		$add_settings = [];
		if (!isset($modSettings['lp_frontpage_title']))
			$add_settings['lp_frontpage_title'] = $context['forum_name'];
		if (!isset($modSettings['lp_frontpage_alias']))
			$add_settings['lp_frontpage_alias'] = 'home';
		if (!isset($modSettings['lp_teaser_size']))
			$add_settings['lp_teaser_size'] = 255;
		if (!isset($modSettings['lp_num_items_per_page']))
			$add_settings['lp_num_items_per_page'] = 10;
		if (!isset($modSettings['lp_num_comments_per_page']))
			$add_settings['lp_num_comments_per_page'] = 12;
		if (!isset($modSettings['lp_cache_update_interval']))
			$add_settings['lp_cache_update_interval'] = 3600;
		if (!empty($add_settings))
			updateSettings($add_settings);

		$config_vars = array(
			array('select', 'lp_frontpage_mode', $txt['lp_frontpage_mode_set']),
			array('text', 'lp_frontpage_title', '80" placeholder="' . $context['forum_name'] . ' - ' . $txt['lp_portal']),
			array('text', 'lp_frontpage_alias', 80, 'subtext' => $txt['lp_frontpage_alias_subtext']),
			array('boards', 'lp_frontpage_boards'),
			array('check', 'lp_show_images_in_articles'),
			array('text', 'lp_image_placeholder', '80" placeholder="' . $txt['lp_example'] . $settings['default_images_url'] . '/smflogo.svg'),
			array('check', 'lp_frontpage_card_alt_layout'),
			array('check', 'lp_frontpage_order_by_num_replies'),
			array('select', 'lp_frontpage_article_sorting', $txt['lp_frontpage_article_sorting_set']),
			array('select', 'lp_frontpage_layout', $txt['lp_frontpage_layout_set']),
			//array('int', 'lp_teaser_size', 'min' => 0),
			array('int', 'lp_num_items_per_page'),
			array('title', 'lp_standalone_mode_title'),
			array('check', 'lp_standalone_mode'),
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
			array('desc', 'lp_manage_permissions'),
			array('permissions', 'light_portal_view', 'help' => 'permissionhelp_light_portal_view'),
			array('permissions', 'light_portal_manage_blocks', 'help' => 'permissionhelp_light_portal_manage_blocks'),
			array('permissions', 'light_portal_manage_own_pages', 'help' => 'permissionhelp_light_portal_manage_own_pages'),
			array('permissions', 'light_portal_approve_pages', 'help' => 'permissionhelp_light_portal_approve_pages'),
			array('title', 'lp_debug_and_caching'),
			array('check', 'lp_show_debug_info'),
			array('int', 'lp_cache_update_interval', 'postinput' => $txt['seconds'])
		);

		if ($return_config)
			return $config_vars;

		// Frontpage mode toggle
		$frontpage_mode_toggle = array('lp_frontpage_title', 'lp_frontpage_alias', 'lp_frontpage_boards', 'lp_show_images_in_articles', 'lp_image_placeholder', 'lp_frontpage_card_alt_layout', 'lp_frontpage_order_by_num_replies', 'lp_frontpage_layout', 'lp_num_items_per_page');

		$frontpage_mode_toggle_dt = [];
		foreach ($frontpage_mode_toggle as $item)
			$frontpage_mode_toggle_dt[] = 'setting_' . $item;

		$frontpage_alias_toggle = array('lp_frontpage_title', 'lp_frontpage_boards', 'lp_show_images_in_articles', 'lp_image_placeholder', 'lp_frontpage_card_alt_layout', 'lp_frontpage_order_by_num_replies', 'lp_frontpage_layout', 'lp_num_items_per_page');

		$frontpage_alias_toggle_dt = [];
		foreach ($frontpage_alias_toggle as $item)
			$frontpage_alias_toggle_dt[] = 'setting_' . $item;

		addInlineJavaScript('
		function toggleFrontpageMode() {
			let change_mode = $("#lp_frontpage_mode").val() > 0;
			$("#' . implode(', #', $frontpage_mode_toggle) . '").closest("dd").toggle(change_mode);
			$("#' . implode(', #', $frontpage_mode_toggle_dt) . '").closest("dt").toggle(change_mode);
			$(".board_selector").toggle(change_mode);
			let allow_change_title = $("#lp_frontpage_mode").val() > 1;
			$("#' . implode(', #', $frontpage_alias_toggle) . '").closest("dd").toggle(allow_change_title);
			$("#' . implode(', #', $frontpage_alias_toggle_dt) . '").closest("dt").toggle(allow_change_title);
			$(".board_selector").toggle(allow_change_title);
			let allow_change_alias = $("#lp_frontpage_mode").val() == 1;
			$("#lp_frontpage_alias").closest("dd").toggle(allow_change_alias);
			$("#setting_lp_frontpage_alias").closest("dt").toggle(allow_change_alias);
		};
		toggleFrontpageMode();
		$("#lp_frontpage_mode").on("click", function () {
			toggleFrontpageMode()
		});', true);

		// Standalone mode toggle
		$standalone_mode_toggle = array('lp_standalone_url', 'lp_standalone_mode_disabled_actions');

		$standalone_mode_toggle_dt = [];
		foreach ($standalone_mode_toggle as $item)
			$standalone_mode_toggle_dt[] = 'setting_' . $item;

		addInlineJavaScript('
		function toggleStandaloneMode() {
			let change_mode = $("#lp_standalone_mode").prop("checked");
			$("#' . implode(', #', $standalone_mode_toggle) . '").closest("dd").toggle(change_mode);
			$("#' . implode(', #', $standalone_mode_toggle_dt) . '").closest("dt").toggle(change_mode);
		};
		toggleStandaloneMode();
		$("#lp_standalone_mode").on("click", function () {
			toggleStandaloneMode()
		});', true);

		// Save
		if (isset($_GET['save'])) {
			checkSession();

			if (empty($_POST['lp_frontpage_mode']))
				$_POST['lp_standalone_url'] = 0;

			if (!empty($_POST['lp_image_placeholder']))
				$_POST['lp_image_placeholder'] = filter_var($_POST['lp_image_placeholder'], FILTER_VALIDATE_URL);

			if (!empty($_POST['lp_standalone_url']))
				$_POST['lp_standalone_url'] = filter_var($_POST['lp_standalone_url'], FILTER_VALIDATE_URL);

			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			clean_cache();
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
	public static function extra(bool $return_config = false)
	{
		global $context, $txt, $scripturl, $modSettings;

		$context['page_title'] = $context['settings_title'] = $txt['lp_extra'];
		$context['post_url']   = $scripturl . '?action=admin;area=lp_settings;sa=extra;save';

		$modSettings['bbc_disabled_lp_disabled_bbc_in_comments'] = empty($modSettings['lp_disabled_bbc_in_comments']) ? [] : explode(',', $modSettings['lp_disabled_bbc_in_comments']);

		$config_vars = array(
			array('check', 'lp_show_tags_on_page'),
			array('check', 'lp_show_tags_as_articles'),
			array('check', 'lp_show_related_pages'),
			array('select', 'lp_show_comment_block', $txt['lp_show_comment_block_set']),
			array('bbc', 'lp_disabled_bbc_in_comments'),
			array('int', 'lp_num_comments_per_page'),
			array('select', 'lp_page_editor_type_default', $txt['lp_page_types']),
			array('check', 'lp_hide_blocks_in_admin_section'),
			array('title', 'lp_open_graph'),
			array('select', 'lp_page_og_image', $txt['lp_page_og_image_set']),
			array('text', 'lp_page_itemprop_address', 80),
			array('text', 'lp_page_itemprop_phone', 80)
		);

		if ($return_config)
			return $config_vars;

		// Show comment block toggle
		$show_comment_block_toggle = array('lp_disabled_bbc_in_comments', 'lp_num_comments_per_page');

		$show_comment_block_toggle_dt = [];
		foreach ($show_comment_block_toggle as $item)
			$show_comment_block_toggle_dt[] = 'setting_' . $item;

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
		if (isset($_GET['save'])) {
			checkSession();

			// Clean up the tags
			$bbcTags = [];
			foreach (parse_bbc(false) as $tag)
				$bbcTags[] = $tag['tag'];

			if (!isset($_POST['lp_disabled_bbc_in_comments_enabledTags']))
				$_POST['lp_disabled_bbc_in_comments_enabledTags'] = [];
			elseif (!is_array($_POST['lp_disabled_bbc_in_comments_enabledTags']))
				$_POST['lp_disabled_bbc_in_comments_enabledTags'] = array($_POST['lp_disabled_bbc_in_comments_enabledTags']);

			$_POST['lp_enabled_bbc_in_comments']  = implode(',', $_POST['lp_disabled_bbc_in_comments_enabledTags']);
			$_POST['lp_disabled_bbc_in_comments'] = implode(',', array_diff($bbcTags, $_POST['lp_disabled_bbc_in_comments_enabledTags']));

			$save_vars = $config_vars;
			$save_vars[] = ['text', 'lp_enabled_bbc_in_comments'];
			saveDBSettings($save_vars);

			clean_cache();
			redirectexit('action=admin;area=lp_settings;sa=extra');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Output panel settings
	 *
	 * Выводим настройки панелей
	 *
	 * @param bool $return_config
	 * @return array|void
	 */
	public static function panels(bool $return_config = false)
	{
		global $sourcedir, $context, $txt, $scripturl, $modSettings;

		loadTemplate('LightPortal/ManageSettings');

		require_once($sourcedir . '/ManageServer.php');

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
		if (!empty($add_settings))
			updateSettings($add_settings);

		$context['lp_panels'] = $txt['lp_block_placement_set'];

		$context['lp_left_right_width_values']    = [2, 3, 4];
		$context['lp_header_footer_width_values'] = [6, 8, 10, 12];

		Subs::runAddons('addPanels');

		$config_vars = array(
			array('check', 'lp_swap_header_footer'),
			array('check', 'lp_swap_left_right'),
			array('check', 'lp_swap_top_bottom'),
			array('callback', 'panel_layout'),
			array('callback', 'panel_direction')
		);

		if ($return_config)
			return $config_vars;

		$context['sub_template'] = 'show_settings';

		if (isset($_GET['save'])) {
			checkSession();

			$_POST['lp_left_panel_width']  = json_encode($_POST['lp_left_panel_width']);
			$_POST['lp_right_panel_width'] = json_encode($_POST['lp_right_panel_width']);
			$_POST['lp_panel_direction']   = json_encode($_POST['lp_panel_direction']);

			$save_vars = $config_vars;
			$save_vars[] = ['int', 'lp_header_panel_width'];
			$save_vars[] = ['text', 'lp_left_panel_width'];
			$save_vars[] = ['text', 'lp_right_panel_width'];
			$save_vars[] = ['int', 'lp_footer_panel_width'];
			$save_vars[] = ['text', 'lp_panel_direction'];
			saveDBSettings($save_vars);

			redirectexit('action=admin;area=lp_settings;sa=panels');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Output the list and settings of plugins
	 *
	 * Выводим список и настройки плагинов
	 *
	 * @return void
	 */
	public static function plugins()
	{
		global $sourcedir, $context, $txt, $scripturl;

		loadTemplate('LightPortal/ManagePlugins');

		require_once($sourcedir . '/ManageServer.php');

		$context['lp_plugins'] = Subs::getAddons();
		asort($context['lp_plugins']);

		$context['page_title'] = $txt['lp_plugins'] . ' (' . count($context['lp_plugins']) . ')';
		$context['post_url']   = $scripturl . '?action=admin;area=lp_settings;sa=plugins;save';

		$config_vars = [];

		// The mod authors can add their own settings | Авторы модов модут добавлять собственные настройки
		Subs::runAddons('addSettings', array(&$config_vars), $context['lp_plugins']);

		$context['all_lp_plugins'] = array_map(function ($item) use ($txt, $context, $config_vars) {
			return [
				'name'       => $item,
				'snake_name' => $snake_name = Helpers::getSnakeName($item),
				'desc'       => $txt['lp_block_types_descriptions'][$snake_name] ?? $txt['lp_' . $snake_name . '_description'] ?? '',
				'status'     => in_array($item, $context['lp_enabled_plugins']) ? 'on' : 'off',
				'types'      => self::getPluginTypes($snake_name),
				'settings'   => self::getPluginSettings($config_vars, $item)
			];
		}, $context['lp_plugins']);

		$context['sub_template'] = 'plugin_settings';

		if (isset($_GET['save'])) {
			checkSession();

			$plugin_options = [];
			foreach ($config_vars as $id => $var) {
				if (isset($_POST[$var[1]])) {
					if ($var[0] == 'check') {
						$plugin_options[$var[1]] = (int) filter_var($_POST[$var[1]], FILTER_VALIDATE_BOOLEAN);
					} elseif ($var[0] == 'int') {
						$plugin_options[$var[1]] = filter_var($_POST[$var[1]], FILTER_VALIDATE_INT);
					} elseif ($var[0] == 'multicheck') {
						$plugin_options[$var[1]] = [];

						foreach ($_POST[$var[1]] as $key => $value) {
							$plugin_options[$var[1]][(int) $key] = (int) filter_var($value, FILTER_VALIDATE_BOOLEAN);
						}

						$plugin_options[$var[1]] = json_encode($plugin_options[$var[1]]);
					} elseif ($var[0] == 'url') {
						$plugin_options[$var[1]] = filter_var($_POST[$var[1]], FILTER_SANITIZE_URL);
					} else {
						$plugin_options[$var[1]] = $_POST[$var[1]];
					}
				}
			}

			if (!empty($plugin_options))
				updateSettings($plugin_options);

			// Additional actions after settings saving | Дополнительные действия после сохранения настроек
			Subs::runAddons('onSettingsSaving');
		}

		// Enable/disable plugins | Включаем/выключаем плагины
		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		if (isset($data['toggle_plugin'])) {
			$plugin_id = (int) $data['toggle_plugin'];

			if (in_array($context['lp_plugins'][$plugin_id], $context['lp_enabled_plugins'])) {
				$key = array_search($context['lp_plugins'][$plugin_id], $context['lp_enabled_plugins']);
				unset($context['lp_enabled_plugins'][$key]);
			} else {
				$context['lp_enabled_plugins'][] = $context['lp_plugins'][$plugin_id];
			}

			updateSettings(array('lp_enabled_plugins' => implode(',', $context['lp_enabled_plugins'])));
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Get all types of the plugin
	 *
	 * Получаем все типы плагина
	 *
	 * @param string $snake_name
	 * @return string
	 */
	private static function getPluginTypes($snake_name)
	{
		global $txt;

		if (empty($snake_name))
			return $txt['not_applicable'];

		$data = $txt['lp_' . $snake_name . '_type'] ?? '';

		if (empty($data))
			return $txt['not_applicable'];

		if (is_array($data)) {
			$all_types = [];
			foreach ($data as $type)
				$all_types[] = $txt['lp_plugins_hooks_types'][$type];

			return implode(' + ', $all_types);
		}

		return $txt['lp_plugins_hooks_types'][$data];
	}

	/**
	 * Undocumented function
	 *
	 * @param array $config_vars
	 * @param string $name
	 * @return array
	 */
	private static function getPluginSettings($config_vars, $name = '')
	{
		if (empty($config_vars))
			return [];

		$settings = [];
		foreach ($config_vars as $var) {
			$plugin_id   = explode('_addon_', substr($var[1], 3))[0];
			$plugin_name = str_replace('_', '', ucwords($plugin_id, '_'));
			if ($plugin_name == $name)
				$settings[] = $var;
		}

		return $settings;
	}

	/**
	 * The list of available areas to control the blocks
	 *
	 * Список доступных областей для управления блоками
	 *
	 * @return void
	 */
	public static function blockArea()
	{
		global $user_info;

		isAllowedTo('light_portal_manage_blocks');

		$subActions = array(
			'main' => 'ManageBlocks::main',
			'add'  => 'ManageBlocks::add',
			'edit' => 'ManageBlocks::edit'
		);

		if ($user_info['is_admin']) {
			$subActions['export'] = 'ManageBlocks::export';
			$subActions['import'] = 'ManageBlocks::import';
		}

		self::loadGeneralSettingParameters($subActions, 'main');
	}

	/**
	 * The list of available fields to control the pages
	 *
	 * Список доступных областей для управления страницами
	 *
	 * @return void
	 */
	public static function pageArea()
	{
		global $user_info;

		isAllowedTo('light_portal_manage_own_pages');

		$subActions = array(
			'main'   => 'ManagePages::main',
			'add'    => 'ManagePages::add',
			'edit'   => 'ManagePages::edit'
		);

		if ($user_info['is_admin']) {
			$subActions['export'] = 'ManagePages::export';
			$subActions['import'] = 'ManagePages::import';
		}

		self::loadGeneralSettingParameters($subActions, 'main');
	}

	/**
	 * Calls the requested subaction if it does exist; otherwise, calls the default action
	 *
	 * Вызывает метод, если он существует; в противном случае вызывается метод по умолчанию
	 *
	 * @param array $subActions
	 * @param string $defaultAction
	 * @return void
	 */
	private static function loadGeneralSettingParameters(array $subActions = [], string $defaultAction = null)
	{
		global $sourcedir, $context;

		require_once($sourcedir . '/ManageServer.php');

		$context['sub_template'] = 'show_settings';

		$defaultAction = $defaultAction ?: key($subActions);

		$subAction = Helpers::request()->has('sa') && isset($subActions[Helpers::request('sa')]) ? Helpers::request('sa') : $defaultAction;

		$context['sub_action'] = $subAction;

		call_helper(__NAMESPACE__ . '\\' . $subActions[$subAction]);
	}

	/**
	 * Check new version status
	 *
	 * Проверяем наличие новой версии
	 *
	 * @return void
	 */
	private static function checkNewVersion()
	{
		global $context, $txt;

		// Check once a week | Проверяем раз в неделю
		if (LP_VERSION < $new_version = Helpers::getFromCache('last_version', 'getLastVersion', __CLASS__, 604800)) {
			$context['settings_insert_above'] = '
			<div class="noticebox">
				' . $txt['lp_new_version_is_available'] . ' (<a class="bbc_link" href="https://custom.simplemachines.org/mods/index.php?mod=4244" target="_blank" rel="noopener">' . $new_version . '</a>)
			</div>';
		}
	}

	/**
	 * Get the number of the last version
	 *
	 * Получаем номер последней версии LP
	 *
	 * @return string
	 */
	public static function getLastVersion()
	{
		if (!extension_loaded('curl'))
			return LP_VERSION;

		$ch = curl_init('https://api.github.com/repos/dragomano/light-portal/releases/latest');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"User-Agent: dragomano"
		]);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
		curl_close($ch);

		if (empty($data))
			return LP_VERSION;

		$data = json_decode($data);

		if (LP_RELEASE_DATE < $data->published_at)
			return str_replace('v', '', $data->tag_name);

		return LP_VERSION;
	}
}
