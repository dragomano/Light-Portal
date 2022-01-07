<?php

declare(strict_types = 1);

/**
 * Integration.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Entities;
use function add_integration_function;
use function allowedTo;
use function loadLanguage;
use function redirectexit;

if (! defined('SMF'))
	die('No direct access...');

/**
 * This class contains only hook methods
 */
final class Integration extends AbstractMain
{
	public function hooks()
	{
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
		add_integration_function('integrate_alert_types',  __CLASS__ . '::alertTypes#', false, __FILE__);
		add_integration_function('integrate_fetch_alerts',  __CLASS__ . '::fetchAlerts#', false, __FILE__);
		add_integration_function('integrate_pre_profile_areas', __CLASS__ . '::preProfileAreas#', false, __FILE__);
		add_integration_function('integrate_profile_popup', __CLASS__ . '::profilePopup#', false, __FILE__);
		add_integration_function('integrate_whos_online', __CLASS__ . '::whoisOnline#', false, __FILE__);
		add_integration_function('integrate_modification_types', __CLASS__ . '::modificationTypes#', false, __FILE__);
		add_integration_function('integrate_packages_sort_id', __CLASS__ . '::packagesSortId#', false, __FILE__);
		add_integration_function('integrate_credits', __NAMESPACE__ . '\Areas\CreditArea::show#', false, '$sourcedir/LightPortal/Areas/CreditArea.php');
		add_integration_function('integrate_admin_areas', __NAMESPACE__ . '\Areas\ConfigArea::adminAreas#', false, '$sourcedir/LightPortal/Areas/ConfigArea.php');
		add_integration_function('integrate_admin_search', __NAMESPACE__ . '\Areas\ConfigArea::adminSearch#', false, '$sourcedir/LightPortal/Areas/ConfigArea.php');
		add_integration_function('integrate_helpadmin', __NAMESPACE__ . '\Areas\ConfigArea::helpadmin#', false, '$sourcedir/LightPortal/Areas/ConfigArea.php');
	}

	public function userInfo()
	{
		$this->context['lp_load_time'] ??= microtime(true);
		$this->context['lp_num_queries'] ??= 0;

		defined('LP_NAME') || define('LP_NAME', 'Light Portal');
		defined('LP_VERSION') || define('LP_VERSION', '2.0 alpha');
		defined('LP_RELEASE_DATE') || define('LP_RELEASE_DATE', '2022-01-07');
		defined('LP_ADDON_DIR') || define('LP_ADDON_DIR', __DIR__ . '/Addons');
		defined('LP_CACHE_TIME') || define('LP_CACHE_TIME', (int) $this->modSettings['lp_cache_update_interval'] ?? 3600);
		defined('LP_ACTION') || define('LP_ACTION', $this->modSettings['lp_portal_action'] ?? 'portal');
		defined('LP_PAGE_PARAM') || define('LP_PAGE_PARAM', $this->modSettings['lp_page_param'] ?? 'page');
	}

	/**
	 * @hook integrate_pre_css_output
	 */
	public function preCssOutput()
	{
		if (SMF === 'BACKGROUND')
			return;

		echo "\n\t" . '<link rel="preconnect" href="//cdn.jsdelivr.net">';

		if (isset($this->context['portal_next_page']))
			echo "\n\t" . '<link rel="prerender" href="' . $this->context['portal_next_page'] . '">';
	}

	public function loadTheme()
	{
		if ($this->isPortalCanBeLoaded() === false)
			return;

		loadLanguage('LightPortal/');

		$this->defineVars();
		$this->loadCssFiles();

		(new Addon)->prepareAssets()->run();
	}

	public function redirect(string &$setLocation)
	{
		if (empty($this->modSettings['lp_frontpage_mode']) || ! (empty($this->modSettings['lp_standalone_mode']) || empty($this->modSettings['lp_standalone_url'])))
			return;

		if ($this->request()->is('markasread'))
			$setLocation = $this->scripturl . '?action=forum';
	}

