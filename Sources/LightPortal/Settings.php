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
 * @version 0.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Settings
{
	/**
	 * Make a section with the mod settings in the admin panel
	 * Формируем раздел с настройками мода в админке
	 *
	 * @param array $admin_areas
	 * @return void
	 */
	public static function adminAreas(&$admin_areas)
	{
		global $sourcedir, $txt;

		require_once($sourcedir . '/ManageSettings.php');

		loadLanguage('ManageSettings');

		// Looking for the "Forum" section... | Ищем раздел "Форум"...
		$counter = array_search('layout', array_keys($admin_areas)) + 1;

		// ... and add a "Portal" section from the right | ... и добавляем справа раздел "Портал"
		$admin_areas = array_merge(
			array_slice($admin_areas, 0, $counter, true),
			array(
				'lp_portal' => array(
					'title' => $txt['lp_portal'],
					'permission' => array('admin_forum', 'moderate_forum', 'light_portal_manage'),
					'areas' => array(
						'lp_settings' => array(
							'label' => $txt['settings'],
							'function' => function () {
								self::settingArea();
							},
							'icon' => 'features',
							'permission' => array('admin_forum', 'light_portal_manage'),
							'subsections' => array()
						),
						'lp_blocks' => array(
							'label' => $txt['lp_blocks'],
							'function' => function () {
								self::blockArea();
							},
							'icon' => 'modifications',
							'permission' => array('admin_forum', 'light_portal_manage'),
							'subsections' => array(
								'main' => array($txt['lp_blocks_manage']),
								'add' => array($txt['lp_blocks_add'])
							)
						),
						'lp_pages' => array(
							'label' => $txt['lp_pages'],
							'function' => function () {
								self::pageArea();
							},
							'icon' => 'posts',
							'permission' => array('admin_forum', 'light_portal_manage'),
							'subsections' => array(
								'main' => array($txt['lp_pages_manage']),
								'add' => array($txt['lp_pages_add'])
							)
						)
					)
				)
			),
			array_slice($admin_areas, $counter, count($admin_areas), true)
		);
	}

	/**
	 * Easy access to the mod settings via a quick search in the admin panel
	 * Легкий доступ к настройкам мода через быстрый поиск в админке
	 *
	 * @param array $language_files
	 * @param array $include_files
	 * @param array $settings_search
	 * @return void
	 */
	public static function adminSearch(&$language_files, &$include_files, &$settings_search)
	{
		$settings_search[] = array(__NAMESPACE__ . '\Settings::settingArea', 'area=lp_settings');
	}

	/**
	 * Output general settings
	 * Выводим общие настройки
	 *
	 * @param boolean $return_config
	 * @return void|array
	 */
	public static function settingArea($return_config = false)
	{
		global $sourcedir, $txt, $scripturl, $db_type, $context, $modSettings;

		isAllowedTo('light_portal_manage');

		require_once($sourcedir . '/ManageServer.php');

		$databases = self::getDatabases();

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => sprintf($txt['lp_php_mysql_info'], LP_VERSION, phpversion(), $databases[$db_type]['name'], eval($databases[$db_type]['version_check']))
		);

		$context['permissions_excluded']['light_portal_manage'] = array(-1);

		$context['page_title']     = $txt['lp_settings'];
		$context['settings_title'] = $txt['settings'];
		$context['post_url']       = $scripturl . '?action=admin;area=lp_settings;save';
		$context['sub_template']   = 'show_settings';

		// Setup initial settings | Устанавливаем первоначальные настройки
		$add_settings = [];
		if (!isset($modSettings['lp_standalone_excluded_actions']))
			$add_settings['lp_standalone_excluded_actions'] = 'home,admin,profile,pm,signup,logout';
		if (!isset($modSettings['lp_num_per_page']))
			$add_settings['lp_num_per_page'] = 10;
		if (!empty($add_settings))
			updateSettings($add_settings);

		Subs::getForumLanguages();

		foreach ($context['languages'] as $lang) {
			$txt['lp_main_page_title_' . $lang['filename']] = $txt['lp_main_page_title'] . ' [<strong>' . $lang['filename'] . '</strong>]';
			$config_vars[] = array('text', 'lp_main_page_title_' . $lang['filename'], 80, 'disabled' => !empty($modSettings['lp_main_page_disable']));
		}

		$config_vars = array_merge(
			$config_vars,
			array(
				array('check', 'lp_main_page_disable', 'disabled' => !empty($modSettings['lp_standalone'])),
				array('check', 'lp_standalone', 'subtext' => $txt['lp_standalone_help'], 'disabled' => !empty($modSettings['lp_main_page_disable'])),
				array('text', 'lp_standalone_excluded_actions', 80, 'subtext' => $txt['lp_standalone_excluded_actions_subtext']),
				array('select', 'lp_page_editor_type_default', $txt['lp_page_types']),
				array('int', 'lp_num_per_page'),
				array('title', 'edit_permissions'),
				array('permissions', 'light_portal_view'),
				array('permissions', 'light_portal_manage')
			)
		);

		if ($return_config)
			return $config_vars;

		if (isset($_GET['save'])) {
			checkSession();
			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			Page::toggleStatus(1, isset($_POST['lp_main_page_disable']) ? 0 : 1);

			clean_cache();
			redirectexit('action=admin;area=lp_settings');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Getting information about the database engine
	 * Получаем информацию о движке базы данных
	 *
	 * @return array
	 */
	private static function getDatabases()
	{
		$databases = array(
			'mysql' => array(
				'name'          => 'MySQL',
				'version_check' =>  'global $db_connection; return mysqli_get_server_info($db_connection);'
			),
			'postgresql' => array(
				'name'          => 'PostgreSQL',
				'version_check' => '$request = pg_query(\'SHOW server_version\'); list ($version) = pg_fetch_row($request); return $version;'
			)
		);

		return $databases;
	}

	/**
	 * The list of available areas to control the blocks
	 * Список доступных областей для управления блоками
	 *
	 * @return void
	 */
	public static function blockArea()
	{
		$subActions = array(
			'main' => 'Block::manage',
			'add'  => 'Block::add',
			'edit' => 'Block::edit'
		);

		loadGeneralSettingParameters($subActions, 'main');

		call_helper(__NAMESPACE__ . '\\' . $subActions[$_REQUEST['sa']]);
	}

	/**
	 * The list of available fields to control the pages
	 * Список доступных областей для управления страницами
	 *
	 * @return void
	 */
	public static function pageArea()
	{
		$subActions = array(
			'main' => 'Page::manage',
			'add'  => 'Page::add',
			'edit' => 'Page::edit'
		);

		loadGeneralSettingParameters($subActions, 'main');

		call_helper(__NAMESPACE__ . '\\' . $subActions[$_REQUEST['sa']]);
	}
}
