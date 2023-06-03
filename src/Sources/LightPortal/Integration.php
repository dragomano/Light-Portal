<?php declare(strict_types=1);

/**
 * Integration.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.2
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Entities\{Block, Category, FrontPage, Page, Tag};
use IntlException;

if (! defined('SMF'))
	die('No direct access...');

/**
 * This class contains only hook methods
 */
final class Integration extends AbstractMain
{
	public function hooks(): void
	{
		$this->applyHook('user_info');
		$this->applyHook('pre_css_output');
		$this->applyHook('load_theme');
		$this->applyHook('redirect', 'changeRedirect');
		$this->applyHook('actions');
		$this->applyHook('default_action');
		$this->applyHook('current_action');
		$this->applyHook('current_action', 'currentPage');
		$this->applyHook('menu_buttons');
		$this->applyHook('display_buttons');
		$this->applyHook('delete_members');
		$this->applyHook('load_illegal_guest_permissions');
		$this->applyHook('load_permissions');
		$this->applyHook('alert_types');
		$this->applyHook('fetch_alerts');
		$this->applyHook('profile_areas');
		$this->applyHook('profile_popup');
		$this->applyHook('download_request');
		$this->applyHook('whos_online', 'whoisOnline');
		$this->applyHook('integrate_credits', [__NAMESPACE__ . '\Areas\CreditArea', 'show'], '$sourcedir/LightPortal/Areas/CreditArea.php');
		$this->applyHook('admin_areas', [__NAMESPACE__ . '\Areas\ConfigArea', 'adminAreas'], '$sourcedir/LightPortal/Areas/ConfigArea.php');
		$this->applyHook('helpadmin', [__NAMESPACE__ . '\Areas\ConfigArea', 'helpadmin'], '$sourcedir/LightPortal/Areas/ConfigArea.php');
		$this->applyHook('clean_cache');
	}

	public function userInfo(): void
	{
		$this->context['lp_load_time'] ??= microtime(true);
		$this->context['lp_num_queries'] ??= 0;

		defined('LP_NAME') || define('LP_NAME', 'Light Portal');
		defined('LP_VERSION') || define('LP_VERSION', '2.2.0 Mandalorian Edition');
		defined('LP_ADDON_URL') || define('LP_ADDON_URL', $this->boardurl . '/Sources/LightPortal/Addons');
		defined('LP_ADDON_DIR') || define('LP_ADDON_DIR', __DIR__ . '/Addons');
		defined('LP_CACHE_TIME') || define('LP_CACHE_TIME', (int) ($this->modSettings['lp_cache_update_interval'] ?? 72000));
		defined('LP_ACTION') || define('LP_ACTION', $this->modSettings['lp_portal_action'] ?? 'portal');
		defined('LP_PAGE_PARAM') || define('LP_PAGE_PARAM', $this->modSettings['lp_page_param'] ?? 'page');
		defined('LP_BASE_URL') || define('LP_BASE_URL', $this->scripturl . '?action=' . LP_ACTION);
		defined('LP_PAGE_URL') || define('LP_PAGE_URL', $this->scripturl . '?' . LP_PAGE_PARAM . '=');
	}

	public function preCssOutput(): void
	{
		if (SMF === 'BACKGROUND')
			return;

		echo "\n\t" . '<link rel="preconnect" href="//cdn.jsdelivr.net">';

		if (! empty($this->context['portal_next_page']))
			echo "\n\t" . '<link rel="prerender" href="' . $this->context['portal_next_page'] . '">';

		if (! isset($this->modSettings['lp_fa_source']) || $this->modSettings['lp_fa_source'] === 'css_cdn')
			echo "\n\t" . '<link rel="preload" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
	}

	public function loadTheme(): void
	{
		if ($this->isPortalCanBeLoaded() === false)
			return;

		$this->loadLanguage('LightPortal/LightPortal');

		$this->defineVars();

		$this->loadAssets();

		$this->hook('init');
	}

