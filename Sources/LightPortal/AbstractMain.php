<?php declare(strict_types=1);

/**
 * AbstractMain.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Entities\{Block, Page};

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractMain
{
	use Helper;

	abstract public function hooks();

	protected function isPortalCanBeLoaded(): bool
	{
		if (! defined('LP_NAME') || isset($this->context['uninstalling']) || $this->request()->is('printpage')) {
			$this->modSettings['minimize_files'] = 0;
			return false;
		}

		return true;
	}

	protected function defineVars(): void
	{
		$this->context['allow_light_portal_view']             = $this->allowedTo('light_portal_view');
		$this->context['allow_light_portal_manage_blocks']    = $this->allowedTo('light_portal_manage_blocks');
		$this->context['allow_light_portal_manage_pages_own'] = $this->allowedTo('light_portal_manage_pages_own');
		$this->context['allow_light_portal_manage_pages_any'] = $this->allowedTo('light_portal_manage_pages_any');
		$this->context['allow_light_portal_approve_pages']    = $this->allowedTo('light_portal_approve_pages');

		$this->calculateNumberOfEntities();

		$this->context['lp_all_title_classes']   = $this->getTitleClasses();
		$this->context['lp_all_content_classes'] = $this->getContentClasses();
		$this->context['lp_block_placements']    = $this->getBlockPlacements();
		$this->context['lp_plugin_types']        = $this->getPluginTypes();
		$this->context['lp_content_types']       = $this->getContentTypes();

		$this->context['lp_enabled_plugins']  = empty($this->modSettings['lp_enabled_plugins'])  ? [] : explode(',', $this->modSettings['lp_enabled_plugins']);
		$this->context['lp_frontpage_pages']  = empty($this->modSettings['lp_frontpage_pages'])  ? [] : explode(',', $this->modSettings['lp_frontpage_pages']);
		$this->context['lp_frontpage_topics'] = empty($this->modSettings['lp_frontpage_topics']) ? [] : explode(',', $this->modSettings['lp_frontpage_topics']);

		$this->context['lp_header_panel_width'] = empty($this->modSettings['lp_header_panel_width']) ? 12 : (int) $this->modSettings['lp_header_panel_width'];
		$this->context['lp_left_panel_width']   = empty($this->modSettings['lp_left_panel_width'])   ? ['lg' => 3, 'xl' => 2] : $this->jsonDecode($this->modSettings['lp_left_panel_width'], true, false);
		$this->context['lp_right_panel_width']  = empty($this->modSettings['lp_right_panel_width'])  ? ['lg' => 3, 'xl' => 2] : $this->jsonDecode($this->modSettings['lp_right_panel_width'], true, false);
		$this->context['lp_footer_panel_width'] = empty($this->modSettings['lp_footer_panel_width']) ? 12 : (int) $this->modSettings['lp_footer_panel_width'];

		$this->context['lp_panel_direction'] = $this->jsonDecode($this->modSettings['lp_panel_direction'] ?? '', true, false);

		$this->context['lp_active_blocks'] = (new Block)->getActive();

		$this->context['lp_icon_set'] = $this->getEntityList('icon');
	}

	protected function loadAssets(): void
	{
		if (! empty($this->modSettings['lp_fa_source'])) {
			if ($this->modSettings['lp_fa_source'] === 'css_local') {
				$this->loadCSSFile('all.min.css', [], 'portal_fontawesome');
			} elseif ($this->modSettings['lp_fa_source'] === 'custom' && $this->modSettings['lp_fa_custom']) {
				$this->loadExtCSS(
					$this->modSettings['lp_fa_custom'],
					['seed' => false],
					'portal_fontawesome'
				);
			}
		}

		$this->loadCSSFile('light_portal/flexboxgrid.css');
		$this->loadCSSFile('light_portal/portal.css');
		$this->loadCSSFile('light_portal/plugins.css');
		$this->loadCSSFile('custom_frontpage.css');

		$this->loadJavaScriptFile('light_portal/plugins.js', ['minimize' => true]);
	}

	/**
	 * Remove unnecessary areas for the standalone mode
	 *
	 * Удаляем ненужные в автономном режиме области
	 */
	protected function unsetDisabledActions(array &$data): void
	{
		$disabled_actions = empty($this->modSettings['lp_disabled_actions']) ? [] : explode(',', $this->modSettings['lp_disabled_actions']);
		$disabled_actions[] = 'home';
		$disabled_actions = array_flip($disabled_actions);

		foreach (array_keys($data) as $action) {
			if (array_key_exists($action, $disabled_actions))
				unset($data[$action]);
		}

		if (array_key_exists('search', $disabled_actions))
			$this->context['allow_search'] = false;

		if (array_key_exists('moderate', $disabled_actions))
			$this->context['allow_moderation_center'] = false;

		if (array_key_exists('calendar', $disabled_actions))
			$this->context['allow_calendar'] = false;

		if (array_key_exists('mlist', $disabled_actions))
			$this->context['allow_memberlist'] = false;

		$this->context['lp_disabled_actions'] = $disabled_actions;
	}

	/**
	 * Fix canonical url for forum action
	 *
	 * Исправляем канонический адрес для области forum
	 */
	protected function fixCanonicalUrl(): void
	{
		if ($this->request()->is('forum'))
			$this->context['canonical_url'] = $this->scripturl . '?action=forum';
	}

	/**
	 * Change the link tree
	 *
	 * Меняем дерево ссылок
	 */
	protected function fixLinktree(): void
	{
		if (empty($this->context['current_board']) && $this->request()->hasNot('c') || empty($this->context['linktree'][1]))
			return;

		$old_url = explode('#', $this->context['linktree'][1]['url']);

		if (! empty($old_url[1]))
			$this->context['linktree'][1]['url'] = $this->scripturl . '?action=forum#' . $old_url[1];
	}

	/**
	 * Allow forum action page indexing
	 *
	 * Разрешаем индексацию главной страницы форума
	 */
	protected function fixForumIndexing(): void
	{
		$this->context['robot_no_index'] = false;
	}

	/**
	 * Show the script execution time and the number of the portal queries
	 *
	 * Отображаем время выполнения скрипта и количество запросов к базе
	 */
	protected function showDebugInfo(): void
	{
		if (empty($this->modSettings['lp_show_debug_info']) || empty($this->context['user']['is_admin']) || empty($this->context['template_layers']) || $this->request()->is('devtools'))
			return;

		$this->context['lp_load_page_stats'] = sprintf($this->txt['lp_load_page_stats'], round(microtime(true) - $this->context['lp_load_time'], 3), $this->context['lp_num_queries']);

		$this->loadTemplate('LightPortal/ViewDebug');

		if (empty($key = array_search('lp_portal', $this->context['template_layers']))) {
			$this->context['template_layers'][] = 'debug';
			return;
		}

		$this->context['template_layers'] = array_merge(
			array_slice($this->context['template_layers'], 0, $key, true),
			['debug'],
			array_slice($this->context['template_layers'], $key, null, true)
		);
	}

	/**
	 * Display "Portal settings" in Main Menu => Admin
	 *
	 * Отображаем "Настройки портала" в Главном меню => Админка
	 */
	protected function prepareAdminButtons(array &$buttons): void
	{
		if ($this->context['user']['is_admin'] === false)
			return;

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
							'amt'   => $this->context['lp_quantities']['active_blocks'],
							'show'  => true,
						],
						'pages'   => [
							'title' => $this->txt['lp_pages'],
							'href'  => $this->scripturl . '?action=admin;area=lp_pages',
							'amt'   => $this->context['lp_quantities']['active_pages'],
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

	protected function prepareModerationButtons(array &$buttons): void
	{
		if ($this->context['allow_light_portal_manage_pages_any'] === false)
			return;

		$buttons['moderate']['show'] = true;

		$buttons['moderate']['sub_buttons'] = [
			'lp_pages' => [
				'title' => $this->txt['lp_pages_unapproved'],
				'href'  => $this->scripturl . '?action=admin;area=lp_pages;sa=main;moderate',
				'amt'   => $this->context['lp_quantities']['unapproved_pages'],
				'show'  => true,
			],
		] + $buttons['moderate']['sub_buttons'];
	}

	protected function preparePageButtons(array &$buttons): void
	{
		if (empty($this->context['lp_menu_pages'] = $this->getMenuPages()))
			return;

		$page_buttons = [];
		foreach ($this->context['lp_menu_pages'] as $item) {
			$page_buttons['portal_page_' . $item['alias']] = [
				'title' => ($item['icon'] ? '<span class="portal_menu_icons fa-fw ' . $item['icon'] . '"></span>' : '') . $this->getTranslatedTitle($item['title']),
				'href'  => LP_PAGE_URL . $item['alias'],
				'icon'  => '" style="display: none"></span><span',
				'show'  => $this->canViewItem($item['permissions'])
			];
		}

		$counter = -1;
		foreach (array_keys($buttons) as $area) {
			$counter++;

			if ($area === 'admin')
				break;
		}

		$buttons = array_merge(
			array_slice($buttons, 0, $counter, true),
			$page_buttons,
			array_slice($buttons, $counter, null, true)
		);
	}

	protected function preparePortalButtons(array &$buttons): void
	{
		// Display "Portal" item in Main Menu
		$buttons = array_merge([
			LP_ACTION => [
				'title'       => $this->txt['lp_portal'],
				'href'        => $this->scripturl,
				'icon'        => 'home',
				'show'        => true,
				'action_hook' => true,
				'is_last'     => $this->context['right_to_left']
			],
		], $buttons);

		// "Forum"
		$buttons['home']['title'] = $this->txt['lp_forum'];
		$buttons['home']['href']  = $this->scripturl . '?action=forum';
		$buttons['home']['icon']  = 'im_on';

		// Standalone mode
		if (empty($this->modSettings['lp_standalone_mode']))
			return;

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
					'action_hook' => true
				],
			],
			array_slice($buttons, 2, null, true)
		);

		$this->unsetDisabledActions($buttons);
	}

	protected function promoteTopic(): void
	{
		if (empty($this->user_info['is_admin']) || $this->request()->hasNot('t'))
			return;

		$topic = $this->request('t');

		if (($key = array_search($topic, $this->context['lp_frontpage_topics'])) !== false) {
			unset($this->context['lp_frontpage_topics'][$key]);
		} else {
			$this->context['lp_frontpage_topics'][] = $topic;
		}

		$this->updateSettings(['lp_frontpage_topics' => implode(',', $this->context['lp_frontpage_topics'])]);

		$this->redirect('topic=' . $topic);
	}

	private function getMenuPages(): array
	{
		if (($pages = $this->cache()->get('menu_pages')) === null) {
			$titles = $this->getEntityList('title');

			$request = $this->smcFunc['db_query']('', '
				SELECT p.page_id, p.alias, p.permissions, pp2.value AS icon
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {literal:page})
					LEFT JOIN {db_prefix}lp_params AS pp2 ON (p.page_id = pp2.item_id AND pp2.type = {literal:page} AND pp2.name = {literal:page_icon})
				WHERE p.status IN ({array_int:statuses})
					AND p.created_at <= {int:current_time}
					AND pp.name = {literal:show_in_menu}
					AND pp.value = {string:show_in_menu}',
				[
					'statuses'     => [Page::STATUS_ACTIVE, Page::STATUS_INTERNAL],
					'current_time' => time(),
					'show_in_menu' => '1',
				]
			);

			$pages = [];
			while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
				$pages[$row['page_id']] = [
					'id'          => $row['page_id'],
					'alias'       => $row['alias'],
					'permissions' => (int) $row['permissions'],
					'title'       => [],
					'icon'        => $row['icon'],
				];

				$pages[$row['page_id']]['title'] = $titles[$row['page_id']];
			}

			$this->smcFunc['db_free_result']($request);
			$this->context['lp_num_queries']++;

			$this->cache()->put('menu_pages', $pages);
		}

		return $pages;
	}

	private function calculateNumberOfEntities(): void
	{
		if (($num_entities = $this->cache()->get('num_active_entities_u' . $this->user_info['id'])) === null) {
			$request = $this->smcFunc['db_query']('', '
				SELECT
					(
						SELECT COUNT(b.block_id)
						FROM {db_prefix}lp_blocks b
						WHERE b.status = {int:active}' . ($this->user_info['is_admin'] ? '
							AND b.user_id = 0' : '
							AND b.user_id = {int:user_id}') . '
					) AS num_blocks,
					(
						SELECT COUNT(p.page_id)
						FROM {db_prefix}lp_pages p
						WHERE p.status = {int:active}' . ($this->user_info['is_admin'] ? '' : '
							AND p.author_id = {int:user_id}') . '
					) AS num_pages,
					(
						SELECT COUNT(page_id)
						FROM {db_prefix}lp_pages
						WHERE author_id = {int:user_id}
					) AS num_my_pages,
					(
						SELECT COUNT(page_id)
						FROM {db_prefix}lp_pages
						WHERE status = {int:unapproved}
					) AS num_unapproved_pages,
					(
						SELECT COUNT(page_id)
						FROM {db_prefix}lp_pages
						WHERE status = {int:internal}
					) AS num_internal_pages',
				[
					'active'     => Page::STATUS_ACTIVE,
					'unapproved' => Page::STATUS_UNAPPROVED,
					'internal'   => Page::STATUS_INTERNAL,
					'user_id'    => $this->user_info['id']
				]
			);

			$num_entities = $this->smcFunc['db_fetch_assoc']($request);
			array_walk($num_entities, fn(&$item) => $item = (int) $item);

			$this->smcFunc['db_free_result']($request);
			$this->context['lp_num_queries']++;

			$this->cache()->put('num_active_entities_u' . $this->user_info['id'], $num_entities);
		}

		$this->context['lp_quantities'] = [
			'active_blocks'    => $num_entities['num_blocks'],
			'active_pages'     => $num_entities['num_pages'],
			'my_pages'         => $num_entities['num_my_pages'],
			'unapproved_pages' => $num_entities['num_unapproved_pages'],
			'internal_pages'   => $num_entities['num_internal_pages'],
		];
	}

	private function getBlockPlacements(): array
	{
		return array_combine(['header', 'top', 'left', 'right', 'bottom', 'footer'], $this->txt['lp_block_placement_set']);
	}

	private function getPluginTypes(): array
	{
		return array_combine(
			['block', 'ssi', 'editor', 'comment', 'parser', 'article', 'frontpage', 'impex', 'block_options', 'page_options', 'icons', 'seo', 'other'],
			$this->txt['lp_plugins_types']
		);
	}
}
