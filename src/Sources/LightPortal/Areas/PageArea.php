<?php declare(strict_types=1);

/**
 * PageArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas;

use Bugo\LightPortal\Actions\{Page, PageInterface};
use Bugo\LightPortal\Areas\Fields\{CheckboxField, CustomField, TextareaField, TextField};
use Bugo\LightPortal\Areas\Partials\{CategorySelect, KeywordSelect, PageAuthorSelect};
use Bugo\LightPortal\Areas\Partials\{PageIconSelect, PermissionSelect, StatusSelect};
use Bugo\LightPortal\Areas\Validators\PageValidator;
use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Models\PageModel;
use Bugo\LightPortal\Repositories\PageRepository;
use Bugo\LightPortal\Utils\{Config, Content, ErrorHandler, DateTime};
use Bugo\LightPortal\Utils\{Icon, Lang, Theme, User, Utils};
use IntlException;

if (! defined('SMF'))
	die('No direct access...');

final class PageArea
{
	use Area, Helper;

	private PageRepository $repository;

	public function __construct()
	{
		$this->repository = new PageRepository;
	}

	public function main(): void
	{
		$this->checkUser();

		Lang::load('Packages');

		if ($this->request()->has('moderate'))
			Theme::addInlineCss('
		#lp_pages .num_views, #lp_pages .num_comments {
			display: none;
		}');

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_pages_manage'];

		$menu = Utils::$context['admin_menu_name'];
		$tabs = [];

		$tabs['title'] = LP_NAME;
		$tabs['description'] = Lang::$txt['lp_pages_manage_' . (Utils::$context['allow_light_portal_manage_pages_any'] && $this->request()->hasNot('u') ? 'all' : 'own') . '_pages'] . ' ' . Lang::$txt['lp_pages_manage_description'];

		if ($this->request()->has('moderate')) {
			$tabs['description'] = Lang::$txt['lp_pages_unapproved_description'];
		}

		if ($this->request()->has('internal')) {
			$tabs['description'] = Lang::$txt['lp_pages_internal_description'];
		}

		Utils::$context[$menu]['tab_data'] = $tabs;

		$this->doActions();
		$this->massActions();

		$search_params_string = trim($this->request('search', ''));
		$search_params = [
			'string' => Utils::$smcFunc['htmlspecialchars']($search_params_string),
		];

		Utils::$context['search_params'] = empty($search_params_string) ? '' : base64_encode(Utils::$smcFunc['json_encode']($search_params));
		Utils::$context['search'] = [
			'string' => $search_params['string'],
		];

		$params = [
			(
				empty($search_params['string']) ? '' : ' AND (INSTR(LOWER(p.alias), {string:search}) > 0 OR INSTR(LOWER(t.title), {string:search}) > 0)'
			) . (
				$this->request()->has('u') ? ' AND p.author_id = {int:user_id}' : ''
			) . (
				$this->request()->has('moderate') ? ' AND p.status = {int:unapproved}' : ''
			) . (
				$this->request()->has('internal') ? ' AND p.status = {int:internal}' : ''
			) . (
				$this->request()->hasNot('u') && $this->request()->hasNot('moderate') && $this->request()->hasNot('internal') ? ' AND p.status IN ({array_int:included_statuses})' : ''
			),
			[
				'search'            => Utils::$smcFunc['strtolower']($search_params['string']),
				'unapproved'        => PageInterface::STATUS_UNAPPROVED,
				'internal'          => PageInterface::STATUS_INTERNAL,
				'included_statuses' => [PageInterface::STATUS_INACTIVE, PageInterface::STATUS_ACTIVE]
			],
		];

		Utils::$context['browse_type'] = 'all';
		$type = '';
		$status = PageInterface::STATUS_ACTIVE;

		if ($this->request()->has('u')) {
			Utils::$context['browse_type'] = 'own';
			$type = ';u=' . User::$info['id'];
		} elseif ($this->request()->has('moderate')) {
			Utils::$context['browse_type'] = 'mod';
			$type = ';moderate';
		} elseif ($this->request()->has('internal')) {
			Utils::$context['browse_type'] = 'int';
			$type = ';internal';
			$status = PageInterface::STATUS_INTERNAL;
		}

		$listOptions = [
			'id' => 'lp_pages',
			'items_per_page' => 20,
			'title' => Lang::$txt['lp_pages_extra'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Config::$scripturl . '?action=admin;area=lp_pages;sa=main' . $type . (empty(Utils::$context['search_params']) ? '' : ';params=' . Utils::$context['search_params']),
			'default_sort_col' => 'date',
			'get_items' => [
				'function' => [$this->repository, 'getAll'],
				'params'   => $params,
			],
			'get_count' => [
				'function' => [$this->repository, 'getTotalCount'],
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
				'alias' => [
					'header' => [
						'value' => Lang::$txt['lp_page_alias'],
					],
					'data' => [
						'db' => 'alias',
						'class' => 'centertext word_break',
					],
					'sort' => [
						'default' => 'p.alias DESC',
						'reverse' => 'p.alias',
					],
				],
				'title' => [
					'header' => [
						'value' => Lang::$txt['lp_title'],
					],
					'data' => [
						'function' => fn($entry) => '<i class="' . $this->getPageIcon($entry['type']) . '" title="' . (Utils::$context['lp_content_types'][$entry['type']] ?? strtoupper($entry['type'])) . '"></i> <a class="bbc_link' . (
							$entry['is_front']
								? ' highlight" href="' . Config::$scripturl
								: '" href="' . LP_PAGE_URL . $entry['alias']
							) . '">' . $entry['title'] . '</a>',
						'class' => 'word_break',
					],
					'sort' => [
						'default' => 't.title DESC',
						'reverse' => 't.title',
					],
				],
				'status' => [
					'header' => [
						'value' => Lang::$txt['status'],
					],
					'data' => [
						'function' => fn($entry) => Utils::$context['allow_light_portal_approve_pages'] || Utils::$context['allow_light_portal_manage_pages_any'] ? /** @lang text */ '<div data-id="' . $entry['id'] . '" x-data="{status: ' . ($entry['status'] === $status ? 'true' : 'false') . '}" x-init="$watch(\'status\', value => page.toggleStatus($el))">
								<span :class="{\'on\': status, \'off\': !status}" :title="status ? \'' . Lang::$txt['lp_action_off'] . '\' : \'' . Lang::$txt['lp_action_on'] . '\'" @click.prevent="status = !status"></span>
							</div>' : /** @lang text */ '<div x-data="{status: ' . ($entry['status'] === $status ? 'true' : 'false') . '}">
								<span :class="{\'on\': status, \'off\': !status}" style="cursor: inherit">
							</div>',
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'p.status DESC',
						'reverse' => 'p.status',
					],
				],
				'actions' => [
					'header' => [
						'value' => Lang::$txt['lp_actions'],
						'style' => 'width: 8%',
					],
					'data' => [
						'function' => fn($entry) => /** @lang text */ '
						<div data-id="' . $entry['id'] . '" x-data="{showContextMenu: false}">
							<div class="context_menu" @click.outside="showContextMenu = false">
								<button class="button floatnone" @click.prevent="showContextMenu = true">
									<svg aria-hidden="true" width="10" height="10" focusable="false" data-prefix="fas" data-icon="ellipsis-h" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M328 256c0 39.8-32.2 72-72 72s-72-32.2-72-72 32.2-72 72-72 72 32.2 72 72zm104-72c-39.8 0-72 32.2-72 72s32.2 72 72 72 72-32.2 72-72-32.2-72-72-72zm-352 0c-39.8 0-72 32.2-72 72s32.2 72 72 72 72-32.2 72-72-32.2-72-72-72z"></path></svg>
								</button>
								<div class="roundframe" x-show="showContextMenu">
									<ul>
										<li>
											<a href="' . Config::$scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $entry['id'] . '" class="button">' . Lang::$txt['modify'] . '</a>
										</li>
										<li>
											<a @click.prevent="showContextMenu = false; page.remove($root)" class="button error">' . Lang::$txt['remove'] . '</a>
										</li>
									</ul>
								</div>
							</div>
						</div>',
						'class' => 'centertext',
					],
				],
			],
			'form' => [
				'name' => 'manage_pages',
				'href' => Config::$scripturl . '?action=admin;area=lp_pages;sa=main' . $type,
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => [
					Utils::$context['session_var'] => Utils::$context['session_id'],
					'params' => Utils::$context['search_params'],
				],
			],
			'javascript' => 'const page = new Page();',
			'additional_rows' => [
				[
					'position' => 'after_title',
					'value' => '
						<div class="row">
							<div class="col-lg-10">
								<input type="search" name="search" value="' . Utils::$context['search']['string'] . '" placeholder="' . Lang::$txt['lp_pages_search'] . '" style="width: 100%">
							</div>
							<div class="col-lg-2">
								<button type="submit" name="is_search" class="button floatnone" style="width: 100%">
									' . Icon::get('search') . Lang::$txt['search'] . '
								</button>
							</div>
						</div>',
				],
			],
		];

		if (Utils::$context['user']['is_admin']) {
			$listOptions['columns']['mass'] = [
				'header' => [
					'value' => '<input type="checkbox" onclick="invertAll(this, this.form);">',
				],
				'data' => [
					'function' => fn($entry) => '<input type="checkbox" value="' . $entry['id'] . '" name="items[]">',
					'class' => 'centertext',
				],
			];

			$listOptions['additional_rows'][] = [
				'position' => 'below_table_data',
				'value' => '
					<select name="page_actions">
						<option value="delete">' . Lang::$txt['remove'] . '</option>' . (Utils::$context['allow_light_portal_approve_pages'] || Utils::$context['allow_light_portal_manage_pages_any'] ? '
						<option value="toggle">' . Lang::$txt['lp_action_toggle'] . '</option>' : '') . (! empty(Config::$modSettings['lp_frontpage_mode']) && Config::$modSettings['lp_frontpage_mode'] === 'chosen_pages' ? '
						<option value="promote_up">' . Lang::$txt['lp_promote_to_fp'] . '</option>
						<option value="promote_down">' . Lang::$txt['lp_remove_from_fp'] . '</option>' : '') . '
					</select>
					<input type="submit" name="mass_actions" value="' . Lang::$txt['quick_mod_go'] . '" class="button" onclick="return document.forms[\'manage_pages\'][\'page_actions\'].value && confirm(\'' . Lang::$txt['quickmod_confirm'] . '\');">',
				'class' => 'floatright',
			];
		}

		$listOptions['title'] = '
			<span class="floatright">
				<a href="' . Config::$scripturl . '?action=admin;area=lp_pages;sa=add;' . Utils::$context['session_var'] . '=' . Utils::$context['session_id'] . '" x-data>
					' . (str_replace(' class=', ' @mouseover="page.toggleSpin($event.target)" @mouseout="page.toggleSpin($event.target)" class=', Icon::get('plus', Lang::$txt['lp_pages_add']))) . '
				</a>
			</span>' . $listOptions['title'];

		if (! (empty(Config::$modSettings['lp_show_comment_block']) || Config::$modSettings['lp_show_comment_block'] === 'default')) {
			unset($listOptions['columns']['num_comments']);
		}

		$this->createList($listOptions);

		$this->changeTableTitle();
	}

	/**
	 * Possible actions with pages
	 *
	 * Возможные действия со страницами
	 */
	public function doActions(): void
	{
		if ($this->request()->hasNot('actions'))
			return;

		$data = $this->request()->json();

		if (isset($data['del_item']))
			$this->remove([(int) $data['del_item']]);

		if (isset($data['toggle_item']))
			$this->toggleStatus([(int) $data['toggle_item']], 'page');

		$this->cache()->flush();

		exit;
	}

	public function massActions(): void
	{
		if ($this->request()->hasNot('mass_actions') || $this->request()->isEmpty('items'))
			return;

		$redirect = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_DEFAULT, ['options' => ['default' => 'action=admin;area=lp_pages']]);

		$items = $this->request('items');
		switch (filter_input(INPUT_POST, 'page_actions')) {
			case 'delete':
				$this->remove($items);
				break;
			case 'toggle':
				$this->toggleStatus($items, 'page');
				break;
			case 'promote_up':
				$this->promote($items);
				break;
			case 'promote_down':
				$this->promote($items, 'down');
				break;
		}

		$this->cache()->flush();

		Utils::redirectexit($redirect);
	}

	public function add(): void
	{
		Theme::loadTemplate('LightPortal/ManagePages');

		Utils::$context['sub_template'] = 'page_add';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_pages_add_title'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_add_title'];
		Utils::$context['canonical_url']   = Config::$scripturl . '?action=admin;area=lp_pages;sa=add';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_pages_add_description'],
		];

		$this->preparePageList();

		$json = $this->request()->json();
		$type = $json['add_page'] ?? $this->request('add_page', '') ?? '';

		if (empty($type) && empty($json['search']))
			return;

		Utils::$context['lp_current_page']['type'] = $type;

		$this->prepareForumLanguages();
		$this->validateData();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData();

		Utils::$context['sub_template'] = 'page_post';
	}

	/**
	 * @throws IntlException
	 */
	public function edit(): void
	{
		$item = (int) ($this->request('page_id') ?: $this->request('id'));

		if (empty($item)) {
			ErrorHandler::fatalLang('lp_page_not_found', status: 404);
		}

		Theme::loadTemplate('LightPortal/ManagePages');

		Utils::$context['sub_template'] = 'page_post';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_pages_edit_title'];

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_pages_edit_description'],
		];

		Utils::$context['lp_current_page'] = (new Page)->getDataByItem($item);

		if (empty(Utils::$context['lp_current_page']))
			ErrorHandler::fatalLang('lp_page_not_found', status: 404);

		if (Utils::$context['lp_current_page']['can_edit'] === false)
			ErrorHandler::fatalLang('lp_page_not_editable');

		$this->prepareForumLanguages();

		if ($this->request()->has('remove')) {
			if (Utils::$context['lp_current_page']['author_id'] !== User::$info['id']) {
				$this->logAction('remove_lp_page', [
					'page' => Utils::$context['lp_current_page']['titles'][User::$info['language']]
				]);
			}

			$this->remove([$item]);

			$this->cache()->forget('page_' . Utils::$context['lp_current_page']['alias']);

			Utils::redirectexit('action=admin;area=lp_pages');
		}

		$this->validateData();

		$page_title = Utils::$context['lp_page']['titles'][Utils::$context['user']['language']] ?? '';
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_edit_title'] . ($page_title ? ' - ' . $page_title : '');
		Utils::$context['canonical_url'] = Config::$scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . Utils::$context['lp_page']['id'];

		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData(Utils::$context['lp_page']['id']);
	}

	private function changeTableTitle(): void
	{
		$titles = [
			'all' => [
				'',
				Lang::$txt['all'],
				$this->repository->getTotalCount(' AND p.status != ' . PageInterface::STATUS_UNAPPROVED)
			],
			'own' => [
				';u=' . User::$info['id'],
				Lang::$txt['lp_my_pages'],
				Utils::$context['lp_quantities']['my_pages']
			],
			'mod' => [
				';moderate',
				Lang::$txt['awaiting_approval'],
				Utils::$context['lp_quantities']['unapproved_pages']
			],
			'int' => [
				';internal',
				Lang::$txt['lp_pages_internal'],
				Utils::$context['lp_quantities']['internal_pages']
			]
		];

		if (! Utils::$context['allow_light_portal_manage_pages_any']) {
			unset($titles['all'], $titles['mod'], $titles['int']);
		}

		Utils::$context['lp_pages']['title'] .= ': ';
		foreach ($titles as $browse_type => $details) {
			if (Utils::$context['browse_type'] === $browse_type)
				Utils::$context['lp_pages']['title'] .= '<img src="' . Theme::$current->settings['images_url'] . '/selected.png" alt="&gt;"> ';

			Utils::$context['lp_pages']['title'] .= '<a href="' . Config::$scripturl . '?action=admin;area=lp_pages;sa=main' . $details[0] . '">' . $details[1] . ' (' . $details[2] . ')</a>';

			if ($browse_type !== 'int' && count($titles) > 1)
				Utils::$context['lp_pages']['title'] .= ' | ';
		}
	}

	private function remove(array $items): void
	{
		if (empty($items))
			return;

		$this->hook('onPageRemoving', [$items]);

		Utils::$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_pages
			WHERE page_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		Utils::$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_titles
			WHERE item_id IN ({array_int:items})
				AND type = {literal:page}',
			[
				'items' => $items,
			]
		);

		Utils::$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_params
			WHERE item_id IN ({array_int:items})
				AND type = {literal:page}',
			[
				'items' => $items,
			]
		);

		$result = Utils::$smcFunc['db_query']('', '
			SELECT id FROM {db_prefix}lp_comments
			WHERE page_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		$comments = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$comments[] = $row['id'];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries'] += 4;

		if ($comments) {
			Utils::$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_comments
				WHERE id IN ({array_int:items})',
				[
					'items' => $comments,
				]
			);

			Utils::$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_params
				WHERE item_id IN ({array_int:items})
					AND type = {literal:comment}',
				[
					'items' => $comments,
				]
			);

			Utils::$context['lp_num_queries'] += 2;
		}
	}

	private function promote(array $items, string $type = 'up'): void
	{
		if (empty($items))
			return;

		if ($type === 'down') {
			$items = array_diff(Utils::$context['lp_frontpage_pages'], $items);
		} else {
			$items = array_merge(array_diff($items, Utils::$context['lp_frontpage_pages']), Utils::$context['lp_frontpage_pages']);
		}

		Config::updateModSettings(['lp_frontpage_pages' => implode(',', $items)]);
	}

	private function getParams(): array
	{
		$baseParams = [
			'show_title'           => true,
			'show_in_menu'         => false,
			'page_icon'            => '',
			'show_author_and_date' => true,
			'show_related_pages'   => false,
			'allow_comments'       => false,
		];

		$params = [];

		$this->hook('preparePageParams', [&$params]);

		return array_merge($baseParams, $params);
	}

	private function validateData(): void
	{
		[$post_data, $parameters] = (new PageValidator())->validate();

		$options = $this->getParams();
		$page_options = Utils::$context['lp_current_page']['options'] ?? $options;

		$page = new PageModel($post_data, Utils::$context['lp_current_page']);
		$page->authorId = empty($post_data['author_id']) ? $page->authorId : $post_data['author_id'];
		$page->titles = Utils::$context['lp_current_page']['titles'] ?? [];
		$page->keywords = $post_data['keywords'] ?? Utils::$context['lp_current_page']['tags'] ?? [];
		$page->options = $options;

		$dateTime = DateTime::get();
		$page->date = $post_data['date'] ?? $dateTime->format('Y-m-d');
		$page->time = $post_data['time'] ?? $dateTime->format('H:i');

		foreach ($page->options as $option => $value) {
			if (isset($parameters[$option]) && isset($post_data) && ! isset($post_data[$option])) {
				$post_data[$option] = 0;

				if ($parameters[$option] === FILTER_DEFAULT)
					$post_data[$option] = '';

				if (is_array($parameters[$option]) && $parameters[$option]['flags'] === FILTER_REQUIRE_ARRAY)
					$post_data[$option] = [];
			}

			$page->options[$option] = $post_data[$option] ?? $page_options[$option] ?? $value;
		}

		foreach (Utils::$context['lp_languages'] as $lang) {
			$page->titles[$lang['filename']] = $post_data['title_' . $lang['filename']] ?? $page->titles[$lang['filename']] ?? '';
		}

		$this->cleanBbcode($page->titles);

		Utils::$context['lp_page'] = $page->toArray();
	}

	private function prepareFormFields(): void
	{
		$this->prepareTitleFields();

		if (Utils::$context['lp_page']['type'] !== 'bbc') {
			TextareaField::make('content', Lang::$txt['lp_content'])
				->setTab('content')
				->setAttribute('style', 'height: 300px')
				->setValue($this->prepareContent(Utils::$context['lp_page']));
		} else {
			$this->createBbcEditor(Utils::$context['lp_page']['content']);
		}

		if (Utils::$context['user']['is_admin']) {
			CustomField::make('show_in_menu', Lang::$txt['lp_page_show_in_menu'])
				->setTab('access_placement')
				->setValue(fn() => new PageIconSelect);
		}

		CustomField::make('permissions', Lang::$txt['edit_permissions'])
			->setTab('access_placement')
			->setValue(fn() => new PermissionSelect);

		CustomField::make('category_id', Lang::$txt['lp_category'])
			->setTab('access_placement')
			->setValue(fn() => new CategorySelect, [
				'id'         => 'category_id',
				'multiple'   => false,
				'full_width' => false,
				'data'       => $this->getEntityList('category'),
				'value'      => Utils::$context['lp_page']['category_id']
			]);

		if (Utils::$context['user']['is_admin']) {
			CustomField::make('status', Lang::$txt['status'])
				->setTab('access_placement')
				->setValue(fn() => new StatusSelect);

			CustomField::make('author_id', Lang::$txt['lp_page_author'])
				->setTab('access_placement')
				->setAfter(Lang::$txt['lp_page_author_placeholder'])
				->setValue(fn() => new PageAuthorSelect);
		}

		TextField::make('alias', Lang::$txt['lp_page_alias'])
			->setTab('seo')
			->setAfter(Lang::$txt['lp_page_alias_subtext'])
			->required()
			->setAttribute('maxlength', 255)
			->setAttribute('pattern', LP_ALIAS_PATTERN)
			->setAttribute('x-slug.lazy.replacement._', empty(Utils::$context['lp_page']['id']) ? 'title_' . User::$info['language'] : '{}')
			->setValue(Utils::$context['lp_page']['alias']);

		TextareaField::make('description', Lang::$txt['lp_page_description'])
			->setTab('seo')
			->setAttribute('maxlength', 255)
			->setValue(Utils::$context['lp_page']['description']);

		CustomField::make('keywords', Lang::$txt['lp_page_keywords'])
			->setTab('seo')
			->setValue(fn() => new KeywordSelect);

		if (Utils::$context['lp_page']['created_at'] >= time()) {
			CustomField::make('datetime', Lang::$txt['lp_page_publish_datetime'])
				->setValue('
			<input type="date" id="datetime" name="date" min="' . date('Y-m-d') . '" value="' . Utils::$context['lp_page']['date'] . '">
			<input type="time" name="time" value="' . Utils::$context['lp_page']['time'] . '">');
		}

		CheckboxField::make('show_title', Lang::$txt['lp_page_show_title'])
			->setValue(Utils::$context['lp_page']['options']['show_title']);

		CheckboxField::make('show_author_and_date', Lang::$txt['lp_page_show_author_and_date'])
			->setValue(Utils::$context['lp_page']['options']['show_author_and_date']);

		if (! empty(Config::$modSettings['lp_show_related_pages'])) {
			CheckboxField::make('show_related_pages', Lang::$txt['lp_page_show_related_pages'])
				->setValue(Utils::$context['lp_page']['options']['show_related_pages']);
		}

		if (! (empty(Config::$modSettings['lp_show_comment_block']) || Config::$modSettings['lp_show_comment_block'] === 'none')) {
			CheckboxField::make('allow_comments', Lang::$txt['lp_page_allow_comments'])
				->setValue(Utils::$context['lp_page']['options']['allow_comments']);
		}

		$this->hook('preparePageFields');

		$this->preparePostFields();
	}

	private function prepareEditor(): void
	{
		$this->hook('prepareEditor', [Utils::$context['lp_page']]);
	}

	private function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		$this->checkSubmitOnce('free');

		Utils::$context['preview_title']   = Utils::$context['lp_page']['titles'][Utils::$context['user']['language']];
		Utils::$context['preview_content'] = Utils::$smcFunc['htmlspecialchars'](Utils::$context['lp_page']['content'], ENT_QUOTES);

		$this->cleanBbcode(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_content']);

		if (Utils::$context['preview_content'])
			Utils::$context['preview_content'] = Content::parse(Utils::$context['preview_content'], Utils::$context['lp_page']['type']);

		Utils::$context['page_title']    = Lang::$txt['preview'] . (Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : '');
		Utils::$context['preview_title'] = $this->getPreviewTitle();
	}

	private function checkUser(): void
	{
		if (Utils::$context['allow_light_portal_manage_pages_any'] === false && $this->request()->has('sa') && $this->request('sa') === 'main' && $this->request()->hasNot('u'))
			Utils::redirectexit('action=admin;area=lp_pages;u=' . User::$info['id']);
	}

	private function preparePageList(): void
	{
		$defaultTypes = $this->getDefaultTypes();

		Utils::$context['lp_all_pages'] = [];
		foreach (Utils::$context['lp_content_types'] as $type => $title) {
			Utils::$context['lp_all_pages'][$type] = [
				'type'  => $type,
				'icon'  => $defaultTypes[$type]['icon'] ?? Utils::$context['lp_loaded_addons'][$type]['icon'],
				'title' => Lang::$txt['lp_' . $type]['title'] ?? $title,
				'desc'  => Lang::$txt['lp_' . $type]['block_desc'] ?? Lang::$txt['lp_' . $type]['description']
			];
		}

		$titles = array_column(Utils::$context['lp_all_pages'], 'title');
		array_multisort($titles, SORT_ASC, Utils::$context['lp_all_pages']);
	}

	private function getPageIcon(string $type): string
	{
		return $this->getDefaultTypes()[$type]['icon'] ?? Utils::$context['lp_loaded_addons'][$type]['icon'] ?? 'fas fa-question';
	}
}
