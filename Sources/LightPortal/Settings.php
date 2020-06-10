<?php

namespace Bugo\LightPortal;

/**
 * Settings.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
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

		if (allowedTo('admin_forum')) {
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
		isAllowedTo('admin_forum');

		$subActions = array(
			'basic'   => 'Settings::basic',
			'extra'   => 'Settings::extra',
			'panels'  => 'Settings::panels',
			'plugins' => 'Settings::plugins'
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
		global $sourcedir, $context, $txt, $smcFunc, $scripturl, $modSettings, $settings, $boardurl;

		require_once($sourcedir . '/ManageServer.php');
		db_extend();

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => sprintf($txt['lp_base_info'], LP_VERSION, phpversion(), $smcFunc['db_title'], $smcFunc['db_get_version']())
		);

		self::checkNewVersion();

		$context['page_title'] = $context['settings_title'] = $txt['lp_base'];
		$context['post_url']   = $scripturl . '?action=admin;area=lp_settings;sa=basic;save';

		$context['lp_frontpage_layout'] = FrontPage::getNumColumns();

		$context['permissions_excluded']['light_portal_manage_blocks']    = [-1, 0];
		$context['permissions_excluded']['light_portal_manage_own_pages'] = [-1, 0];

		$txt['lp_manage_permissions'] = '<p class="errorbox">' . $txt['lp_manage_permissions'] . '</p>';

		// Initial settings | Первоначальные настройки
		$add_settings = [];
		if (!isset($modSettings['lp_frontpage_title']))
			$add_settings['lp_frontpage_title'] = $context['forum_name'];
		if (!isset($modSettings['lp_frontpage_id']))
			$add_settings['lp_frontpage_id'] = 1;
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

		$frontpage_disabled = empty($modSettings['lp_frontpage_mode']);

		$active_pages = !$frontpage_disabled && $modSettings['lp_frontpage_mode'] == 1 ? self::getActivePages() : array($txt['no']);

		$config_vars = array(
			array(
				'text',
				'lp_frontpage_title',
				'80" placeholder="' . $context['forum_name'] . ' - ' . $txt['lp_portal'],
				'disabled' => $frontpage_disabled || (!$frontpage_disabled && $modSettings['lp_frontpage_mode'] == 1)
			),
			array('select', 'lp_frontpage_mode', $txt['lp_frontpage_mode_set']),
			array('select', 'lp_frontpage_id', $active_pages, 'disabled' => $frontpage_disabled || $modSettings['lp_frontpage_mode'] != 1),
			array('boards', 'lp_frontpage_boards', 'disabled' => $frontpage_disabled),
			array('check', 'lp_show_images_in_articles', 'disabled' => $frontpage_disabled),
			array('text', 'lp_image_placeholder', '80" placeholder="' . $txt['lp_example'] . $settings['default_images_url'] . '/smflogo.svg', 'disabled' => $frontpage_disabled),
			array('int', 'lp_teaser_size', 'min' => 0, 'disabled' => $frontpage_disabled),
			array('select', 'lp_frontpage_layout', $txt['lp_frontpage_layout_set'], 'disabled' => $frontpage_disabled),
			array('int', 'lp_num_items_per_page', 'disabled' => $frontpage_disabled),
			array('title', 'lp_standalone_mode_title'),
			array('check', 'lp_standalone_mode', 'disabled' => $frontpage_disabled),
			array(
				'text',
				'lp_standalone_url',
				'80" placeholder="' . $txt['lp_example'] . $boardurl . '/portal.php',
				'help' => 'lp_standalone_url_help',
				'disabled' => empty($modSettings['lp_standalone_mode'])
			),
			array(
				'text',
				'lp_standalone_mode_disabled_actions',
				'80" placeholder="' . $txt['lp_example'] . 'mlist,calendar',
				'subtext' => $txt['lp_standalone_mode_disabled_actions_subtext'],
				'help' => 'lp_standalone_mode_disabled_actions_help',
				'disabled' => empty($modSettings['lp_standalone_mode'])
			),
			array('title', 'edit_permissions'),
			array('desc', 'lp_manage_permissions'),
			array('permissions', 'light_portal_view', 'help' => 'permissionhelp_light_portal_view'),
			array('permissions', 'light_portal_manage_blocks', 'help' => 'permissionhelp_light_portal_manage_blocks'),
			array('permissions', 'light_portal_manage_own_pages', 'help' => 'permissionhelp_light_portal_manage_own_pages'),
			array('title', 'lp_debug_and_caching'),
			array('check', 'lp_show_debug_info'),
			array('int', 'lp_cache_update_interval', 'postinput' => $txt['seconds'])
		);

		if ($return_config)
			return $config_vars;

		$context['sub_template'] = 'show_settings';

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

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_extra_info']
		);

		$context['page_title'] = $context['settings_title'] = $txt['lp_extra'];
		$context['post_url']   = $scripturl . '?action=admin;area=lp_settings;sa=extra;save';

		$modSettings['bbc_disabled_lp_disabled_bbc_in_comments'] = empty($modSettings['lp_disabled_bbc_in_comments']) ? [] : explode(',', $modSettings['lp_disabled_bbc_in_comments']);

		$config_vars = array(
			array('check', 'lp_show_tags_on_page'),
			array('select', 'lp_show_comment_block', $txt['lp_show_comment_block_set']),
			array('bbc', 'lp_disabled_bbc_in_comments'),
			array('int', 'lp_num_comments_per_page', 'disabled' => empty($modSettings['lp_show_comment_block'])),
			array('select', 'lp_page_editor_type_default', $txt['lp_page_types']),
			array('check', 'lp_hide_blocks_in_admin_section'),
			array('title', 'lp_open_graph'),
			array('select', 'lp_page_og_image', $txt['lp_page_og_image_set']),
			array('text', 'lp_page_itemprop_address', 80),
			array('text', 'lp_page_itemprop_phone', 80)
		);

		if ($return_config)
			return $config_vars;

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

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => sprintf($txt['lp_panels_info'], LP_NAME, 'https://evgenyrodionov.github.io/flexboxgrid2/')
		);

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

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => sprintf($txt['lp_plugins_info'], 'https://github.com/dragomano/Light-Portal/wiki/How-to-create-an-addon')
		);

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
				if (isset($_POST[$var[1]]))
					$plugin_options[$var[1]] = $var[0] == 'check' || $var[0] == 'int' ? (int) $_POST[$var[1]] : $_POST[$var[1]];
			}

			if (!empty($plugin_options))
				updateSettings($plugin_options);
		}

		// Enable/disable plugins | Включаем/выключаем плагины
		if (isset($_POST['toggle_plugin'])) {
			$plugin_id = (int) $_POST['toggle_plugin'];
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
		isAllowedTo('light_portal_manage_blocks');

		$subActions = array(
			'main' => 'ManageBlocks::main',
			'add'  => 'ManageBlocks::add',
			'edit' => 'ManageBlocks::edit'
		);

		if (allowedTo('admin_forum')) {
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
		isAllowedTo('light_portal_manage_own_pages');

		$subActions = array(
			'main'   => 'ManagePages::main',
			'add'    => 'ManagePages::add',
			'edit'   => 'ManagePages::edit'
		);

		if (allowedTo('admin_forum')) {
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

		$_REQUEST['sa'] = isset($_REQUEST['sa'], $subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : $defaultAction;
		$context['sub_action'] = $_REQUEST['sa'];

		call_helper(__NAMESPACE__ . '\\' . $subActions[$_REQUEST['sa']]);
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

		// Check every 3 days | Проверяем раз в 3 дня
		if (LP_VERSION < $new_version = Helpers::getFromCache('last_version', 'getLastVersion', __CLASS__, 259200)) {
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
			return $data->tag_name;

		return LP_VERSION;
	}

	/**
	 * Get active pages to set the frontpage
	 *
	 * Получаем список активных страниц, для назначения главной
	 *
	 * @return array
	 */
	private static function getActivePages()
	{
		$pages = Helpers::getFromCache('all_titles', 'getAllTitles', '\Bugo\LightPortal\Subs', LP_CACHE_TIME, 'page');
		if (!empty($pages)) {
			$pages = array_map(function ($page) {
				global $user_info;

				return $page['id'] = $page[$user_info['language']];
			}, $pages);
		}

		return $pages;
	}
}