	/**
	 * @hook integrate_redirect
	 */
	public function changeRedirect(string &$setLocation): void
	{
		if (empty($this->modSettings['lp_frontpage_mode']) || ! (empty($this->modSettings['lp_standalone_mode']) || empty($this->modSettings['lp_standalone_url'])))
			return;

		if ($this->request()->is('markasread'))
			$setLocation = $this->scripturl . '?action=forum';
	}

	public function actions(array &$actions): void
	{
		if (! empty($this->modSettings['lp_frontpage_mode']))
			$actions[LP_ACTION] = [false, [new FrontPage, 'show']];

		$actions['forum'] = ['BoardIndex.php', 'BoardIndex'];

		if ($this->request()->is(LP_ACTION) && $this->context['current_subaction'] === 'categories')
			(new Category)->show(new Page);

		if ($this->request()->is(LP_ACTION) && $this->context['current_subaction'] === 'tags')
			(new Tag)->show(new Page);

		if ($this->request()->is(LP_ACTION) && $this->context['current_subaction'] === 'promote')
			$this->promoteTopic();

		if (! empty($this->modSettings['lp_standalone_mode'])) {
			$this->unsetDisabledActions($actions);

			if (! empty($this->context['current_action']) && array_key_exists($this->context['current_action'], $this->context['lp_disabled_actions']))
				$this->redirect();
		}
	}

	public function defaultAction()
	{
		if ($this->request()->isNotEmpty(LP_PAGE_PARAM))
			return call_user_func([new Page, 'show']);

		if (empty($this->modSettings['lp_frontpage_mode']) || ! (empty($this->modSettings['lp_standalone_mode']) || empty($this->modSettings['lp_standalone_url']))) {
			$this->require('BoardIndex');

			return call_user_func('BoardIndex');
		}

		return call_user_func([new FrontPage, 'show']);
	}

	/**
	 * Add a selection for some menu items when navigating to the specified areas
	 *
	 * Добавляем выделение для некоторых пунктов меню при переходе в указанные области
	 */
	public function currentAction(string &$current_action): void
	{
		if (empty($this->modSettings['lp_frontpage_mode']))
			return;

		if ($this->request()->isEmpty('action')) {
			$current_action = LP_ACTION;

			if (! (empty($this->modSettings['lp_standalone_mode']) || empty($this->modSettings['lp_standalone_url'])) &&
				$this->modSettings['lp_standalone_url'] !== $this->request()->url()) {
				$current_action = 'forum';
			}

			if ($this->request()->isNotEmpty(LP_PAGE_PARAM)) {
				$current_action = LP_ACTION;
			}
		} else {
			$current_action = empty($this->modSettings['lp_standalone_mode']) && $this->request()->is('forum') ? 'home' : $this->context['current_action'];
		}

		$disabled_actions = empty($this->modSettings['lp_disabled_actions']) ? [] : explode(',', $this->modSettings['lp_disabled_actions']);
		$disabled_actions[] = 'home';

		if (isset($this->context['current_board']) || $this->request()->is('keywords'))
			$current_action = empty($this->modSettings['lp_standalone_mode']) ? 'home' : (! in_array('forum', $disabled_actions) ? 'forum' : LP_ACTION);
	}

	/**
	 * @hook integrate_current_page
	 */
	public function currentPage(string &$current_action): void
	{
		if (empty($this->context['lp_page']) || empty($this->context['lp_menu_pages']) || empty($this->context['lp_menu_pages'][$this->context['lp_page']['id']]))
			return;

		if ($this->request()->url() === LP_PAGE_URL . $this->context['lp_page']['alias']) {
			$current_action = 'portal_page_' . $this->request(LP_PAGE_PARAM);
		}
	}

	public function menuButtons(array &$buttons): void
	{
		if ($this->isPortalCanBeLoaded() === false)
			return;

		(new Block)->show();

		$this->prepareAdminButtons($buttons);

		$this->prepareModerationButtons($buttons);

		$this->preparePageButtons($buttons);

		$this->showDebugInfo();

		if (empty($this->modSettings['lp_frontpage_mode']))
			return;

		$this->preparePortalButtons($buttons);

		$this->fixCanonicalUrl();

		$this->fixLinktree();
	}

