<?php declare(strict_types=1);

/**
 * @package BlogMode (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 03.12.24
 */

namespace Bugo\LightPortal\Plugins\BlogMode;

use Bugo\Bricks\Presenters\TablePresenter;
use Bugo\Bricks\Tables\Column;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Configs\BasicConfig;
use Bugo\LightPortal\Enums\Hook;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Repositories\PageRepository;
use Bugo\LightPortal\UI\Tables\NumViewsColumn;
use Bugo\LightPortal\UI\Tables\DateColumn;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\Str;

use function array_column;
use function array_keys;
use function array_merge;
use function array_search;
use function array_slice;
use function count;
use function sprintf;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class BlogMode extends Plugin
{
	public string $type = 'other';

	private string $blogAction = 'blog';

	private string $mode = 'blog_pages';

	public function __construct()
	{
		parent::__construct();

		$this->blogAction = $this->context['blog_action'] ?? $this->blogAction;
	}

	public function init(): void
	{
		if (empty(Utils::$context['allow_light_portal_view']))
			return;

		$this->applyHook(Hook::actions);
		$this->applyHook(Hook::menuButtons);
		$this->applyHook(Hook::currentAction);
		$this->applyHook(Hook::loadIllegalGuestPermissions);
		$this->applyHook(Hook::loadPermissions);

		Lang::$txt['group_perms_name_light_portal_post_blog_entries'] = $this->txt['permission'];

		if (empty(User::hasPermission('light_portal_post_blog_entries')))
			return;

		Utils::$context['lp_page_types'][$this->blogAction] = $this->txt['blogged_status'];

		if (empty($this->context['show_blogs_in_profiles']))
			return;

		$this->applyHook(Hook::profileAreas);
		$this->applyHook(Hook::profilePopup);
	}

	public function addSettings(Event $e): void
	{
		$this->addDefaultValues([
			'blog_action' => 'blog',
			'show_blogs_in_profiles' => false,
		]);

		$e->args->settings[$this->name][] = ['text', 'blog_action'];
		$e->args->settings[$this->name][] = ['check', 'show_blogs_in_profiles'];
	}

	public function frontModes(Event $e): void
	{
		if ($this->request()->isNot($this->blogAction))
			return;

		$e->args->modes[$this->mode] = BlogArticle::class;

		Config::$modSettings['lp_frontpage_mode'] = $this->mode;
	}

	public function extendBasicConfig(Event $e): void
	{
		Lang::$txt['groups_light_portal_post_blog_entries'] = $this->txt['group_permission'];
		Lang::$txt['permissionname_light_portal_post_blog_entries'] = $this->txt['permission'];

		$configVars = &$e->args->configVars;

		$key = array_search('light_portal_approve_pages', array_column($configVars, 1)) + 1;

		$configVars = array_merge(
			array_slice($configVars, 0, $key, true),
			[
				[
					'permissions',
					'light_portal_post_blog_entries',
					'tab' => BasicConfig::TAB_PERMISSIONS,
				],
			],
			array_slice($configVars, $key, count($configVars), true)
		);
	}

	public function actions(array &$actions): void
	{
		if ($this->blogAction === '')
			return;

		$actions[$this->blogAction] = [false, [new BlogIndex(), 'show']];
	}

	public function menuButtons(array &$buttons): void
	{
		if ($this->blogAction === '')
			return;

		$counter = 0;
		foreach (array_keys($buttons) as $area) {
			$counter++;

			if ($area === 'home')
				break;
		}

		$buttons = array_merge(
			array_slice($buttons, 0, $counter, true),
			[
				$this->blogAction => [
					'title'       => $this->txt['menu_item_title'],
					'href'        => Config::$scripturl . '?action=' . $this->blogAction,
					'icon'        => 'replies',
					'show'        => true,
					'action_hook' => true,
				],
			],
			array_slice($buttons, $counter, null, true)
		);
	}

	public function loadIllegalGuestPermissions(): void
	{
		Utils::$context['non_guest_permissions'] = array_merge(
			Utils::$context['non_guest_permissions'],
			[
				'light_portal_post_blog_entries',
			]
		);
	}

	public function currentAction(string &$action): void
	{
		if (empty(Utils::$context['lp_page']) || Utils::$context['lp_page']['entry_type'] !== BlogArticle::TYPE)
			return;

		$action = $this->blogAction;
	}

	public function loadPermissions(array &$permissionGroups, array &$permissionList): void
	{
		Lang::$txt['permissionname_light_portal_post_blog_entries']   = $this->txt['permission'];
		Lang::$txt['group_perms_name_light_portal_post_blog_entries'] = $this->txt['permission'];

		$permissionList['membergroup']['light_portal_post_blog_entries'] = [false, 'light_portal'];
	}

	public function profileAreas(array &$areas): void
	{
		$areas['info']['areas']['blogs'] = [
			'label'      => $this->txt['menu_item_title'],
			'function'   => self::class . '::showBlogEntries#',
			'icon'       => 'replies',
			'enabled'    => Utils::$context['allow_light_portal_view'],
			'permission' => [
				'own' => 'light_portal_manage_pages_own',
				'any' => ['profile_view', 'light_portal_view'],
			]
		];
	}

	public function showBlogEntries(int $memID): void
	{
		Utils::$context['current_member'] = $memID;

		Utils::$context['page_title'] = sprintf(
			$this->txt['profile_title'],
			' - ' . User::$profiles[$memID]['real_name']
		);

		$repository = new PageRepository();

		$params = [
			'AND p.author_id = {int:current_user} AND p.entry_type = {string:entry_type}',
			['current_user' => $memID, 'entry_type' => BlogArticle::TYPE],
		];

		$builder = PortalTableBuilder::make('user_blogs', $this->txt['entries'])
			->withParams(
				30,
				action: Config::$scripturl . '?action=profile;area=blogs;u=' . Utils::$context['current_member'],
				defaultSortColumn: 'date'
			)
			->setItems($repository->getAll(...), $params)
			->setCount($repository->getTotalCount(...), $params)
			->addColumns([
				IdColumn::make()->setSort('p.page_id'),
				DateColumn::make(),
				NumViewsColumn::make(),
				TitleColumn::make()
					->setData(static fn($entry) => Str::html('a', ['class' => 'bbc_link'])
						->href(LP_PAGE_URL . $entry['slug'])
						->setText($entry['title']), 'word_break'
					),
			]);

		Utils::$context['user']['is_owner'] && $builder->addColumn(
			Column::make('actions', Lang::$txt['lp_actions'])
				->setStyle('width: 8%')
				->setData(static fn($entry) => Str::html('a', ['class' => 'button'])
					->title(Lang::$txt['modify'])
					->href(Config::$scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $entry['id'])
					->setHtml(Str::html('span', ['class' => 'main_icons modify_button'])), 'centertext'),
		);

		TablePresenter::show($builder);
	}

	public function profilePopup(array &$items): void
	{
		$counter = 0;
		foreach ($items as $item) {
			$counter++;

			if ($item['area'] === 'showdrafts')
				break;
		}

		$items = array_merge(
			array_slice($items, 0, $counter, true),
			[
				[
					'menu'  => 'info',
					'area'  => 'blogs',
					'title' => $this->txt['menu_item_title'],
				]
			],
			array_slice($items, $counter, null, true)
		);
	}
}