	public function actions(array &$actions)
	{
		if (! empty($this->modSettings['lp_frontpage_mode']))
			$actions[LP_ACTION] = ['LightPortal/Entities/FrontPage.php', [new Entities\FrontPage, 'show']];

		$actions['forum'] = ['BoardIndex.php', 'BoardIndex'];

		if ($this->request()->is(LP_ACTION) && $this->context['current_subaction'] === 'categories')
			call_user_func([new Lists\Category, 'show']);

		if ($this->request()->is(LP_ACTION) && $this->context['current_subaction'] === 'tags')
			call_user_func([new Lists\Tag, 'show']);

		if (! empty($this->modSettings['lp_standalone_mode'])) {
			$this->unsetDisabledActions($actions);

			if ($this->context['current_action'] && array_key_exists($this->context['current_action'], $this->context['lp_disabled_actions']))
				redirectexit();
		}
	}

	public function defaultAction()
	{
		if ($this->request()->notEmpty(LP_PAGE_PARAM))
			return call_user_func([new Entities\Page, 'show']);

		if (empty($this->modSettings['lp_frontpage_mode']) || ! (empty($this->modSettings['lp_standalone_mode']) || empty($this->modSettings['lp_standalone_url']))) {
			$this->require('BoardIndex');

			return call_user_func('BoardIndex');
		}

		return call_user_func([new Entities\FrontPage, 'show']);
	}

	/**
	 * Add a selection for some menu items when navigating to the specified areas
	 *
	 * Добавляем выделение для некоторых пунктов меню при переходе в указанные области
	 */
	public function currentAction(string &$current_action)
	{
		if (empty($this->modSettings['lp_frontpage_mode']))
			return;

		if ($this->request()->isEmpty('action')) {
			$current_action = LP_ACTION;

			if (! (empty($this->modSettings['lp_standalone_mode']) || empty($this->modSettings['lp_standalone_url'])) &&
				$this->modSettings['lp_standalone_url'] !== $this->request()->url()) {
				$current_action = 'forum';
			}

			if ($this->request()->notEmpty(LP_PAGE_PARAM)) {
				$current_action = LP_ACTION;
			}
		} else {
			$current_action = empty($this->modSettings['lp_standalone_mode']) && $this->request()->is('forum') ? 'home' : $this->context['current_action'];
		}

		$disabled_actions = empty($this->modSettings['lp_standalone_mode_disabled_actions']) ? [] : explode(',', $this->modSettings['lp_standalone_mode_disabled_actions']);
		$disabled_actions[] = 'home';

		if (isset($this->context['current_board']) || $this->request()->is('keywords'))
			$current_action = empty($this->modSettings['lp_standalone_mode']) ? 'home' : (! in_array('forum', $disabled_actions) ? 'forum' : LP_ACTION);
	}

