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
 * @version 2.6
 */

namespace Bugo\LightPortal;

use Bugo\Compat\{Config, Lang, Theme, User, Utils};
use Bugo\LightPortal\Actions\Block;
use Bugo\LightPortal\Areas\{ConfigArea, CreditArea};
use Bugo\LightPortal\Compilers\CompilerInterface;
use Bugo\LightPortal\Repositories\PageRepository;
use Bugo\LightPortal\Utils\SessionManager;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractMain
{
	use Helper;

	public function __construct()
	{
		(new ConfigArea())();
		(new CreditArea())();
	}

	protected function isPortalCanBeLoaded(): bool
	{
		if (
			! defined('LP_NAME')
			|| isset(Utils::$context['uninstalling'])
			|| $this->request()->is('printpage')
		) {
			Config::$modSettings['minimize_files'] = 0;
			return false;
		}

		return true;
	}

	protected function defineVars(): void
	{
		Utils::$context['allow_light_portal_view']             = User::hasPermission('light_portal_view');
		Utils::$context['allow_light_portal_manage_pages_own'] = User::hasPermission('light_portal_manage_pages_own');
		Utils::$context['allow_light_portal_manage_pages_any'] = User::hasPermission('light_portal_manage_pages_any');
		Utils::$context['allow_light_portal_approve_pages']    = User::hasPermission('light_portal_approve_pages');

		$this->calculateNumberOfEntities();

		Utils::$context['lp_all_title_classes']   = $this->getTitleClasses();
		Utils::$context['lp_all_content_classes'] = $this->getContentClasses();
		Utils::$context['lp_block_placements']    = $this->getBlockPlacements();
		Utils::$context['lp_plugin_types']        = $this->getPluginTypes();
		Utils::$context['lp_content_types']       = $this->getContentTypes();

		Utils::$context['lp_enabled_plugins'] = empty(Config::$modSettings['lp_enabled_plugins'])
			? [] : explode(',', Config::$modSettings['lp_enabled_plugins']);

		Utils::$context['lp_frontpage_pages'] = empty(Config::$modSettings['lp_frontpage_pages'])
			? [] : explode(',', Config::$modSettings['lp_frontpage_pages']);

		Utils::$context['lp_frontpage_topics'] = empty(Config::$modSettings['lp_frontpage_topics'])
			? [] : explode(',', Config::$modSettings['lp_frontpage_topics']);

		Utils::$context['lp_header_panel_width'] = empty(Config::$modSettings['lp_header_panel_width'])
			? 12 : (int) Config::$modSettings['lp_header_panel_width'];

		Utils::$context['lp_left_panel_width'] = empty(Config::$modSettings['lp_left_panel_width'])
			? ['lg' => 3, 'xl' => 2]
			: Utils::jsonDecode(Config::$modSettings['lp_left_panel_width'], true);

		Utils::$context['lp_right_panel_width'] = empty(Config::$modSettings['lp_right_panel_width'])
			? ['lg' => 3, 'xl' => 2]
			: Utils::jsonDecode(Config::$modSettings['lp_right_panel_width'], true);

		Utils::$context['lp_footer_panel_width'] = empty(Config::$modSettings['lp_footer_panel_width'])
			? 12 : (int) Config::$modSettings['lp_footer_panel_width'];

		Utils::$context['lp_swap_left_right'] = empty(Lang::$txt['lang_rtl'])
			? ! empty(Config::$modSettings['lp_swap_left_right'])
			: empty(Config::$modSettings['lp_swap_left_right']);

		Utils::$context['lp_panel_direction'] = Utils::jsonDecode(
			Config::$modSettings['lp_panel_direction'] ?? '', true
		);

		Utils::$context['lp_active_blocks'] = (new Block())->getActive();
	}

	protected function loadAssets(CompilerInterface $compiler): void
	{
		$this->loadFontAwesome();

		$compiler->compile();

		Theme::loadCSSFile('light_portal/flexboxgrid.css');
		Theme::loadCSSFile('light_portal/portal.css');
		Theme::loadCSSFile('light_portal/plugins.css');
		Theme::loadCSSFile('portal_custom.css');

		Theme::loadJavaScriptFile('light_portal/plugins.js', ['minimize' => true]);
	}

	protected function loadFontAwesome(): void
	{
		if (empty(Config::$modSettings['lp_fa_source']) || Config::$modSettings['lp_fa_source'] === 'none')
			return;

		if (Config::$modSettings['lp_fa_source'] === 'css_local') {
			Theme::loadCSSFile('all.min.css', [], 'portal_fontawesome');
		} elseif (Config::$modSettings['lp_fa_source'] === 'custom' && isset(Config::$modSettings['lp_fa_custom'])) {
			Theme::loadCSSFile(
				Config::$modSettings['lp_fa_custom'],
				[
					'external' => true,
					'seed'     => false,
				],
				'portal_fontawesome'
			);
		} elseif (isset(Config::$modSettings['lp_fa_kit'])) {
			Theme::loadJavaScriptFile(
				Config::$modSettings['lp_fa_kit'],
				[
					'attributes' => ['crossorigin' => 'anonymous'],
					'external'   => true,
				]
			);
		}
	}

	/**
	 * Remove unnecessary areas for the standalone mode
	 *
	 * Удаляем ненужные в автономном режиме области
	 */
	protected function unsetDisabledActions(array &$data): void
	{
		$disabledActions = array_flip($this->getDisabledActions());

		foreach (array_keys($data) as $action) {
			if (array_key_exists($action, $disabledActions))
				unset($data[$action]);
		}

		if (array_key_exists('search', $disabledActions))
			Utils::$context['allow_search'] = false;

		if (array_key_exists('moderate', $disabledActions))
			Utils::$context['allow_moderation_center'] = false;

		if (array_key_exists('calendar', $disabledActions))
			Utils::$context['allow_calendar'] = false;

		if (array_key_exists('mlist', $disabledActions))
			Utils::$context['allow_memberlist'] = false;

		Utils::$context['lp_disabled_actions'] = $disabledActions;
	}

	protected function redirectFromDisabledActions(): void
	{
		if (empty(Utils::$context['current_action']))
			return;

		if (array_key_exists(Utils::$context['current_action'], Utils::$context['lp_disabled_actions'])) {
			Utils::redirectexit();
		}
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
		if (
			$this->request()->hasNot('c')
			&& empty(Utils::$context['current_board'])
			|| empty(Utils::$context['linktree'][1])
			|| empty(Utils::$context['linktree'][1]['url'])
		) {
			return;
		}

		$oldUrl = explode('#', Utils::$context['linktree'][1]['url']);

		if (empty($oldUrl[1]))
			return;

		Utils::$context['linktree'][1]['url'] = Config::$scripturl . '?action=forum#' . $oldUrl[1];
	}

	/**
	 * Show the script execution time and the number of the portal queries
	 *
	 * Отображаем время выполнения скрипта и количество запросов к базе
	 */
	protected function showDebugInfo(): void
	{
		if (
			empty(Config::$modSettings['lp_show_debug_info'])
			|| empty(Utils::$context['user']['is_admin'])
			|| empty(Utils::$context['template_layers'])
			|| $this->request()->is('devtools')
		) {
			return;
		}

		Utils::$context['lp_load_page_stats'] = Lang::getTxt('lp_load_page_stats', [
			Lang::getTxt('lp_seconds_set', [
				'seconds' => round(microtime(true) - Utils::$context['lp_load_time'], 3)
			]),
		]);

		Theme::loadTemplate('LightPortal/ViewDebug');

		if (empty($key = array_search('lp_portal', Utils::$context['template_layers'], true))) {
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
						'categories'   => [
							'title' => Lang::$txt['lp_categories'],
							'href'  => Config::$scripturl . '?action=admin;area=lp_categories',
							'amt'   => Utils::$context['lp_quantities']['active_categories'],
							'show'  => true,
						],
						'tags'   => [
							'title' => Lang::$txt['lp_tags'],
							'href'  => Config::$scripturl . '?action=admin;area=lp_tags',
							'amt'   => Utils::$context['lp_quantities']['active_tags'],
							'show'  => true,
						],
						'plugins' => [
							'title'   => Lang::$txt['lp_plugins'],
							'href'    => Config::$scripturl . '?action=admin;area=lp_plugins',
							'amt'     => Utils::$context['lp_enabled_plugins']
								? count(Utils::$context['lp_enabled_plugins']) : 0,
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
		if (empty(Utils::$context['lp_menu_pages'] = (new PageRepository())->getMenuItems()))
			return;

		$pageButtons = [];
		foreach (Utils::$context['lp_menu_pages'] as $item) {
			$pageButtons['portal_page_' . $item['slug']] = [
				'title' => (
					$item['icon']
						? '<span class="portal_menu_icons fa-fw ' . $item['icon'] . '"></span>'
						: ''
					) . $this->getTranslatedTitle($item['titles']),
				'href'  => LP_PAGE_URL . $item['slug'],
				'icon'  => '" style="display: none"></span><span',
				'show'  => $this->canViewItem($item['permissions']),
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
			empty(Config::$modSettings['lp_menu_separate_subsection']) ? $pageButtons : [
				'lp_pages' => [
					'title' => $this->getPageSubsectionTitle(),
					'href'  => Config::$modSettings['lp_menu_separate_subsection_href'] ?? Config::$scripturl,
					'icon'  => 'topics_replies',
					'show'  => Utils::$context['allow_light_portal_view'],
					'sub_buttons' => $pageButtons,
				]
			],
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
				'is_last'     => Utils::$context['right_to_left'],
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
					'href'        => Config::$modSettings['lp_standalone_url']
						? Config::$scripturl : Config::$scripturl . '?action=forum',
					'icon'        => 'im_on',
					'show'        => true,
					'action_hook' => true,
				],
			],
			array_slice($buttons, 2, null, true)
		);

		$this->unsetDisabledActions($buttons);
	}

	protected function getDisabledActions(): array
	{
		$disabledActions = empty(Config::$modSettings['lp_disabled_actions'])
			? [] : explode(',', Config::$modSettings['lp_disabled_actions']);

		$disabledActions[] = 'home';

		return $disabledActions;
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

		Config::updateModSettings(
			['lp_frontpage_topics' => implode(',', Utils::$context['lp_frontpage_topics'])]
		);

		Utils::redirectexit('topic=' . $topic);
	}

	private function calculateNumberOfEntities(): void
	{
		$sessionManager = new SessionManager();

		$entities = [
			'active_blocks', 'active_pages', 'my_pages', 'unapproved_pages',
			'internal_pages', 'active_categories', 'active_tags',
		];

		Utils::$context['lp_quantities'] = array_map(
			static fn($key) => $sessionManager($key), array_combine($entities, $entities)
		);
	}

	private function getBlockPlacements(): array
	{
		return array_combine(
			['header', 'top', 'left', 'right', 'bottom', 'footer'],
			Lang::$txt['lp_block_placement_set']
		);
	}

	private function getPluginTypes(): array
	{
		return array_combine(
			[
				'block', 'ssi', 'editor', 'comment', 'parser', 'article', 'frontpage',
				'impex', 'block_options', 'page_options', 'icons', 'seo', 'other',
			],
			Lang::$txt['lp_plugins_types']
		);
	}

	private function getPageSubsectionTitle(): string
	{
		if (empty($title = Config::$modSettings['lp_menu_separate_subsection_title'] ?? ''))
			return Lang::tokenTxtReplace('{lp_pages}');

		return Lang::tokenTxtReplace($title);
	}
}
