<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Actions\Block;
use LightPortal\Enums\Action;
use LightPortal\Enums\Permission;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasBreadcrumbs;

use function LightPortal\app;

use const LP_ACTION;
use const LP_PAGE_URL;

if (! defined('SMF'))
	die('No direct access...');

class MenuButtons
{
	use HasCommonChecks;
	use HasBreadcrumbs;

	public function __invoke(array &$buttons): void
	{
		if ($this->isPortalCanBeLoaded() === false)
			return;

		app(Block::class)->show();

		Theme::loadTemplate('LightPortal/ViewCustom');

		Utils::$context['template_layers'][] = 'custom';

		$this->prepareAdminButtons($buttons);
		$this->prepareModerationButtons($buttons);
		$this->preparePageButtons($buttons);
		$this->showDebugInfo();

		if (empty(Config::$modSettings['lp_frontpage_mode']))
			return;

		$this->preparePortalButtons($buttons);
		$this->fixCanonicalUrl();
		$this->fixLinktree();
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
							'amt'     => count(Setting::getEnabledPlugins()),
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
		if (! User::$me->allowedTo('light_portal_manage_pages_any'))
			return;

		$buttons['moderate']['show'] = true;

		$buttons['moderate']['sub_buttons'] = array_merge(
			[
				'lp_pages' => [
					'title' => Lang::$txt['lp_pages_unapproved'],
					'href'  => Config::$scripturl . '?action=admin;area=lp_pages;sa=main;moderate',
					'amt'   => Utils::$context['lp_quantities']['unapproved_pages'],
					'show'  => true,
				],
			],
			(array) $buttons['moderate']['sub_buttons']
		);
	}

	protected function preparePageButtons(array &$buttons): void
	{
		if (empty(Utils::$context['lp_menu_pages'] = app(PageRepositoryInterface::class)->getMenuItems()))
			return;

		$pageButtons = [];
		foreach (Utils::$context['lp_menu_pages'] as $item) {
			$pageButtons['portal_page_' . $item['slug']] = [
				'title' => (
					$item['icon']
						? Str::html('span', ['class' => 'portal_menu_icons ' . $item['icon']]) . ' '
						: ''
					) . $item['title'],
				'href'  => LP_PAGE_URL . $item['slug'],
				'icon'  => '" style="display: none"></span><span',
				'show'  => Permission::canViewItem($item['permissions']),
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
					'title'       => $this->getPageSubsectionTitle(),
					'href'        => Config::$modSettings['lp_menu_separate_subsection_href'] ?? Config::$scripturl,
					'icon'        => 'topics_replies',
					'show'        => User::$me->allowedTo('light_portal_view'),
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
				'icon'        => Action::HOME->value,
				'show'        => true,
				'action_hook' => true,
				'is_last'     => Utils::$context['right_to_left'],
			],
		], $buttons);

		// "Forum"
		$buttons[Action::HOME->value]['title'] = Lang::$txt['lp_forum'];
		$buttons[Action::HOME->value]['href']  = Config::$scripturl . '?action=forum';
		$buttons[Action::HOME->value]['icon']  = 'im_on';

		// Standalone mode
		if (empty(Config::$modSettings['lp_standalone_mode']))
			return;

		$buttons[LP_ACTION]['title']   = Lang::$txt['lp_portal'];
		$buttons[LP_ACTION]['href']    = Config::$modSettings['lp_standalone_url'] ?: Config::$scripturl;
		$buttons[LP_ACTION]['icon']    = Action::HOME->value;
		$buttons[LP_ACTION]['is_last'] = Utils::$context['right_to_left'];

		$buttons = array_merge(
			array_slice($buttons, 0, 2, true),
			[
				Action::FORUM->value => [
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

	protected function showDebugInfo(): void
	{
		if (
			empty(Config::$modSettings['lp_show_debug_info'])
			|| empty(Utils::$context['user']['is_admin'])
			|| empty(Utils::$context['template_layers'])
			|| $this->request()->is('devtools')
			|| $this->request()->is('xmlhttp')
			|| $this->request()->has('backtrace')
			|| $this->request()->has('file')
		) {
			return;
		}

		Utils::$context['lp_load_page_stats'] = Lang::getTxt('lp_load_page_stats', [
			Lang::getTxt('lp_seconds_set', [
				'seconds' => microtime(true) - Utils::$context['lp_load_time']
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

	protected function fixCanonicalUrl(): void
	{
		if ($this->request()->is(Action::FORUM->value)) {
			Utils::$context['canonical_url'] = Config::$scripturl . '?action=forum';
		}
	}

	protected function fixLinktree(): void
	{
		$linkTree = $this->breadcrumbs()->getByIndex(1);

		if (
			$this->request()->hasNot('c')
			&& empty(Utils::$context['current_board'])
			|| empty($linkTree)
			|| empty($linkTree['url'])
		) {
			return;
		}

		$oldUrl = explode('#', $linkTree['url']);

		if (empty($oldUrl[1]))
			return;

		$this->breadcrumbs()->update(1, 'url', Config::$scripturl . '?action=forum#' . $oldUrl[1]);
	}

	private function getPageSubsectionTitle(): string
	{
		if (empty($title = Config::$modSettings['lp_menu_separate_subsection_title'] ?? '')) {
			return Lang::tokenTxtReplace('{lp_pages}');
		}

		return Lang::tokenTxtReplace($title);
	}
}
