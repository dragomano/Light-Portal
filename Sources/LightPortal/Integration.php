<?php

namespace Bugo\LightPortal;

/**
 * Integration.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.5
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Integration
{
	/**
	 * Add used hooks
	 *
	 * Подключаем используемые хуки
	 *
	 * @return void
	 */
	public function hooks()
	{
		add_integration_function('integrate_autoload', __CLASS__ . '::autoload#', false, __FILE__);
		add_integration_function('integrate_user_info', __CLASS__ . '::userInfo#', false, __FILE__);
		add_integration_function('integrate_pre_css_output', __CLASS__ . '::preCssOutput#', false, __FILE__);
		add_integration_function('integrate_load_theme', __CLASS__ . '::loadTheme#', false, __FILE__);
		add_integration_function('integrate_redirect', __CLASS__ . '::redirect#', false, __FILE__);
		add_integration_function('integrate_actions', __CLASS__ . '::actions#', false, __FILE__);
		add_integration_function('integrate_default_action', __CLASS__ . '::defaultAction#', false, __FILE__);
		add_integration_function('integrate_current_action', __CLASS__ . '::currentAction#', false, __FILE__);
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons#', false, __FILE__);
		add_integration_function('integrate_delete_members', __CLASS__ . '::deleteMembers#', false, __FILE__);
		add_integration_function('integrate_load_illegal_guest_permissions', __CLASS__ . '::loadIllegalGuestPermissions#', false, __FILE__);
		add_integration_function('integrate_load_permissions', __CLASS__ . '::loadPermissions#', false, __FILE__);
		add_integration_function('integrate_valid_likes', __CLASS__ . '::validLikes#', false, __FILE__);
		add_integration_function('integrate_issue_like', __CLASS__ . '::issueLike#', false, __FILE__);
		add_integration_function('integrate_alert_types',  __CLASS__ . '::alertTypes#', false, __FILE__);
		add_integration_function('integrate_fetch_alerts',  __CLASS__ . '::fetchAlerts#', false, __FILE__);
		add_integration_function('integrate_pre_profile_areas', __CLASS__ . '::preProfileAreas#', false, __FILE__);
		add_integration_function('integrate_profile_popup', __CLASS__ . '::profilePopup#', false, __FILE__);
		add_integration_function('integrate_whos_online', __CLASS__ . '::whoisOnline#', false, __FILE__);
		add_integration_function('integrate_credits', __NAMESPACE__ . '\Credits::show#', false, '$sourcedir/LightPortal/Credits.php');
		add_integration_function('integrate_admin_areas', __NAMESPACE__ . '\Settings::adminAreas#', false, '$sourcedir/LightPortal/Settings.php');
		add_integration_function('integrate_admin_search', __NAMESPACE__ . '\Settings::adminSearch#', false, '$sourcedir/LightPortal/Settings.php');
	}

	/**
	 * Setup for autoloader of used classes
	 *
	 * Настраиваем автоподключение используемых классов
	 *
	 * @param array $classMap
	 * @return void
	 */
	public function autoload(array &$classMap)
	{
		$classMap['Bugo\\LightPortal\\']         = 'LightPortal/';
		$classMap['Bugo\\LightPortal\\Addons\\'] = 'LightPortal/addons/';
		$classMap['Bugo\\LightPortal\\Front\\']  = 'LightPortal/front/';
		$classMap['Bugo\\LightPortal\\Impex\\']  = 'LightPortal/impex/';
		$classMap['Bugo\\LightPortal\\Utils\\']  = 'LightPortal/utils/';
	}

	/**
	 * Determine used constants
	 *
	 * Определяем необходимые константы
	 *
	 * @return void
	 */
	public function userInfo()
	{
		global $context, $smcFunc, $modSettings, $user_info, $sourcedir;

		$context['lp_load_time']   = $context['lp_load_time'] ?? microtime(true);
		$smcFunc['lp_num_queries'] = $smcFunc['lp_num_queries'] ?? 0;

		$lp_constants = [
			'LP_NAME'         => 'Light Portal',
			'LP_VERSION'      => '1.6 beta',
			'LP_RELEASE_DATE' => '2021-01-30',
			'LP_DEBUG'        => !empty($modSettings['lp_show_debug_info']) && !empty($user_info['is_admin']),
			'LP_CACHE_TIME'   => $modSettings['lp_cache_update_interval'] ?? 3600,
			'LP_ADDON_DIR'    => $sourcedir . '/LightPortal/addons',
			'MAX_MSG_LENGTH'  => 65535
		];

		foreach ($lp_constants as $key => $value)
			defined($key) or define($key, $value);
	}

	/**
	 * Speed up the loading of third-party resources
	 *
	 * Ускоряем загрузку сторонних ресурсов
	 *
	 * @return void
	 */
	public function preCssOutput()
	{
		global $context;

		if (SMF === 'BACKGROUND')
			return;

		echo "\n\t" . '<link rel="preconnect" href="//cdn.jsdelivr.net">';

		if (!empty($context['portal_next_page']))
			echo "\n\t" . '<link rel="prerender" href="', $context['portal_next_page'], '">';
	}

	/**
	 * Load the mod languages, addons, blocks & styles
	 *
	 * Подключаем языковой файл, скрипты и стили, используемые модом
	 *
	 * @return void
	 */
	public function loadTheme()
	{
		global $context, $modSettings;

		if (Subs::isPortalShouldNotBeLoaded())
			return;

		loadLanguage('LightPortal/');

		$context['lp_enabled_plugins'] = empty($modSettings['lp_enabled_plugins']) ? [] : explode(',', $modSettings['lp_enabled_plugins']);

		$context['lp_num_active_pages'] = Helpers::getNumActivePages();

		Subs::loadBlocks();
		Subs::loadCssFiles();
		Subs::runAddons();
	}

	/**
	 * Set up a redirect to the main page of the forum, when requesting an action markasread
	 *
	 * Настраиваем редирект на главную страницу форума, при запросе действия action=markasread
	 *
	 * @param string $setLocation
	 * @return void
	 */
	public function redirect(string &$setLocation)
	{
		global $modSettings, $scripturl;

		if (empty($modSettings['lp_frontpage_mode']) || (!empty($modSettings['lp_standalone_mode']) && !empty($modSettings['lp_standalone_url'])))
			return;

		if (Helpers::request()->is('markasread'))
			$setLocation = $scripturl . '?action=forum';
	}

	/**
	 * Add "action=portal"
	 *
	 * Добавляем action «portal»
	 *
	 * @param array $actions
	 * @return void
	 */
	public function actions(array &$actions)
	{
		global $context, $modSettings;

		$actions['portal'] = array('LightPortal/FrontPage.php', array(new FrontPage, 'show'));
		$actions['forum']  = array('BoardIndex.php', 'BoardIndex');

		if (Helpers::request()->is('portal') && $context['current_subaction'] == 'categories')
			return call_user_func(array(new Category, 'show'));

		if (Helpers::request()->is('portal') && $context['current_subaction'] == 'tags')
			return call_user_func(array(new Tag, 'show'));

		if (!empty($modSettings['lp_standalone_mode'])) {
			$disabled_actions = Subs::unsetDisabledActions($actions);

			if (!empty($context['current_action']) && array_key_exists($context['current_action'], $disabled_actions))
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
	public function defaultAction()
	{
		global $modSettings;

		if (Helpers::request()->filled('page'))
			return call_user_func(array(new Page, 'show'));

		if (empty($modSettings['lp_frontpage_mode']) || (!empty($modSettings['lp_standalone_mode']) && !empty($modSettings['lp_standalone_url']))) {
			Helpers::require('BoardIndex');

			return call_user_func('BoardIndex');
		}

		if (!empty($modSettings['lp_frontpage_mode']))
			return call_user_func(array(new FrontPage, 'show'));
	}

	/**
	 * Add a selection for some menu items when navigating to the specified areas
	 *
	 * Добавляем выделение для некоторых пунктов меню при переходе в указанные области
	 *
	 * @param string $current_action
	 * @return void
	 */
	public function currentAction(string &$current_action)
	{
		global $modSettings, $context;

		if (empty($modSettings['lp_frontpage_mode']))
			return;

		if (Helpers::request()->isEmpty('action')) {
			$current_action = 'portal';

			if (!empty($modSettings['lp_standalone_mode']) && !empty($modSettings['lp_standalone_url']) && $modSettings['lp_standalone_url'] != Helpers::server('REQUEST_URL'))
				$current_action = 'forum';

			if (Helpers::request()->filled('page')) {
				$current_action = 'portal';

				$page = Helpers::request('page');
				if (isset(Subs::getPagesInMenu()[$page]))
					$current_action = 'portal_' . $page;
			}
		} else {
			$current_action = empty($modSettings['lp_standalone_mode']) && Helpers::request()->is('forum') ? 'home' : $context['current_action'];
		}

		$disabled_actions = !empty($modSettings['lp_standalone_mode_disabled_actions']) ? explode(',', $modSettings['lp_standalone_mode_disabled_actions']) : [];
		$disabled_actions[] = 'home';

		if (!empty($context['current_board']) || Helpers::request()->is(['keywords']))
			$current_action = !empty($modSettings['lp_standalone_mode']) ? (!in_array('forum', $disabled_actions) ? 'forum' : 'portal') : 'home';
	}

	/**
	 * Manage the display of items in the main menu
	 *
	 * Управляем отображением пунктов в главном меню
	 *
	 * @param array $buttons
	 * @return void
	 */
	public function menuButtons(array &$buttons)
	{
		global $context, $txt, $scripturl, $modSettings;

		if (Subs::isPortalShouldNotBeLoaded())
			return;

		$context['allow_light_portal_view']             = allowedTo('light_portal_view');
		$context['allow_light_portal_manage_blocks']    = allowedTo('light_portal_manage_blocks');
		$context['allow_light_portal_manage_own_pages'] = allowedTo('light_portal_manage_own_pages');

		(new Block)->show();

		// Display "Portal settings" in Main Menu => Admin
		if ($context['user']['is_admin']) {
			$counter = 0;
			foreach ($buttons['admin']['sub_buttons'] as $area => $dummy) {
				$counter++;

				if ($area == 'featuresettings')
					break;
			}

			$buttons['admin']['sub_buttons'] = array_merge(
				array_slice($buttons['admin']['sub_buttons'], 0, $counter, true),
				array(
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
								'amt'     => $context['lp_num_active_pages'],
								'show'    => true
							),
							'plugins' => array(
								'title'   => $txt['lp_plugins'],
								'href'    => $scripturl . '?action=admin;area=lp_plugins',
								'amt'     => count($context['lp_enabled_plugins']),
								'show'    => true,
								'is_last' => true
							)
						)
					)
				),
				array_slice($buttons['admin']['sub_buttons'], $counter, null, true)
			);
		}

		// Display chosen pages in the main menu
		if (!empty($pages_in_menu = Subs::getPagesInMenu())) {
			$pages = [];
			foreach ($pages_in_menu as $alias => $item) {
				$pages['portal_' . $alias] = array(
					'title' => Helpers::getTitle($item),
					'href'  => $scripturl . '?page=' . $alias,
					'icon'  => empty($item['icon']) ? null : ('" style="display: none"></span><span class="portal_menu_icons ' . $item['icon_type'] . ' fa-' . $item['icon']),
					'show'  => Helpers::canViewItem($item['permissions'])
				);
			}

			$counter = -1;
			foreach ($buttons as $area => $dummy) {
				$counter++;

				if ($area == 'admin')
					break;
			}

			$buttons = array_merge(
				array_slice($buttons, 0, $counter, true),
				$pages,
				array_slice($buttons, $counter, null, true)
			);
		}

		Subs::showDebugInfo();

		if (empty($modSettings['lp_frontpage_mode']))
			return;

		// Display "Portal" item in Main Menu
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
		if (!empty($modSettings['lp_standalone_mode'])) {
			$buttons['portal']['title']   = $txt['lp_portal'];
			$buttons['portal']['href']    = !empty($modSettings['lp_standalone_url']) ? $modSettings['lp_standalone_url'] : $scripturl;
			$buttons['portal']['icon']    = 'home';
			$buttons['portal']['is_last'] = $context['right_to_left'];

			$buttons = array_merge(
				array_slice($buttons, 0, 2, true),
				array(
					'forum' => array(
						'title'       => $txt['lp_forum'],
						'href'        => !empty($modSettings['lp_standalone_url']) ? $scripturl : ($scripturl . '?action=forum'),
						'icon'        => 'im_on',
						'show'        => true,
						'action_hook' => true
					)
				),
				array_slice($buttons, 2, null, true)
			);

			Subs::unsetDisabledActions($buttons);
		}

		// Other fixes
		Subs::fixCanonicalUrl();
		Subs::fixLinktree();
	}

	/**
	 * Remove comments and alerts on deleting members
	 *
	 * Удаляем комментарии и оповещения при удалении пользователей
	 *
	 * @param array $users
	 * @return void
	 */
	public function deleteMembers(array $users)
	{
		global $smcFunc;

		if (empty($users))
			return;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_comments
			WHERE author_id IN ({array_int:users})',
			array(
				'users' => $users
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}user_alerts
			WHERE id_member IN ({array_int:users})
				OR id_member_started IN ({array_int:users})',
			array(
				'users' => $users
			)
		);

		Helpers::cache()->flush();
	}

	/**
	 * Guests cannot to manage the portal!
	 *
	 * Гости могут только просматривать портал
	 *
	 * @return void
	 */
	public function loadIllegalGuestPermissions()
	{
		global $context;

		$context['non_guest_permissions'] = array_merge(
			$context['non_guest_permissions'],
			array(
				//'light_portal_manage_blocks',
				'light_portal_manage_own_pages',
				'light_portal_approve_pages'
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
	public function loadPermissions(array &$permissionGroups, array &$permissionList, array &$leftPermissionGroups)
	{
		global $context;

		//$context['permissions_excluded']['light_portal_manage_blocks'][]    = 0;
		$context['permissions_excluded']['light_portal_manage_own_pages'][] = 0;
		$context['permissions_excluded']['light_portal_approve_pages'][]    = 0;

		$permissionList['membergroup']['light_portal_view']             = array(false, 'light_portal');
		//$permissionList['membergroup']['light_portal_manage_blocks']    = array(false, 'light_portal');
		$permissionList['membergroup']['light_portal_manage_own_pages'] = array(false, 'light_portal');
		$permissionList['membergroup']['light_portal_approve_pages']    = array(false, 'light_portal');

		$leftPermissionGroups[] = 'light_portal';
	}

	/**
	 * Validating data when like/unlike pages
	 *
	 * Валидируем данные при лайке/дизлайке страниц
	 *
	 * @param string $type
	 * @param int $content
	 * @return bool|array
	 */
	public function validLikes(string $type, int $content)
	{
		global $smcFunc, $user_info;

		if ($type !== 'lpp')
			return false;

		$request = $smcFunc['db_query']('', '
			SELECT alias, author_id
			FROM {db_prefix}lp_pages
			WHERE page_id = {int:id}
			LIMIT 1',
			array(
				'id' => $content
			)
		);

		[$alias, $author] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		if (empty($alias))
			return false;

		return [
			'type'        => $type,
			'flush_cache' => 'light_portal_likes_page_' . $content . '_' . $user_info['id'],
			'redirect'    => 'page=' . $alias,
			'can_like'    => $user_info['id'] == $author ? 'cannot_like_content' : (allowedTo('likes_like') ? true : 'cannot_like_content')
		];
	}

	/**
	 * Update cache on like/unlike pages
	 *
	 * Обновляем кэш при лайке/дизлайке страниц
	 *
	 * @param \Likes $obj
	 * @return void
	 */
	public function issueLike(\Likes $obj)
	{
		if ($obj->get('type') !== 'lpp')
			return;

		Helpers::cache()->put('likes_page_' . $obj->get('content') . '_count', $obj->get('numLikes'));
	}

	/**
	 * Adding the "Light Portal" section to the notification settings in user profile
	 *
	 * Добавляем раздел «Light Portal» в настройки уведомлений в профиле
	 *
	 * @param array $alert_types
	 * @return void
	 */
	public function alertTypes(array &$alert_types)
	{
		global $modSettings;

		if (!empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] == 'default')
			$alert_types['light_portal'] = array(
				'page_comment'       => array('alert' => 'yes', 'email' => 'never', 'permission' => array('name' => 'light_portal_manage_own_pages', 'is_board' => false)),
				'page_comment_reply' => array('alert' => 'yes', 'email' => 'never', 'permission' => array('name' => 'light_portal_view', 'is_board' => false))
			);
	}

	/**
	 * Adding a notification about new comments
	 *
	 * Добавляем оповещение о новых комментариях
	 *
	 * @param array $alerts
	 * @param array $formats
	 * @return void
	 */
	public function fetchAlerts(array &$alerts, array &$formats)
	{
		global $user_info;

		if (empty($alerts))
			return;

		foreach ($alerts as $id => $alert) {
			if ($alert['content_action'] == 'page_comment' || $alert['content_action'] == 'page_comment_reply') {
				if ($alert['sender_id'] != $user_info['id']) {
					$alerts[$id]['icon'] = '<span class="alert_icon main_icons ' . ($alert['content_action'] == 'page_comment' ? 'im_off' : 'im_on') . '"></span>';

					$formats['page_comment_new_comment'] = array(
						'required' => array('content_subject', 'content_link'),
						'link'     => '<a href="%2$s">%1$s</a>',
						'text'     => '<strong>%1$s</strong>'
					);

					$formats['page_comment_reply_new_reply'] = array(
						'required' => array('content_subject', 'content_link'),
						'link'     => '<a href="%2$s">%1$s</a>',
						'text'     => '<strong>%1$s</strong>'
					);
				} else {
					unset($alerts[$id]);
				}
			}
		}
	}

	/**
	 * Add the "My pages" item in the profile popup window
	 *
	 * Добавляем пункт «Мои страницы» в попап-окне профиля
	 *
	 * @param array $profile_areas
	 * @return void
	 */
	public function preProfileAreas(&$profile_areas)
	{
		global $context, $txt, $scripturl;

		if (!empty($context['user']['is_admin']))
			return;

		$profile_areas['info']['areas']['lp_my_pages'] = array(
			'label' => $txt['lp_my_pages'],
			'custom_url' => $scripturl . '?action=admin;area=lp_pages',
			'icon' => 'reports',
			'enabled' => Helpers::request('area') === 'popup',
			'permission' => array(
				'own' => array('light_portal_manage_own_pages'),
				'any' => array()
			)
		);
	}

	/**
	 * Register the "My pages" item in the profile popup window
	 *
	 * Регистрируем пункт «Мои страницы» в попап-окне профиля
	 *
	 * @param array $profile_items
	 * @return void
	 */
	public function profilePopup(&$profile_items)
	{
		global $context;

		if (!empty($context['user']['is_admin']) || !allowedTo('light_portal_manage_own_pages'))
			return;

		$counter = 0;
		foreach ($profile_items as $item) {
			$counter++;

			if ($item['area'] == 'showdrafts')
				break;
		}

		$profile_items = array_merge(
			array_slice($profile_items, 0, $counter, true),
			array(
				array(
					'menu' => 'info',
					'area' => 'lp_my_pages'
				)
			),
			array_slice($profile_items, $counter, null, true)
		);
	}

	/**
	 * Display current actions of members (on portal area)
	 *
	 * Показываем, кто что делает на портале
	 *
	 * @param array $actions
	 * @return string
	 */
	public function whoisOnline(array $actions)
	{
		global $txt, $scripturl, $modSettings, $context;

		$result = '';
		if (empty($actions['action']) && empty($actions['board'])) {
			$result = sprintf($txt['lp_who_viewing_frontpage'], $scripturl);

			if (!empty($modSettings['lp_standalone_mode']) && !empty($modSettings['lp_standalone_url']))
				$result = sprintf($txt['lp_who_viewing_index'], $modSettings['lp_standalone_url'], $scripturl);
		}

		if (!empty($actions['action']) && $actions['action'] == 'portal') {
			if (!empty($actions['sa']) && $actions['sa'] == 'tags') {
				!empty($actions['key'])
					? $result = sprintf($txt['lp_who_viewing_the_tag'], $scripturl . '?action=portal;sa=tags;key=' . $actions['key'], $actions['key'])
					: $result = sprintf($txt['lp_who_viewing_tags'], $scripturl . '?action=portal;sa=tags');
			} else {
				$result = sprintf($txt['lp_who_viewing_frontpage'], $scripturl . '?action=portal');
			}
		}

		if (!empty($actions['action']) && $actions['action'] == 'forum')
			$result = sprintf($txt['who_index'], $scripturl . '?action=forum', $context['forum_name']);

		if (!empty($actions['page']))
			$result = sprintf($txt['lp_who_viewing_page'], $scripturl . '?page=' . $actions['page']);

		if (!empty($actions['action']) && $actions['action'] == 'lp_settings')
			$result = sprintf($txt['lp_who_viewing_portal_settings'], $scripturl . '?action=admin;area=lp_settings');

		if (!empty($actions['action']) && $actions['action'] == 'lp_blocks') {
			if (!empty($actions['area']) && $actions['area'] == 'lp_blocks') {
				$result = sprintf($txt['lp_who_viewing_portal_blocks'], $scripturl . '?action==admin;area=lp_blocks');

				if (!empty($actions['sa']) && $actions['sa'] == 'edit' && !empty($actions['id']))
					$result = sprintf($txt['lp_who_viewing_editing_block'], $actions['id']);

				if (!empty($actions['sa']) && $actions['sa'] == 'add')
					$result = $txt['lp_who_viewing_adding_block'];
			}
		}

		if (!empty($actions['action']) && $actions['action'] == 'lp_pages') {
			if (!empty($actions['area']) && $actions['area'] == 'lp_pages') {
				$result = sprintf($txt['lp_who_viewing_portal_pages'], $scripturl . '?action==admin;area=lp_pages');

				if (!empty($actions['sa']) && $actions['sa'] == 'edit' && !empty($actions['id']))
					$result = sprintf($txt['lp_who_viewing_editing_page'], $actions['id']);

				if (!empty($actions['sa']) && $actions['sa'] == 'add')
					$result = $txt['lp_who_viewing_adding_page'];
			}
		}

		return $result;
	}
}
