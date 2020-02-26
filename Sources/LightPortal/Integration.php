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
 * @version 1.0
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
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons', false, __FILE__);
		add_integration_function('integrate_load_illegal_guest_permissions', __CLASS__ . '::loadIllegalGuestPermissions', false, __FILE__);
		add_integration_function('integrate_load_permissions', __CLASS__ . '::loadPermissions', false, __FILE__);
		add_integration_function('integrate_alert_types',  __CLASS__ . '::alertTypes', false, __FILE__);
		add_integration_function('integrate_fetch_alerts',  __CLASS__ . '::fetchAlerts', false, __FILE__);
		add_integration_function('integrate_whos_online', __CLASS__ . '::whosOnline', false, __FILE__);
		add_integration_function('integrate_credits', __NAMESPACE__ . '\Credits::show', false, '$sourcedir/LightPortal/Credits.php');
		add_integration_function('integrate_admin_areas', __NAMESPACE__ . '\Settings::adminAreas', false, '$sourcedir/LightPortal/Settings.php');
		add_integration_function('integrate_admin_search', __NAMESPACE__ . '\Settings::adminSearch', false, '$sourcedir/LightPortal/Settings.php');
	}

	/**
	 * Setup for autoloading of used classes
	 *
	 * Настраиваем поиск файлов используемых классов для автоподключения
	 *
	 * @param array $classMap
	 * @return void
	 */
	public static function autoload(array &$classMap)
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
		global $user_info, $sourcedir;

		Debug::start();

		$lp_constants = [
			'LP_VERSION' => '1.0 rc1',
			'LP_NAME'    => 'Light Portal',
			'LP_DEBUG'   => $user_info['is_admin'],
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
		global $context, $txt;

		if (!defined('LP_NAME') || !empty($context['uninstalling']))
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
	public static function actions(array &$actions)
	{
		global $context, $modSettings;

		$actions['portal'] = array('LightPortal/Page.php', array(__NAMESPACE__ . '\Page', 'show'));
		$actions['forum']  = array('BoardIndex.php', 'BoardIndex');

		if (!empty($context['current_action']) && $context['current_action'] == 'portal' && $context['current_subaction'] == 'tags')
			Tag::show();

		if (!empty($modSettings['lp_standalone'])) {
			Subs::unsetUnusedActions($actions);

			if (empty($actions[$_REQUEST['action']]))
				redirectexit();
		}
	}

	/**
	 * Access the portal page or call the default method
	 *
	 * Обращаемся к странице портала или вызываем метод по умолчанию
	 *
	 * @return void
	 */
	public static function defaultAction()
	{
		global $modSettings, $sourcedir;

		if (!empty($_GET['page']))
			return Page::show((string) $_GET['page']);

		if (!empty($modSettings['lp_frontpage_mode']))
			return FrontPage::show();

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
	public static function currentAction(string &$current_action)
	{
		global $modSettings, $context;

		if (empty($modSettings['lp_frontpage_mode']))
			return;

		if (empty($_REQUEST['action']))
			$current_action = 'portal';

		$excluded_actions = !empty($modSettings['lp_standalone_excluded_actions']) ? explode(',', $modSettings['lp_standalone_excluded_actions']) : [];

		if (!empty($context['current_board']) || !empty($context['current_topic']))
			$current_action = !empty($modSettings['lp_standalone']) ? (in_array('forum', $excluded_actions) ? 'forum' : 'portal') : 'home';
	}

	/**
	 * Manage the display of items in the main menu
	 *
	 * Управляем отображением пунктов в главном меню
	 *
	 * @param array $buttons
	 * @return void
	 */
	public static function menuButtons(array &$buttons)
	{
		global $context, $txt, $scripturl, $modSettings;

		if (!defined('LP_NAME') || !empty($context['uninstalling']))
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
						'title' => $txt['lp_pages'],
						'href'  => $scripturl . '?action=admin;area=lp_pages',
						'amt'   => $context['lp_active_pages_num'],
						'show'  => $context['allow_light_portal_manage_own_pages']
					)
				),
				array_slice($buttons['admin']['sub_buttons'], $counter, null, true)
			);
		}

		if (!empty($context['current_action']))
			Block::show($context['current_action']);
		else if (!empty($_REQUEST['board']) || !empty($_REQUEST['topic']) || (empty($modSettings['lp_frontpage_mode']) && empty($context['current_action']) && empty($_GET['page'])))
			Block::show('forum');

		if (empty($modSettings['lp_frontpage_mode']))
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
			$buttons['portal']['title']   = $txt['lp_portal'];
			$buttons['portal']['href']    = $scripturl;
			$buttons['portal']['icon']    = 'home';
			$buttons['portal']['is_last'] = $context['right_to_left'];

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

		if ($context['current_action'] == 'forum')
			$context['canonical_url'] = $scripturl . '?action=forum';

		$context['lp_load_page_stats'] = LP_DEBUG || isset($_GET['debug']) ? sprintf($txt['lp_load_page_stats'], Debug::getScriptExecutionTime(), Debug::getUsageMemory()) : false;
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
	public static function loadPermissions(array &$permissionGroups, array &$permissionList, array &$leftPermissionGroups)
	{
		$permissionList['membergroup']['light_portal_view']             = array(false, 'light_portal');
		$permissionList['membergroup']['light_portal_manage_blocks']    = array(false, 'light_portal');
		$permissionList['membergroup']['light_portal_manage_own_pages'] = array(false, 'light_portal');

		$leftPermissionGroups[] = 'light_portal';
	}

	/**
	 * Adding the "Light Portal" section to the notification settings in user profile
	 *
	 * Добавляем раздел «Light Portal» в настройки уведомлений в профиле
	 *
	 * @param array $alert_types
	 * @return void
	 */
	public static function alertTypes(array &$alert_types)
	{
		global $modSettings;

		if (!empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] == 'default')
			$alert_types['light_portal'] = array(
				'page_comment'       => array('alert' => 'yes', 'email' => 'never', 'permission' => array('name' => 'light_portal_view', 'is_board' => false)),
				'page_comment_reply' => array('alert' => 'yes', 'email' => 'never', 'permission' => array('name' => 'light_portal_view', 'is_board' => false))
			);
	}

	/**
	 * Adding a notification about new comments
	 *
	 * Добавляем оповещение о новых комментариях
	 *
	 * @param array $alerts
	 * @return void
	 */
	public static function fetchAlerts(array &$alerts)
	{
		global $user_info, $memberContext, $txt, $scripturl;

		if (empty($alerts))
			return;

		foreach ($alerts as $id => $alert) {
			if ($alert['content_action'] == 'page_comment' || $alert['content_action'] == 'page_comment_reply') {
				if ($alert['sender_id'] != $user_info['id']) {
					if (!empty($memberContext[$alert['sender_id']]))
						$alerts[$id]['sender'] = &$memberContext[$alert['sender_id']];

					$string = 'alert_' . $alert['content_type'] . '_' . $alert['content_action'];

					if (isset($txt[$string])) {
						$extra   = $alerts[$id]['extra'];
						$search  = array('{member_link}', '{comment_link}', '{comment_title}');
						$replace = array(
							!empty($alert['sender_id']) ? ('<a href="' . $scripturl . '?action=profile;u=' . $alert['sender_id'] . '">' . $alert['sender_name'] . '</a>') : $alert['sender_name'],
							$alert['extra']['content_link'],
							$alert['extra']['content_subject']
						);

						foreach ($extra as $k => $v) {
							$search[]  = '{' . $k . '}';
							$replace[] = $v;
						}

						$alerts[$id]['text'] = str_replace($search, $replace, $txt[$string]);
					}
				} else {
					unset($alerts[$id]);
				}
			}
		}

		if (!empty($user_info['alerts']))
			$_SESSION['lp_update_comments'] = true;
	}

	/**
	 * Display current actions of members (on portal area)
	 *
	 * Показываем, кто что делает на портале
	 *
	 * @param array $actions
	 * @return string
	 */
	public static function whosOnline(array $actions)
	{
		global $txt, $scripturl;

		$result = '';
		if (empty($actions['action']))
			$result = sprintf($txt['lp_who_viewing_frontpage'], $scripturl);

		if (!empty($actions['action']) && $actions['action'] == 'portal') {
			if (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'tags') {
				if (!empty($_REQUEST['key']))
					$result = sprintf($txt['lp_who_viewing_the_tag'], $scripturl . '?action=portal;sa=tags;key=' . $_REQUEST['key'], $_REQUEST['key']);
				else
					$result = sprintf($txt['lp_who_viewing_tags'], $scripturl . '?action=portal;sa=tags');
			} else
				$result = sprintf($txt['lp_who_viewing_frontpage'], $scripturl . '?action=portal');
		}

		if (!empty($actions['page']))
			$result = sprintf($txt['lp_who_viewing_page'], $scripturl . '?page=' . $actions['page']);

		return $result;
	}
}