	public function menuButtons(array &$buttons)
	{
		if ($this->isPortalCanBeLoaded() === false)
			return;

		$this->context['allow_light_portal_view']              = allowedTo('light_portal_view');
		$this->context['allow_light_portal_manage_own_blocks'] = allowedTo('light_portal_manage_own_blocks');
		$this->context['allow_light_portal_manage_own_pages']  = allowedTo('light_portal_manage_own_pages');

		(new Entities\Block)->show();

		// Display "Portal settings" in Main Menu => Admin
		if ($this->context['user']['is_admin']) {
			$counter = 0;
			foreach (array_keys($buttons['admin']['sub_buttons']) as $area) {
				$counter++;

				if ($area === 'featuresettings')
					break;
			}

			$buttons['admin']['sub_buttons'] = array_merge(
				array_slice($buttons['admin']['sub_buttons'], 0, $counter, true),
				[
					'portal_settings' => [
						'title'       => $this->txt['lp_settings'],
						'href'        => $this->scripturl . '?action=admin;area=lp_settings',
						'show'        => true,
						'sub_buttons' => [
							'blocks'  => [
								'title' => $this->txt['lp_blocks'],
								'href'  => $this->scripturl . '?action=admin;area=lp_blocks',
								'amt'   => $this->context['lp_num_active_blocks'],
								'show'  => true,
							],
							'pages'   => [
								'title' => $this->txt['lp_pages'],
								'href'  => $this->scripturl . '?action=admin;area=lp_pages',
								'amt'   => $this->context['lp_num_active_pages'],
								'show'  => true,
							],
							'plugins' => [
								'title'   => $this->txt['lp_plugins'],
								'href'    => $this->scripturl . '?action=admin;area=lp_plugins',
								'amt'     => $this->context['lp_enabled_plugins'] ? count($this->context['lp_enabled_plugins']) : 0,
								'show'    => true,
								'is_last' => true,
							],
						],
					],
				],
				array_slice($buttons['admin']['sub_buttons'], $counter, null, true)
			);
		}

		$this->showDebugInfo();

		if (empty($this->modSettings['lp_frontpage_mode']))
			return;

		// Display "Portal" item in Main Menu
		$buttons = array_merge([
			LP_ACTION => [
				'title'       => $this->txt['lp_portal'],
				'href'        => $this->scripturl,
				'icon'        => 'home',
				'show'        => true,
				'action_hook' => true,
				'is_last'     => $this->context['right_to_left'],
			],
		], $buttons);

		// "Forum"
		$buttons['home']['title'] = $this->txt['lp_forum'];
		$buttons['home']['href']  = $this->scripturl . '?action=forum';
		$buttons['home']['icon']  = 'im_on';

		// Standalone mode
		if (! empty($this->modSettings['lp_standalone_mode'])) {
			$buttons[LP_ACTION]['title']   = $this->txt['lp_portal'];
			$buttons[LP_ACTION]['href']    = $this->modSettings['lp_standalone_url'] ?: $this->scripturl;
			$buttons[LP_ACTION]['icon']    = 'home';
			$buttons[LP_ACTION]['is_last'] = $this->context['right_to_left'];

			$buttons = array_merge(
				array_slice($buttons, 0, 2, true),
				[
					'forum' => [
						'title'       => $this->txt['lp_forum'],
						'href'        => $this->modSettings['lp_standalone_url'] ? $this->scripturl : $this->scripturl . '?action=forum',
						'icon'        => 'im_on',
						'show'        => true,
						'action_hook' => true,
					],
				],
				array_slice($buttons, 2, null, true)
			);

			$this->unsetDisabledActions($buttons);
		}

		// Other fixes
		$this->fixCanonicalUrl();
		$this->fixLinktree();
	}

