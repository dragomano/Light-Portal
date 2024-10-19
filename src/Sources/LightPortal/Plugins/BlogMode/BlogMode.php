<?php declare(strict_types=1);

/**
 * @package BlogMode (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 18.10.24
 */

namespace Bugo\LightPortal\Plugins\BlogMode;

use Bugo\Compat\{Config, Lang, User, Utils};
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Enums\{Hook, Status, Tab};
use Bugo\LightPortal\Areas\Partials\EntryTypeSelect;
use Bugo\LightPortal\Repositories\PageRepository;
use Bugo\LightPortal\Utils\{Icon, ItemList};
use Nette\Utils\Html;

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
		$this->blogAction = Utils::$context['lp_blog_mode_plugin']['blog_action'] ?? $this->blogAction;

		if (Utils::$context['user']['is_admin'] === false) {
			unset(Utils::$context['lp_page_types']['internal']);
		}
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

		if (empty(User::hasPermission('light_portal_post_blog_entries')))
			return;

		Utils::$context['lp_page_types'][$this->blogAction] = Lang::$txt['lp_blog_mode']['blogged_status'];

		if (empty(Utils::$context['lp_blog_mode_plugin']['show_blogs_in_profiles']))
			return;

		$this->applyHook(Hook::profileAreas);
		$this->applyHook(Hook::profilePopup);
	}

	public function addSettings(array &$settings): void
	{
		$this->addDefaultValues([
			'blog_action' => 'blog',
			'show_blogs_in_profiles' => false,
		]);

		$settings['blog_mode'][] = ['text', 'blog_action'];
		$settings['blog_mode'][] = ['check', 'show_blogs_in_profiles'];
	}

	public function preparePageFields(): void
	{
		if (Utils::$context['user']['is_admin'] || empty(User::hasPermission('light_portal_post_blog_entries')))
			return;

		CustomField::make('entry_type', Lang::$txt['lp_page_type'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new EntryTypeSelect());
	}

	public function frontModes(array &$modes): void
	{
		if ($this->request()->isNot($this->blogAction))
			return;

		$modes[$this->mode] = BlogArticle::class;

		Config::$modSettings['lp_frontpage_mode'] = $this->mode;
	}

	public function extendBasicConfig(&$configVars): void
	{
		Lang::$txt['groups_light_portal_post_blog_entries'] = Lang::$txt['lp_blog_mode']['group_permission'];

		$key = array_search('light_portal_approve_pages', array_column($configVars, 1)) + 1;

		$configVars = array_merge(
			array_slice($configVars, 0, $key, true),
			[
				['permissions', 'light_portal_post_blog_entries'],
			],
			array_slice($configVars, $key, count($configVars), true)
		);
	}

	public function actions(&$actions): void
	{
		if ($this->blogAction === '')
			return;

		$actions[$this->blogAction] = [false, [new BlogIndex(), 'show']];
	}

	public function menuButtons(&$buttons): void
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
					'title'       => Lang::$txt['lp_blog_mode']['menu_item_title'],
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
		Lang::$txt['permissionname_light_portal_post_blog_entries']   = Lang::$txt['lp_blog_mode']['permission'];
		Lang::$txt['group_perms_name_light_portal_post_blog_entries'] = Lang::$txt['lp_blog_mode']['permission'];

		$permissionList['membergroup']['light_portal_post_blog_entries'] = [false, 'light_portal'];
	}

	public function profileAreas(array &$areas): void
	{
		$areas['info']['areas']['blogs'] = [
			'label'      => Lang::$txt['lp_blog_mode']['menu_item_title'],
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
			Lang::$txt['lp_blog_mode']['profile_title'],
			' - ' . User::$profiles[$memID]['real_name']
		);

		$repository = new PageRepository();

		$params = [
			'AND p.author_id = {int:current_user} AND p.entry_type = {string:entry_type}',
			['current_user' => $memID, 'entry_type' => BlogArticle::TYPE],
		];

		$listOptions = [
			'id' => 'user_blogs',
			'items_per_page' => 30,
			'title' => Lang::$txt['lp_blog_mode']['entries'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Config::$scripturl . '?action=profile;area=blogs;u=' . Utils::$context['current_member'],
			'default_sort_col' => 'date',
			'get_items' => [
				'function' => $repository->getAll(...),
				'params'   => $params,
			],
			'get_count' => [
				'function' => $repository->getTotalCount(...),
				'params'   => $params,
			],
			'columns' => [
				'id' => [
					'header' => [
						'value' => '#',
						'style' => 'width: 5%',
					],
					'data' => [
						'db' => 'id',
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'p.page_id',
						'reverse' => 'p.page_id DESC',
					],
				],
				'date' => [
					'header' => [
						'value' => Lang::$txt['date'],
					],
					'data' => [
						'db' => 'created_at',
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'date DESC',
						'reverse' => 'date',
					],
				],
				'num_views' => [
					'header' => [
						'value' => Icon::get('views', Lang::$txt['lp_views'])
					],
					'data' => [
						'db' => 'num_views',
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'p.num_views DESC',
						'reverse' => 'p.num_views',
					],
				],
				'title' => [
					'header' => [
						'value' => Lang::$txt['lp_title'],
					],
					'data' => [
						'function' => static fn($entry) => Html::el('a', ['class' => 'bbc_link'])
							->href(LP_PAGE_URL . $entry['slug'])
							->setText($entry['title'])
							->toHtml(),
						'class' => 'word_break',
					],
					'sort' => [
						'default' => 't.value DESC',
						'reverse' => 't.value',
					],
				],
			],
		];

		if (Utils::$context['user']['is_owner']) {
			$listOptions['columns']['actions'] = [
				'header' => [
					'value' => Lang::$txt['lp_actions'],
					'style' => 'width: 8%',
				],
				'data' => [
					'function' => static fn($entry) => Html::el('a', ['class' => 'button'])
						->title(Lang::$txt['modify'])
						->href(Config::$scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $entry['id'])
						->setHtml(Html::el('span', ['class' => 'main_icons modify_button']))
						->toHtml(),
					'class' => 'centertext',
				],
			];
		}

		new ItemList($listOptions);
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
					'title' => Lang::$txt['lp_blog_mode']['menu_item_title'],
				]
			],
			array_slice($items, $counter, null, true)
		);
	}
}