	/**
	 * Add "Promote to frontpage" (or "Remove from frontpage") button if the "Selected topics" portal mode is selected
	 *
	 * Добавляем кнопку «Добавить на главную» (или «Убрать с главной»), если выбран режим портала «Выбранные темы»
	 */
	public function displayButtons(): void
	{
		if (empty($this->user_info['is_admin']) || empty($this->modSettings['lp_frontpage_mode']) || $this->modSettings['lp_frontpage_mode'] !== 'chosen_topics')
			return;

		$this->context['normal_buttons']['lp_promote'] = [
			'text' => in_array($this->context['current_topic'], $this->context['lp_frontpage_topics']) ? 'lp_remove_from_fp' : 'lp_promote_to_fp',
			'url'  => LP_BASE_URL . ';sa=promote;t=' . $this->context['current_topic']
		];
	}

	/**
	 * Remove comments, and alerts on deleting members
	 * @TODO Remove all portal content from these users?
	 * Удаляем комментарии и оповещения при удалении пользователей
	 */
	public function deleteMembers(array $users): void
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

	public function loadIllegalGuestPermissions(): void
	{
		$this->context['non_guest_permissions'] = array_merge(
			$this->context['non_guest_permissions'],
			[
				'light_portal_manage_pages',
				'light_portal_approve_pages',
			]
		);
	}

	public function loadPermissions(array &$permissionGroups, array &$permissionList, array &$leftPermissionGroups): void
	{
		$this->txt['permissiongroup_light_portal'] = LP_NAME;

		$this->context['permissions_excluded']['light_portal_manage_pages'][]  = 0;
		$this->context['permissions_excluded']['light_portal_approve_pages'][] = 0;

		$permissionList['membergroup']['light_portal_view']          = [false, 'light_portal'];
		$permissionList['membergroup']['light_portal_manage_pages']  = [true, 'light_portal'];
		$permissionList['membergroup']['light_portal_approve_pages'] = [false, 'light_portal'];

		$permissionGroups['membergroup'][] = $leftPermissionGroups[] = 'light_portal';
	}

	public function alertTypes(array &$types): void
	{
		$this->txt['alert_group_light_portal'] = $this->txt['lp_portal'];

		if (! empty($this->modSettings['lp_show_comment_block']) ?? $this->modSettings['lp_show_comment_block'] === 'default')
			$types['light_portal'] = [
				'page_comment' => [
					'alert' => 'yes',
					'email' => 'never',
					'permission' => [
						'name'     => 'light_portal_manage_pages_own',
						'is_board' => false
					]
				],
				'page_comment_reply' => [
					'alert' => 'yes',
					'email' => 'never',
					'permission' => [
						'name'     => 'light_portal_view',
						'is_board' => false
					]
				]
			];

		$types['light_portal']['page_unapproved'] = [
			'alert' => 'yes',
			'email' => 'yes',
			'permission' => [
				'name'     => 'light_portal_manage_pages_any',
				'is_board' => false
			]
		];
	}

	/**
	 * @throws IntlException
	 */
	public function fetchAlerts(array &$alerts): void
	{
		foreach ($alerts as $id => $alert) {
			if (in_array($alert['content_action'], ['page_comment', 'page_comment_reply', 'page_unapproved'])) {
				if ($alert['sender_id'] !== $this->user_info['id']) {
					$alerts[$id]['icon'] = '<span class="alert_icon main_icons ' . ($alert['content_action'] === 'page_unapproved' ? 'news' : ($alert['content_action'] === 'page_comment' ? 'im_off' : 'im_on')) . '"></span>';
					$alerts[$id]['text'] = $this->translate('alert_' . $alert['content_type'] . '_' . $alert['content_action'], ['gender' => $alert['extra']['sender_gender']]);

					$substitutions = [
						'{member_link}' => $alert['sender_id'] && $alert['show_links'] ? '<a href="' . $this->scripturl . '?action=profile;u=' . $alert['sender_id'] . '">' . $alert['sender_name'] . '</a>' : '<strong>' . $alert['sender_name'] . '</strong>',
						'{content_subject}' => '(' . $alert['extra']['content_subject'] . ')'
					];

					$alerts[$id]['text'] = strtr($alerts[$id]['text'], $substitutions);
					$alerts[$id]['target_href'] = $alert['extra']['content_link'];
				} else {
					unset($alerts[$id]);
				}
			}
		}
	}

