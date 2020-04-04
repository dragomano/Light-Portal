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

		// Looking for the "Forum" section..
		// Ищем раздел "Форум"...
		$counter = array_search('layout', array_keys($admin_areas)) + 1;

		// ... and add a "Portal" section from the right
		//... и добавляем справа раздел "Портал"
		$admin_areas = array_merge(
			array_slice($admin_areas, 0, $counter, true),
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
							'subsections' => array()
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
							'amt' => $context['lp_active_pages_num'],
							'permission' => array('admin_forum', 'light_portal_manage_own_pages'),
							'subsections' => array(
								'main'   => array($txt['lp_pages_manage']),
								'add'    => array($txt['lp_pages_add'])
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
		$settings_search[] = array(__CLASS__ . '::settingArea', 'area=lp_settings');
	}

	/**
	 * Output general settings
	 *
	 * Выводим общие настройки
	 *
	 * @param bool $return_config
	 * @return array|void
	 */
	public static function settingArea(bool $return_config = false)
	{
		global $sourcedir, $context, $txt, $scripturl, $modSettings;

		loadTemplate('LightPortal/ManagePages');

		require_once($sourcedir . '/ManageServer.php');

		[$db_engine, $db_version] = self::getDbInfo();

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => sprintf($txt['lp_php_mysql_info'], LP_VERSION, phpversion(), $db_engine, $db_version)
		);

		self::checkNewVersion();

		$context['page_title']     = $txt['lp_settings'];
		$context['settings_title'] = $txt['settings'];
		$context['post_url']       = $scripturl . '?action=admin;area=lp_settings;save';

		$context['permissions_excluded']['light_portal_manage_blocks'] = [-1, 0];
		$context['permissions_excluded']['light_portal_manage_own_pages'] = [-1, 0];

		$txt['lp_manage_permissions'] = '<p class="errorbox permissions">' . $txt['lp_manage_permissions'] . '</p>';

		// Initial settings
		// Устанавливаем первоначальные настройки
		$add_settings = [];
		if (!isset($modSettings['lp_frontpage_title']))
			$add_settings['lp_frontpage_title'] = $context['forum_name'];
		if (!isset($modSettings['lp_frontpage_id']))
			$add_settings['lp_frontpage_id'] = 1;
		if (!isset($modSettings['lp_frontpage_layout']))
			$add_settings['lp_frontpage_layout'] = 2;
		if (!isset($modSettings['lp_subject_size']))
			$add_settings['lp_subject_size'] = 22;
		if (!isset($modSettings['lp_teaser_size']))
			$add_settings['lp_teaser_size'] = 100;
		if (!isset($modSettings['lp_num_items_per_page']))
			$add_settings['lp_num_items_per_page'] = 10;
		if (!isset($modSettings['lp_standalone_excluded_actions']))
			$add_settings['lp_standalone_excluded_actions'] = 'forum,admin,profile,pm,signup,logout';
		if (!isset($modSettings['lp_num_comments_per_page']))
			$add_settings['lp_num_comments_per_page'] = 10;
		if (!empty($add_settings))
			updateSettings($add_settings);

		$frontpage_disabled = empty($modSettings['lp_frontpage_mode']);

		$active_pages = self::getActivePages();

		// The mod authors can easily add their own settings
		// Авторы модов модут легко добавлять собственные настройки
		$extra_settings = [];
		Subs::runAddons('addSettings', array(&$extra_settings));

		if (!empty($extra_settings))
			$extra_settings = array_merge(array(array('title', 'lp_extra_settings')), $extra_settings);

		$config_vars = array_merge(
			array(
				array('string', 'lp_frontpage_title', 'disabled' => $frontpage_disabled || (!$frontpage_disabled && $modSettings['lp_frontpage_mode'] == 1)),
				array('select', 'lp_frontpage_mode', $txt['lp_frontpage_mode_set']),
				array('select', 'lp_frontpage_id', $active_pages, 'disabled' => $frontpage_disabled || $modSettings['lp_frontpage_mode'] != 1),
				array('boards', 'lp_frontpage_boards', 'disabled' => $frontpage_disabled),
				array('select', 'lp_frontpage_layout', $txt['lp_frontpage_layout_set'], 'disabled' => $frontpage_disabled),
				array('check', 'lp_show_images_in_articles', 'disabled' => $frontpage_disabled),
				array('int', 'lp_subject_size', 'min' => 0, 'disabled' => $frontpage_disabled),
				array('int', 'lp_teaser_size', 'min' => 0, 'disabled' => $frontpage_disabled),
				array('int', 'lp_num_items_per_page', 'disabled' => $frontpage_disabled),
				'',
				array('check', 'lp_standalone', 'subtext' => $txt['lp_standalone_help'], 'disabled' => $frontpage_disabled),
				array('text', 'lp_standalone_excluded_actions', 80, 'subtext' => $txt['lp_standalone_excluded_actions_subtext']),
				'',
				array('check', 'lp_show_tags_on_page'),
				array('select', 'lp_show_comment_block', $txt['lp_show_comment_block_set']),
				array('int', 'lp_num_comments_per_page', 'disabled' => empty($modSettings['lp_show_comment_block'])),
				array('select', 'lp_page_editor_type_default', $txt['lp_page_types']),
				array('check', 'lp_hide_blocks_in_admin_section'),
				array('select', 'lp_use_block_icons', $txt['lp_use_block_icons_set'])
			),
			$extra_settings,
			array(
				array('title', 'lp_open_graph'),
				array('select', 'lp_page_og_image', $txt['lp_page_og_image_set']),
				array('text', 'lp_page_itemprop_address', 80),
				array('text', 'lp_page_itemprop_phone', 80),
				array('title', 'edit_permissions'),
				array('desc', 'lp_manage_permissions'),
				array('permissions', 'light_portal_view'),
				array('permissions', 'light_portal_manage_blocks'),
				array('permissions', 'light_portal_manage_own_pages')
			)
		);

		if ($return_config)
			return $config_vars;

		$context['sub_template'] = 'show_settings';

		if (isset($_GET['save'])) {
			checkSession();

			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			clean_cache();
			redirectexit('action=admin;area=lp_settings');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Getting information about the database engine/version
	 *
	 * Получаем информацию о движке и версии базы данных
	 *
	 * @return array
	 */
	private static function getDbInfo()
	{
		global $smcFunc;

		return [$smcFunc['db_title'], $smcFunc['db_server_info']()];
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

		if (str_replace(' ', '', LP_VERSION) < Helpers::useCache('last_version', 'getLastVersion', __CLASS__)) {
			$context['settings_insert_above'] = '
			<div class="noticebox">
				<a href="https://custom.simplemachines.org/mods/index.php?mod=4244" target="_blank" rel="noopener">' . $txt['lp_new_version_is_available'] . '</a>
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
		return str_replace('v', '', $data->tag_name);
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
		global $txt;

		$pages = FrontPage::getActivePages();
		if (!empty($pages)) {
			$pages = array_map(function ($page) {
				return $page['id'] = Helpers::getPublicTitle($page);
			}, $pages);
			$pages = array_filter($pages);
		} else {
			$pages = array($txt['no']);
		}

		return $pages;
	}
}
