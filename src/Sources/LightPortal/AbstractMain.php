<?php declare(strict_types=1);

/**
 * AbstractMain.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Actions\{Block, PageInterface};
use Bugo\LightPortal\Utils\{Config, ErrorHandler, Lang, Theme, User, Utils};
use Exception;
use Less_Exception_Parser;
use Less_Parser;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractMain
{
	use Helper;

	protected function isPortalCanBeLoaded(): bool
	{
		if (! defined('LP_NAME') || isset(Utils::$context['uninstalling']) || $this->request()->is('printpage')) {
			Config::$modSettings['minimize_files'] = 0;
			return false;
		}

		return true;
	}

	protected function defineVars(): void
	{
		Utils::$context['allow_light_portal_view']             = $this->allowedTo('light_portal_view');
		Utils::$context['allow_light_portal_manage_pages_own'] = $this->allowedTo('light_portal_manage_pages_own');
		Utils::$context['allow_light_portal_manage_pages_any'] = $this->allowedTo('light_portal_manage_pages_any');
		Utils::$context['allow_light_portal_approve_pages']    = $this->allowedTo('light_portal_approve_pages');

		$this->calculateNumberOfEntities();

		Utils::$context['lp_all_title_classes']   = $this->getTitleClasses();
		Utils::$context['lp_all_content_classes'] = $this->getContentClasses();
		Utils::$context['lp_block_placements']    = $this->getBlockPlacements();
		Utils::$context['lp_plugin_types']        = $this->getPluginTypes();
		Utils::$context['lp_content_types']       = $this->getContentTypes();

		Utils::$context['lp_enabled_plugins']  = empty(Config::$modSettings['lp_enabled_plugins'])  ? [] : explode(',', Config::$modSettings['lp_enabled_plugins']);
		Utils::$context['lp_frontpage_pages']  = empty(Config::$modSettings['lp_frontpage_pages'])  ? [] : explode(',', Config::$modSettings['lp_frontpage_pages']);
		Utils::$context['lp_frontpage_topics'] = empty(Config::$modSettings['lp_frontpage_topics']) ? [] : explode(',', Config::$modSettings['lp_frontpage_topics']);

		Utils::$context['lp_header_panel_width'] = empty(Config::$modSettings['lp_header_panel_width']) ? 12 : (int) Config::$modSettings['lp_header_panel_width'];
		Utils::$context['lp_left_panel_width']   = empty(Config::$modSettings['lp_left_panel_width'])   ? ['lg' => 3, 'xl' => 2] : Utils::jsonDecode(Config::$modSettings['lp_left_panel_width'], true);
		Utils::$context['lp_right_panel_width']  = empty(Config::$modSettings['lp_right_panel_width'])  ? ['lg' => 3, 'xl' => 2] : Utils::jsonDecode(Config::$modSettings['lp_right_panel_width'], true);
		Utils::$context['lp_footer_panel_width'] = empty(Config::$modSettings['lp_footer_panel_width']) ? 12 : (int) Config::$modSettings['lp_footer_panel_width'];

		Utils::$context['lp_swap_left_right'] = empty(Lang::$txt['lang_rtl']) ? ! empty(Config::$modSettings['lp_swap_left_right']) : empty(Config::$modSettings['lp_swap_left_right']);
		Utils::$context['lp_panel_direction'] = Utils::jsonDecode(Config::$modSettings['lp_panel_direction'] ?? '', true);

		Utils::$context['lp_active_blocks'] = (new Block)->getActive();

		Utils::$context['lp_icon_set'] = $this->getEntityList('icon');
	}

	protected function loadAssets(): void
	{
		$this->loadFontAwesome();
		$this->compileLess();

		Theme::loadCSSFile('light_portal/flexboxgrid.css');
		Theme::loadCSSFile('light_portal/portal.css');
		Theme::loadCSSFile('light_portal/plugins.css');
		Theme::loadCSSFile('portal_custom.css');

		Theme::loadJSFile('light_portal/plugins.js', ['minimize' => true]);
	}

	protected function loadFontAwesome(): void
	{
		if (empty(Config::$modSettings['lp_fa_source']) || Config::$modSettings['lp_fa_source'] === 'none')
			return;

		if (Config::$modSettings['lp_fa_source'] === 'css_local') {
			Theme::loadCSSFile('all.min.css', [], 'portal_fontawesome');
		} elseif (Config::$modSettings['lp_fa_source'] === 'custom' && isset(Config::$modSettings['lp_fa_custom'])) {
			Theme::loadExtCSS(
				Config::$modSettings['lp_fa_custom'],
				['seed' => false],
				'portal_fontawesome'
			);
		} elseif (isset(Config::$modSettings['lp_fa_kit'])) {
			Theme::loadExtJS(Config::$modSettings['lp_fa_kit'], ['attributes' => ['crossorigin' => 'anonymous']]);
		}
	}

	protected function compileLess(): void
	{
		$cssFile  = Theme::$current->settings['default_theme_dir'] . '/css/light_portal/portal.css';
		$lessFile = Theme::$current->settings['default_theme_dir'] . '/css/light_portal/less/portal.less';

		if (! is_file($lessFile)) return;

		if (is_file($cssFile) && filemtime($lessFile) < filemtime($cssFile))
			return;

		$parser = new Less_Parser([
			'compress'  => true,
			'cache_dir' => empty(Config::$modSettings['cache_enable']) ? null : sys_get_temp_dir(),
		]);

		try {
			$parser->parseFile($lessFile);
			file_put_contents($cssFile, $parser->getCss());
		} catch (Less_Exception_Parser | Exception $e) {
			ErrorHandler::log($e->getMessage(), 'critical');
		}
	}

	/**
	 * Remove unnecessary areas for the standalone mode
	 *
	 * Удаляем ненужные в автономном режиме области
	 */
	protected function unsetDisabledActions(array &$data): void
	{
		$disabled_actions = empty(Config::$modSettings['lp_disabled_actions']) ? [] : explode(',', Config::$modSettings['lp_disabled_actions']);
		$disabled_actions[] = 'home';
		$disabled_actions = array_flip($disabled_actions);

		foreach (array_keys($data) as $action) {
			if (array_key_exists($action, $disabled_actions))
				unset($data[$action]);
		}

		if (array_key_exists('search', $disabled_actions))
			Utils::$context['allow_search'] = false;

		if (array_key_exists('moderate', $disabled_actions))
			Utils::$context['allow_moderation_center'] = false;

		if (array_key_exists('calendar', $disabled_actions))
			Utils::$context['allow_calendar'] = false;

		if (array_key_exists('mlist', $disabled_actions))
			Utils::$context['allow_memberlist'] = false;

		Utils::$context['lp_disabled_actions'] = $disabled_actions;
	}

	/**
	 * Fix canonical url for forum action
	 *
	 * Исправляем канонический адрес для области forum
	 */
	protected function fixCanonicalUrl(): void
	{
		if ($this->request()->is('forum'))
			Utils::$context['canonical_url'] = Config::$scripturl . '?action=forum';
	}

	/**
	 * Change the link tree
	 *
	 * Меняем дерево ссылок
	 */
	protected function fixLinktree(): void
	{
		if (empty(Utils::$context['current_board']) && $this->request()->hasNot('c') || empty(Utils::$context['linktree'][1]) || empty(Utils::$context['linktree'][1]['url']))
			return;

		$old_url = explode('#', Utils::$context['linktree'][1]['url']);

		if (! empty($old_url[1]))
			Utils::$context['linktree'][1]['url'] = Config::$scripturl . '?action=forum#' . $old_url[1];
	}

	/**
	 * Show the script execution time and the number of the portal queries
	 *
	 * Отображаем время выполнения скрипта и количество запросов к базе
	 */
	protected function showDebugInfo(): void
	{
		if (empty(Config::$modSettings['lp_show_debug_info']) || empty(Utils::$context['user']['is_admin']) || empty(Utils::$context['template_layers']) || $this->request()->is('devtools'))
			return;

		Utils::$context['lp_load_page_stats'] = sprintf(Lang::$txt['lp_load_page_stats'], round(microtime(true) - Utils::$context['lp_load_time'], 3), Utils::$context['lp_num_queries']);

		Theme::loadTemplate('LightPortal/ViewDebug');

		if (empty($key = array_search('lp_portal', Utils::$context['template_layers']))) {
			Utils::$context['template_layers'][] = 'debug';
			return;
		}

		Utils::$context['template_layers'] = array_merge(
			array_slice(Utils::$context['template_layers'], 0, $key, true),
			['debug'],
			array_slice(Utils::$context['template_layers'], $key, null, true)
		);
	}

	/**
	 * Display "Portal settings" in Main Menu => Admin
	 *
	 * Отображаем "Настройки портала" в Главном меню => Админка
	 */
	protected function prepareAdminButtons(array &$buttons): void
	{
		if (Utils::$context['user']['is_admin'] === false)
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
					'title'       => Lang::$txt['lp_settings'],
					'href'        => Config::$scripturl . '?action=admin;area=lp_settings',
					'show'        => true,
					'sub_buttons' => [
						'blocks'  => [
							'title' => Lang::$txt['lp_blocks'],
							'href'  => Config::$scripturl . '?action=admin;area=lp_blocks',
							'amt'   => Utils::$context['lp_quantities']['active_blocks'],
							'show'  => true,
						],
						'pages'   => [
							'title' => Lang::$txt['lp_pages'],
							'href'  => Config::$scripturl . '?action=admin;area=lp_pages',
							'amt'   => Utils::$context['lp_quantities']['active_pages'],
							'show'  => true,
						],
						'plugins' => [
							'title'   => Lang::$txt['lp_plugins'],
							'href'    => Config::$scripturl . '?action=admin;area=lp_plugins',
							'amt'     => Utils::$context['lp_enabled_plugins'] ? count(Utils::$context['lp_enabled_plugins']) : 0,
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
		if (Utils::$context['allow_light_portal_manage_pages_any'] === false)
			return;

		$buttons['moderate']['show'] = true;

		$buttons['moderate']['sub_buttons'] = [
			'lp_pages' => [
				'title' => Lang::$txt['lp_pages_unapproved'],
				'href'  => Config::$scripturl . '?action=admin;area=lp_pages;sa=main;moderate',
				'amt'   => Utils::$context['lp_quantities']['unapproved_pages'],
				'show'  => true,
			],
		] + $buttons['moderate']['sub_buttons'];
	}

	protected function preparePageButtons(array &$buttons): void
	{
		if (empty(Utils::$context['lp_menu_pages'] = $this->getMenuPages()))
			return;

		$page_buttons = [];
		foreach (Utils::$context['lp_menu_pages'] as $item) {
			$page_buttons['portal_page_' . $item['alias']] = [
				'title' => ($item['icon'] ? '<span class="portal_menu_icons fa-fw ' . $item['icon'] . '"></span>' : '') . $this->getTranslatedTitle($item['titles']),
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
				'title'       => Lang::$txt['lp_portal'],
				'href'        => Config::$scripturl,
				'icon'        => 'home',
				'show'        => true,
				'action_hook' => true,
				'is_last'     => Utils::$context['right_to_left']
			],
		], $buttons);

		// "Forum"
		$buttons['home']['title'] = Lang::$txt['lp_forum'];
		$buttons['home']['href']  = Config::$scripturl . '?action=forum';
		$buttons['home']['icon']  = 'im_on';

		// Standalone mode
		if (empty(Config::$modSettings['lp_standalone_mode']))
			return;

		$buttons[LP_ACTION]['title']   = Lang::$txt['lp_portal'];
		$buttons[LP_ACTION]['href']    = Config::$modSettings['lp_standalone_url'] ?: Config::$scripturl;
		$buttons[LP_ACTION]['icon']    = 'home';
		$buttons[LP_ACTION]['is_last'] = Utils::$context['right_to_left'];

		$buttons = array_merge(
			array_slice($buttons, 0, 2, true),
			[
				'forum' => [
					'title'       => Lang::$txt['lp_forum'],
					'href'        => Config::$modSettings['lp_standalone_url'] ? Config::$scripturl : Config::$scripturl . '?action=forum',
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
		if (empty(User::$info['is_admin']) || $this->request()->hasNot('t'))
			return;

		$topic = $this->request('t');

		if (($key = array_search($topic, Utils::$context['lp_frontpage_topics'])) !== false) {
			unset(Utils::$context['lp_frontpage_topics'][$key]);
		} else {
			Utils::$context['lp_frontpage_topics'][] = $topic;
		}

		Config::updateModSettings(['lp_frontpage_topics' => implode(',', Utils::$context['lp_frontpage_topics'])]);

		Utils::redirectexit('topic=' . $topic);
	}

	private function getMenuPages(): array
	{
		if (($pages = $this->cache()->get('menu_pages')) === null) {
			$titles = $this->getEntityList('title');

			$result = Utils::$smcFunc['db_query']('', '
				SELECT p.page_id, p.alias, p.permissions, pp2.value AS icon
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {literal:page})
					LEFT JOIN {db_prefix}lp_params AS pp2 ON (p.page_id = pp2.item_id AND pp2.type = {literal:page} AND pp2.name = {literal:page_icon})
				WHERE p.status IN ({array_int:statuses})
					AND p.created_at <= {int:current_time}
					AND pp.name = {literal:show_in_menu}
					AND pp.value = {string:show_in_menu}',
				[
					'statuses'     => [PageInterface::STATUS_ACTIVE, PageInterface::STATUS_INTERNAL],
					'current_time' => time(),
					'show_in_menu' => '1',
				]
			);

			$pages = [];
			while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
				$pages[$row['page_id']] = [
					'id'          => $row['page_id'],
					'alias'       => $row['alias'],
					'permissions' => (int) $row['permissions'],
					'title'       => [],
					'icon'        => $row['icon'],
				];

				$pages[$row['page_id']]['titles'] = $titles[$row['page_id']];
			}

			Utils::$smcFunc['db_free_result']($result);
			Utils::$context['lp_num_queries']++;

			$this->cache()->put('menu_pages', $pages);
		}

		return $pages;
	}

	private function calculateNumberOfEntities(): void
	{
		if (($num_entities = $this->cache()->get('num_active_entities_u' . User::$info['id'])) === null) {
			$result = Utils::$smcFunc['db_query']('', '
				SELECT
					(
						SELECT COUNT(b.block_id)
						FROM {db_prefix}lp_blocks b
						WHERE b.status = {int:active}
					) AS num_blocks,
					(
						SELECT COUNT(p.page_id)
						FROM {db_prefix}lp_pages p
						WHERE p.status = {int:active}' . (Utils::$context['allow_light_portal_manage_pages_any'] ? '' : '
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
					'active'     => PageInterface::STATUS_ACTIVE,
					'unapproved' => PageInterface::STATUS_UNAPPROVED,
					'internal'   => PageInterface::STATUS_INTERNAL,
					'user_id'    => User::$info['id'],
				]
			);

			$num_entities = Utils::$smcFunc['db_fetch_assoc']($result);
			array_walk($num_entities, fn(&$item) => $item = (int) $item);

			Utils::$smcFunc['db_free_result']($result);
			Utils::$context['lp_num_queries']++;

			$this->cache()->put('num_active_entities_u' . User::$info['id'], $num_entities);
		}

		Utils::$context['lp_quantities'] = [
			'active_blocks'    => $num_entities['num_blocks'],
			'active_pages'     => $num_entities['num_pages'],
			'my_pages'         => $num_entities['num_my_pages'],
			'unapproved_pages' => $num_entities['num_unapproved_pages'],
			'internal_pages'   => $num_entities['num_internal_pages'],
		];
	}

	private function getBlockPlacements(): array
	{
		return array_combine(['header', 'top', 'left', 'right', 'bottom', 'footer'], Lang::$txt['lp_block_placement_set']);
	}

	private function getPluginTypes(): array
	{
		return array_combine(
			['block', 'ssi', 'editor', 'comment', 'parser', 'article', 'frontpage', 'impex', 'block_options', 'page_options', 'icons', 'seo', 'other'],
			Lang::$txt['lp_plugins_types']
		);
	}
}