	public function profileAreas(array &$profile_areas): void
	{
		if ($this->context['user']['is_admin'])
			return;

		$profile_areas['info']['areas']['lp_my_pages'] = [
			'label'      => $this->txt['lp_my_pages'],
			'custom_url' => $this->scripturl . '?action=admin;area=lp_pages',
			'icon'       => 'reports',
			'enabled'    => $this->request('area') === 'popup',
			'permission' => 'light_portal_manage_pages_own',
		];
	}

	public function profilePopup(array &$profile_items): void
	{
		if ($this->context['user']['is_admin'] || empty($this->context['allow_light_portal_manage_pages_own']))
			return;

		$counter = 0;
		foreach ($profile_items as $item) {
			$counter++;

			if ($item['area'] === 'showdrafts')
				break;
		}

		$profile_items = array_merge(
			array_slice($profile_items, 0, $counter, true),
			[
				[
					'menu' => 'info',
					'area' => 'lp_my_pages'
				]
			],
			array_slice($profile_items, $counter, null, true)
		);
	}

	/**
	 * @hook integrate_download_request
	 */
	public function downloadRequest(&$attachRequest): void
	{
		$this->loadTheme();

		$this->hook('downloadRequest', [&$attachRequest]);
	}

	public function whoisOnline(array $actions): string
	{
		$result = '';
		if (empty($actions['action']) && empty($actions['board'])) {
			$result = sprintf($this->txt['lp_who_viewing_frontpage'], $this->scripturl);

			if (! (empty($this->modSettings['lp_standalone_mode']) || empty($this->modSettings['lp_standalone_url'])))
				$result = sprintf($this->txt['lp_who_viewing_index'], $this->modSettings['lp_standalone_url'], $this->scripturl);
		}

		if (isset($actions[LP_PAGE_PARAM]))
			$result = sprintf($this->txt['lp_who_viewing_page'], LP_PAGE_URL . $actions[LP_PAGE_PARAM]);

		if (empty($actions['action']))
			return $result;

		if ($actions['action'] === LP_ACTION) {
			$result = sprintf($this->txt['lp_who_viewing_frontpage'], LP_BASE_URL);

			if (isset($actions['sa']) && $actions['sa'] === 'tags') {
				$tags = $this->getEntityList('tag');

				isset($actions['id'])
					? $result = sprintf($this->txt['lp_who_viewing_the_tag'], LP_BASE_URL . ';sa=tags;id=' . $actions['id'], $tags[$actions['id']])
					: $result = sprintf($this->txt['lp_who_viewing_tags'], LP_BASE_URL . ';sa=tags');
			}

			if (isset($actions['sa']) && $actions['sa'] === 'categories') {
				$categories = $this->getEntityList('category');

				isset($actions['id'])
					? $result = sprintf($this->txt['lp_who_viewing_the_category'], LP_BASE_URL . ';sa=categories;id=' . $actions['id'], $categories[$actions['id']]['name'])
					: $result = sprintf($this->txt['lp_who_viewing_categories'], LP_BASE_URL . ';sa=categories');
			}
		}

		if ($actions['action'] === 'forum')
			$result = sprintf($this->txt['who_index'], $this->scripturl . '?action=forum', $this->context['forum_name']);

		return $result;
	}

	public function cleanCache(): void
	{
		$file = $this->settings['default_theme_dir'] . '/css/light_portal/less/portal.less';

		if (is_file($file)) {
			touch($file);
		}
	}
}