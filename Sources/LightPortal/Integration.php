<?php

namespace Bugo\LightPortal;

/**
 * Integration.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.9.2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Integration
{
	/**
	 * Used hooks
	 *
	 * Подключаем используемые хуки
	 *
	 * @return void
	 */
	public static function hooks()
	{
		add_integration_function('integrate_autoload', __CLASS__ . '::autoload', false, __FILE__);
		add_integration_function('integrate_user_info', __CLASS__ . '::userInfo', false, __FILE__);
		add_integration_function('integrate_load_theme', __CLASS__ . '::loadTheme', false, __FILE__);
		add_integration_function('integrate_actions', __CLASS__ . '::actions', false, __FILE__);
		add_integration_function('integrate_default_action', __CLASS__ . '::defaultAction', false, __FILE__);
		add_integration_function('integrate_current_action', __CLASS__ . '::currentAction', false, __FILE__);
		add_integration_function('integrate_admin_areas', __NAMESPACE__ . '\Settings::adminAreas', false, '$sourcedir/LightPortal/Settings.php');
		add_integration_function('integrate_admin_search', __NAMESPACE__ . '\Settings::adminSearch', false, '$sourcedir/LightPortal/Settings.php');
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons', false, __FILE__);
		add_integration_function('integrate_load_illegal_guest_permissions', __CLASS__ . '::loadIllegalGuestPermissions', false, __FILE__);
		add_integration_function('integrate_load_permissions', __CLASS__ . '::loadPermissions', false, __FILE__);
		add_integration_function('integrate_change_member_data', __CLASS__ . '::changeMemberData', false, __FILE__);
		add_integration_function('integrate_credits', __CLASS__ . '::credits', false, __FILE__);
		add_integration_function('integrate_whos_online', __CLASS__ . '::whosOnline', false, __FILE__);
	}

	/**
	 * Setup for autoloading of used classes
	 *
	 * Настраиваем поиск файлов используемых классов для автоподключения
	 *
	 * @param array $classMap
	 * @return void
	 */
	public static function autoload(&$classMap)
	{
		$classMap['Bugo\\LightPortal\\'] = 'LightPortal/';
		$classMap['Bugo\\LightPortal\\Addons\\'] = 'LightPortal/addons/';
	}

	/**
	 * Determine used constants
	 *
	 * Определяем необходимые константы
	 *
	 * @return void
	 */
	public static function userInfo()
	{
		global $sourcedir;

		$lp_constants = [
			'LP_VERSION' => '0.9.2',
			'LP_NAME'    => 'Light Portal',
			'LP_ADDONS'  => $sourcedir . '/LightPortal/addons'
		];

		foreach ($lp_constants as $key => $value)
			defined($key) or define($key, $value);
	}

	/**
	 * Load the mod languages, addons, blocks & styles
	 *
	 * Подключаем языковой файл, скрипты и стили, используемые модом
	 *
	 * @return void
	 */
	public static function loadTheme()
	{
		global $txt;

		if (!defined('LP_NAME'))
			return;

		loadLanguage('LightPortal/');

		Subs::runAddons();
		Subs::loadBlocks();
		Subs::loadCssFiles();
	}

	/**
	 * Add "action=portal"
	 *
	 * Подключаем action «portal»
	 *
	 * @param array $actions
	 * @return void
	 */
	public static function actions(&$actions)
	{
		global $modSettings, $context;

		if (!empty($modSettings['lp_frontpage_disable']))
			return;

		// Fix for Pretty URLs
		if (!empty($context['pretty']['action_array'])) {
			if (!in_array('portal', array_values($context['pretty']['action_array'])))
				$context['pretty']['action_array'][] = 'portal';
		}

		$actions['portal'] = array('LightPortal/Page.php', array(__NAMESPACE__ . '\Page', 'show'));
		$actions['forum']  = array('BoardIndex.php', 'BoardIndex');

		if (!empty($modSettings['lp_standalone'])) {
			Subs::unsetUnusedActions($actions);

			if (empty($actions[$_REQUEST['action']]) || !empty($context['current_board']))
				redirectexit();
		}
	}

	/**
	 * Access the page or call the default method
	 *
	 * Обращаемся к странице или вызываем метод по умолчанию
	 *
	 * @return void
	 */
	public static function defaultAction()
	{
		global $modSettings, $sourcedir;

		if (!empty($_GET['page'])) {
			$alias = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
			return Page::show($alias);
		}

		if (empty($modSettings['lp_frontpage_disable']))
			return Page::show();

		require_once($sourcedir . '/BoardIndex.php');
		$action = 'BoardIndex';
		return $action();
	}

	/**
	 * Add a selection of the "Forum" menu item  when viewing boards and topics
	 *
	 * Добавляем выделение кнопки «Форум» при просмотре разделов и тем
	 *
	 * @param string $current_action
	 * @return void
	 */
	public static function currentAction(&$current_action)
	{
		global $modSettings, $context;

		if (!empty($modSettings['lp_frontpage_disable']))
			return;

		if (empty($_REQUEST['action']))
			$current_action = !empty($modSettings['lp_standalone']) ? 'home' : 'portal';

		if (!empty($context['current_board']) || !empty($context['current_topic']))
			$current_action = !empty($modSettings['lp_standalone']) ? 'forum' : 'home';
	}

	/**
	 * Manage the display of items in the main menu
	 *
	 * Управляем отображением пунктов в главном меню
	 *
	 * @param array $buttons
	 * @return void
	 */
	public static function menuButtons(&$buttons)
	{
		global $context, $txt, $scripturl, $modSettings;

		if (!defined('LP_NAME'))
			return;

		$context['allow_light_portal_manage_blocks']    = allowedTo('light_portal_manage_blocks');
		$context['allow_light_portal_manage_own_pages'] = allowedTo('light_portal_manage_own_pages');

		// Display "Portal settings" in Main Menu => Admin
		// Отображение пункта "Настройки портала"
		if ($context['allow_light_portal_manage_blocks'] || $context['allow_light_portal_manage_own_pages']) {
			$buttons['admin']['show'] = true;
			$counter = 0;
			foreach ($buttons['admin']['sub_buttons'] as $area => $dummy) {
				$counter++;
				if ($area == 'featuresettings')
					break;
			}

			$buttons['admin']['sub_buttons'] = array_merge(
				array_slice($buttons['admin']['sub_buttons'], 0, $counter, true),
				allowedTo('admin_forum') ? array(
					'portal_settings' => array(
						'title' => $txt['lp_settings'],
						'href'  => $scripturl . '?action=admin;area=lp_settings',
						'show'  => true,
						'sub_buttons' => array(
							'blocks' => array(
								'title' => $txt['lp_blocks'],
								'href'  => $scripturl . '?action=admin;area=lp_blocks',
								'amt'   => count($context['lp_active_blocks']),
								'show'  => true
							),
							'pages' => array(
								'title'   => $txt['lp_pages'],
								'href'    => $scripturl . '?action=admin;area=lp_pages',
								'amt'     => $context['lp_active_pages_num'],
								'show'    => true,
								'is_last' => true
							)
						)
					)
				) : array(
					'portal_blocks' => array(
						'title' => $txt['lp_blocks'],
						'href'  => $scripturl . '?action=admin;area=lp_blocks',
						'amt'   => count($context['lp_active_blocks']),
						'show'  => $context['allow_light_portal_manage_blocks']
					),
					'portal_pages' => array(
						'title'   => $txt['lp_pages'],
						'href'    => $scripturl . '?action=admin;area=lp_pages',
						'amt'     => $context['lp_active_pages_num'],
						'show'    => $context['allow_light_portal_manage_own_pages']
					)
				),
				array_slice($buttons['admin']['sub_buttons'], $counter, null, true)
			);
		}

		if (!empty($context['current_action']))
			Block::display($context['current_action']);
		else if (!empty($_REQUEST['board']) || !empty($_REQUEST['topic']) || (!empty($modSettings['lp_frontpage_disable']) && empty($context['current_action']) && empty($_GET['page'])))
			Block::display('forum');

		if (!empty($modSettings['lp_frontpage_disable']))
			return;

		// Display "Portal" item in Main Menu
		// Отображение пункта "Портал"
		$buttons = array_merge(
			array_slice($buttons, 0, 0, true),
			array(
				'portal' => array(
					'title'       => $txt['lp_portal'],
					'href'        => $scripturl,
					'icon'        => 'home',
					'show'        => true,
					'action_hook' => true,
					'is_last'     => $context['right_to_left']
				)
			),
			array_slice($buttons, 0, null, true)
		);

		// "Forum"
		$buttons['home']['title']   = $txt['lp_forum'];
		$buttons['home']['href']    = $scripturl . '?action=forum';
		$buttons['home']['icon']    = 'im_on';
		$buttons['home']['is_last'] = false;

		// Standalone mode
		// Автономный режим
		if (!empty($modSettings['lp_standalone'])) {
			$buttons['home']['title']   = $txt['lp_portal'];
			$buttons['home']['href']    = $scripturl;
			$buttons['home']['icon']    = 'home';
			$buttons['home']['is_last'] = $context['right_to_left'];

			$buttons = array_merge(
				array_slice($buttons, 0, 2, true),
				array(
					'forum' => array(
						'title'       => $txt['lp_forum'],
						'href'        => $scripturl . '?action=forum',
						'icon'        => 'im_on',
						'show'        => true,
						'action_hook' => true
					)
				),
				array_slice($buttons, 2, null, true)
			);

			Subs::unsetUnusedActions($buttons);
		}

		// Correct canonical urls
		// Правильные канонические адреса
		if ($context['current_action'] == 'portal' || (empty($context['current_action']) && empty($_REQUEST['page'])))
			$context['canonical_url'] = $scripturl;
		if ($context['current_action'] == 'forum')
			$context['canonical_url'] = $scripturl . '?action=forum';
	}

	/**
	 * Guests cannot to manage the portal!
	 *
	 * Гости могут только просматривать портал
	 *
	 * @return void
	 */
	public static function loadIllegalGuestPermissions()
	{
		global $context;

		$context['non_guest_permissions'] = array_merge(
			$context['non_guest_permissions'],
			array(
				'light_portal_manage_blocks',
				'light_portal_manage_own_pages'
			)
		);
	}

	/**
	 * Determine permissions
	 *
	 * Определяем права доступа
	 *
	 * @param array $permissionGroups
	 * @param array $permissionList
	 * @param array $leftPermissionGroups
	 * @return void
	 */
	public static function loadPermissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups)
	{
		$permissionList['membergroup']['light_portal_view']             = array(false, 'light_portal');
		$permissionList['membergroup']['light_portal_manage_blocks']    = array(false, 'light_portal');
		$permissionList['membergroup']['light_portal_manage_own_pages'] = array(false, 'light_portal');

		$leftPermissionGroups[] = 'light_portal';
	}

	/**
	 * We reset the cache when changing user data (for example, when a user is changing the current language)
	 *
	 * Сбрасываем кэш при изменении пользовательских данных (например, текущего языка)
	 *
	 * @return void
	 */
	public static function changeMemberData()
	{
		clean_cache();
	}

	/**
	 * The mod credits for action=credits
	 *
	 * Отображаем копирайты на странице action=credits
	 *
	 * @return void
	 */
	public static function credits()
	{
		global $context, $txt;

		$context['credits_modifications'][] = Subs::getCredits();

		if (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'light_portal') {
			Subs::getComponentCredits();

			loadTemplate('LightPortal/ViewPage');

			$context['sub_template']   = 'portal_credits';
			$context['robot_no_index'] = true;
			$context['page_title']     = LP_NAME . ' - ' . $txt['lp_credits'];

			obExit();
		}
	}

	/**
	 * Display current actions of members (on portal area)
	 *
	 * Показываем, кто что делает на портале
	 *
	 * @param array $actions
	 * @return string
	 */
	public static function whosOnline($actions)
	{
		global $modSettings, $txt, $scripturl;

		$result = '';
		if (empty($modSettings['lp_frontpage_disable'])) {
			if (empty($actions['action']))
				$result = sprintf($txt['lp_who_viewing_frontpage'], $scripturl);

			if (!empty($actions['action']) && $actions['action'] == 'portal')
				$result = sprintf($txt['lp_who_viewing_frontpage'], $scripturl . '?action=portal');
		}

		if (!empty($actions['page']))
			$result = sprintf($txt['lp_who_viewing_page'], $scripturl . '?page=' . $actions['page']);

		return $result;
	}
}