	/**
	 * Remove comments and alerts on deleting members
	 *
	 * Удаляем комментарии и оповещения при удалении пользователей
	 */
	public function deleteMembers(array $users)
	{
		if (empty($users))
			return;

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_comments
			WHERE author_id IN ({array_int:users})',
			[
				'users' => $users,
			]
		);

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}user_alerts
			WHERE id_member IN ({array_int:users})
				OR id_member_started IN ({array_int:users})',
			[
				'users' => $users,
			]
		);

		$this->cache()->flush();
	}

	/**
	 * @hook integrate_load_illegal_guest_permissions
	 */
	public function loadIllegalGuestPermissions()
	{
		$this->context['non_guest_permissions'] = array_merge(
			$this->context['non_guest_permissions'],
			[
				'light_portal_manage_own_blocks',
				'light_portal_manage_own_pages',
				'light_portal_approve_pages',
			]
		);
	}

	/**
	 * @hook integrate_load_permissions
	 */
	public function loadPermissions(array &$permissionGroups, array &$permissionList, array &$leftPermissionGroups)
	{
		$this->txt['permissiongroup_light_portal'] = LP_NAME;

		$this->context['permissions_excluded']['light_portal_manage_own_blocks'][] = 0;
		$this->context['permissions_excluded']['light_portal_manage_own_pages'][]  = 0;
		$this->context['permissions_excluded']['light_portal_approve_pages'][]     = 0;

		$permissionList['membergroup']['light_portal_view']              = [false, 'light_portal'];
		$permissionList['membergroup']['light_portal_manage_own_blocks'] = [false, 'light_portal'];
		$permissionList['membergroup']['light_portal_manage_own_pages']  = [false, 'light_portal'];
		$permissionList['membergroup']['light_portal_approve_pages']     = [false, 'light_portal'];

		$leftPermissionGroups[] = 'light_portal';
	}

	/**
	 * @hook integrate_alert_types
	 */
	public function alertTypes(array &$alert_types)
	{
		if (empty($this->modSettings['lp_show_comment_block']))
			return;

		$this->txt['alert_group_light_portal'] = LP_NAME;

		if ($this->modSettings['lp_show_comment_block'] === 'default')
			$alert_types['light_portal'] = [
				'page_comment' => [
					'alert' => 'yes',
					'email' => 'never',
					'permission' => ['name' => 'light_portal_manage_own_pages', 'is_board' => false]
				],
				'page_comment_reply' => [
					'alert' => 'yes',
					'email' => 'never',
					'permission' => ['name' => 'light_portal_view', 'is_board' => false]
				]
			];
	}

	/**
	 * Adding a notification about new comments
	 *
	 * Добавляем оповещение о новых комментариях
	 */
	public function fetchAlerts(array &$alerts)
	{
		if (empty($alerts))
			return;

		foreach ($alerts as $id => $alert) {
			if (in_array($alert['content_action'], ['page_comment', 'page_comment_reply'])) {
				if ($alert['sender_id'] !== $this->user_info['id']) {
					$alerts[$id]['icon'] = '<span class="alert_icon main_icons ' . ($alert['content_action'] === 'page_comment' ? 'im_off' : 'im_on') . '"></span>';
					$alerts[$id]['text'] = __('alert_' . $alert['content_type'] . '_' . $alert['content_action'], ['gender' => $alert['extra']['sender_gender']]);

					$substitutions = [
						'{member_link}' => $alert['sender_id'] && $alert['show_links'] ? '<a href="' . $this->scripturl . '?action=profile;u=' . $alert['sender_id'] . '">' . $alert['sender_name'] . '</a>' : '<strong>' . $alert['sender_name'] . '</strong>',
						'{content_subject}' => $alert['extra']['content_subject']
					];

					$alerts[$id]['text'] = strtr($alerts[$id]['text'], $substitutions);
					$alerts[$id]['target_href'] = $alert['extra']['content_link'];
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
	 */
	public function preProfileAreas(array &$profile_areas)
	{
		if ($this->context['user']['is_admin'])
			return;

		$profile_areas['info']['areas']['lp_my_blocks'] = [
			'label' => $this->txt['lp_my_blocks'],
			'custom_url' => $this->scripturl . '?action=admin;area=lp_blocks',
			'icon' => 'modifications',
			'enabled' => $this->request('area') === 'popup',
			'permission' => [
				'own' => ['light_portal_manage_own_blocks'],
				'any' => []
			]
		];

		$profile_areas['info']['areas']['lp_my_pages'] = [
			'label' => $this->txt['lp_my_pages'],
			'custom_url' => $this->scripturl . '?action=admin;area=lp_pages',
			'icon' => 'reports',
			'enabled' => $this->request('area') === 'popup',
			'permission' => [
				'own' => ['light_portal_manage_own_pages'],
				'any' => []
			]
		];
	}

	/**
	 * @hook integrate_profile_popup
	 */
	public function profilePopup(array &$profile_items)
	{
		if ($this->context['user']['is_admin'])
			return;

		if (! (allowedTo('light_portal_manage_own_blocks') || allowedTo('light_portal_manage_own_blocks')))
			return;

		$counter = 0;
		foreach ($profile_items as $item) {
			$counter++;

			if ($item['area'] === 'showdrafts')
				break;
		}

		$portal_items = [];

		if (allowedTo('light_portal_manage_own_blocks'))
			$portal_items[] = [
				'menu' => 'info',
				'area' => 'lp_my_blocks'
			];

		if (allowedTo('light_portal_manage_own_pages'))
			$portal_items[] = [
				'menu' => 'info',
				'area' => 'lp_my_pages'
			];

		if (empty($portal_items))
			return;

		$profile_items = array_merge(
			array_slice($profile_items, 0, $counter, true),
			$portal_items,
			array_slice($profile_items, $counter, null, true)
		);
	}

	/**
	 * @hook integrate_whos_online
	 */
	public function whoisOnline(array $actions): string
	{
		$result = '';
		if (empty($actions['action']) && empty($actions['board'])) {
			$result = sprintf($this->txt['lp_who_viewing_frontpage'], $this->scripturl);

			if (! (empty($this->modSettings['lp_standalone_mode']) || empty($this->modSettings['lp_standalone_url'])))
				$result = sprintf($this->txt['lp_who_viewing_index'], $this->modSettings['lp_standalone_url'], $this->scripturl);
		}

		if (isset($actions[LP_PAGE_PARAM]))
			$result = sprintf($this->txt['lp_who_viewing_page'], $this->scripturl . '?' . LP_PAGE_PARAM . '=' . $actions[LP_PAGE_PARAM]);

		if (empty($actions['action']))
			return $result;

		if ($actions['action'] === LP_ACTION) {
			if ($actions['sa'] === 'tags') {
				$tags = $this->getAllTags();

				isset($actions['id'])
					? $result = sprintf($this->txt['lp_who_viewing_the_tag'], $this->scripturl . '?action=' . LP_ACTION . ';sa=tags;id=' . $actions['id'], $tags[$actions['id']])
					: $result = sprintf($this->txt['lp_who_viewing_tags'], $this->scripturl . '?action=' . LP_ACTION . ';sa=tags');
			} else {
				$result = sprintf($this->txt['lp_who_viewing_frontpage'], $this->scripturl . '?action=' . LP_ACTION);
			}
		}

		if ($actions['action'] === 'forum')
			$result = sprintf($this->txt['who_index'], $this->scripturl . '?action=forum', $this->context['forum_name']);

		if ($actions['action'] === 'lp_settings')
			$result = sprintf($this->txt['lp_who_viewing_portal_settings'], $this->scripturl . '?action=admin;area=lp_settings');

		if ($actions['action'] === 'lp_blocks') {
			if ($actions['area'] === 'lp_blocks') {
				$result = sprintf($this->txt['lp_who_viewing_portal_blocks'], $this->scripturl . '?action=admin;area=lp_blocks');

				if ($actions['sa'] === 'edit' && $actions['id'])
					$result = sprintf($this->txt['lp_who_viewing_editing_block'], $actions['id']);

				if ($actions['sa'] === 'add')
					$result = $this->txt['lp_who_viewing_adding_block'];
			}
		}

		if ($actions['action'] === 'lp_pages') {
			if ($actions['area'] === 'lp_pages') {
				$result = sprintf($this->txt['lp_who_viewing_portal_pages'], $this->scripturl . '?action==admin;area=lp_pages');

				if ($actions['sa'] === 'edit' && $actions['id'])
					$result = sprintf($this->txt['lp_who_viewing_editing_page'], $actions['id']);

				if ($actions['sa'] === 'add')
					$result = $this->txt['lp_who_viewing_adding_page'];
			}
		}

		return $result;
	}

	/**
	 * @hook integrate_modification_types
	 */
	public function modificationTypes()
	{
		$this->context['modification_types'][] = 'lp_addon';
	}

	/**
	 * @hook integrate_packages_sort_id
	 */
	public function packagesSortId(array &$sort_id)
	{
		$sort_id['lp_addon'] = 1;
	}
}
