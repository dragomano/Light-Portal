<?php

declare(strict_types=1);

/**
 * PageArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Areas;

use Bugo\LightPortal\Areas\Fields\{CheckboxField, CustomField, TextareaField, TextField};
use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Entities\Page;
use Bugo\LightPortal\Partials\{
	CategorySelect,
	KeywordSelect,
	PageAuthorSelect,
	PageIconSelect,
	PermissionSelect,
	StatusSelect,
};
use Bugo\LightPortal\Repositories\PageRepository;

if (! defined('SMF'))
	die('No direct access...');

final class PageArea
{
	use Area, Helper;

	private PageRepository $repository;

	private const ALIAS_PATTERN = '^[a-z][a-z0-9_]+$';

	public function __construct()
	{
		$this->repository = new PageRepository;
	}

	public function main(): void
	{
		$this->checkUser();

		$this->loadLanguage('Packages');
		$this->loadTemplate('LightPortal/ManagePages');

		if ($this->request()->has('moderate'))
			$this->addInlineCss('
		#lp_pages .num_views, #lp_pages .num_comments {
			display: none;
		}');

		$this->context['page_title'] = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_pages_manage'];

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_pages_manage_' . ($this->context['allow_light_portal_manage_pages_any'] && $this->request()->hasNot('u') ? 'all' : 'own') . '_pages'] . ' ' . $this->txt['lp_pages_manage_description'],
		];

		if ($this->request()->has('moderate'))
			$this->context[$this->context['admin_menu_name']]['tab_data']['description'] = $this->txt['lp_pages_unapproved_description'];

		if ($this->request()->has('internal'))
			$this->context[$this->context['admin_menu_name']]['tab_data']['description'] = $this->txt['lp_pages_internal_description'];

		$this->doActions();
		$this->massActions();

		$search_params_string = trim($this->request('search', ''));
		$search_params = [
			'string' => $this->smcFunc['htmlspecialchars']($search_params_string),
		];

		$this->context['search_params'] = empty($search_params_string) ? '' : base64_encode($this->smcFunc['json_encode']($search_params));
		$this->context['search'] = [
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
				'search'            => $this->smcFunc['strtolower']($search_params['string']),
				'unapproved'        => Page::STATUS_UNAPPROVED,
				'internal'          => Page::STATUS_INTERNAL,
				'included_statuses' => [Page::STATUS_INACTIVE, Page::STATUS_ACTIVE]
			],
		];

		$this->context['browse_type'] = 'all';
		$type = '';
		$status = Page::STATUS_ACTIVE;

		if ($this->request()->has('u')) {
			$this->context['browse_type'] = 'own';
			$type = ';u=' . $this->user_info['id'];
		} elseif ($this->request()->has('moderate')) {
			$this->context['browse_type'] = 'mod';
			$type = ';moderate';
		} elseif ($this->request()->has('internal')) {
			$this->context['browse_type'] = 'int';
			$type = ';internal';
			$status = Page::STATUS_INTERNAL;
		}

		$listOptions = [
			'id' => 'lp_pages',
			'items_per_page' => 20,
			'title' => $this->txt['lp_pages_extra'],
			'no_items_label' => $this->txt['lp_no_items'],
			'base_href' => $this->scripturl . '?action=admin;area=lp_pages;sa=main' . $type . (empty($this->context['search_params']) ? '' : ';params=' . $this->context['search_params']),
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
						'value' => $this->txt['date'],
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
						'value' => str_replace(' class=', ' title="' . $this->txt['lp_views'] . '" class=', $this->context['lp_icon_set']['views'])
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
						'value' => $this->txt['lp_page_alias'],
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
						'value' => $this->txt['lp_title'],
					],
					'data' => [
						'function' => fn($entry) => '<i class="' . ($this->getDefaultTypes()[$entry['type']]['icon'] ?? 'fas fa-question') . '" title="' . ($this->context['lp_content_types'][$entry['type']] ?? strtoupper($entry['type'])) . '"></i> <a class="bbc_link' . (
							$entry['is_front']
								? ' highlight" href="' . $this->scripturl
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
						'value' => $this->txt['status'],
					],
					'data' => [
						'function' => fn($entry) => $this->context['allow_light_portal_approve_pages'] || $this->context['allow_light_portal_manage_pages_any'] ? /** @lang text */ '<div data-id="' . $entry['id'] . '" x-data="{status: ' . ($entry['status'] === $status ? 'true' : 'false') . '}" x-init="$watch(\'status\', value => page.toggleStatus($el))">
								<span :class="{\'on\': status, \'off\': !status}" :title="status ? \'' . $this->txt['lp_action_off'] . '\' : \'' . $this->txt['lp_action_on'] . '\'" @click.prevent="status = !status"></span>
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
						'value' => $this->txt['lp_actions'],
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
											<a href="' . $this->scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $entry['id'] . '" class="button">' . $this->txt['modify'] . '</a>
										</li>
										<li>
											<a @click.prevent="showContextMenu = false; page.remove($root)" class="button error">' . $this->txt['remove'] . '</a>
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
				'href' => $this->scripturl . '?action=admin;area=lp_pages;sa=main' . $type,
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => [
					$this->context['session_var'] => $this->context['session_id'],
					'params' => $this->context['search_params'],
				],
			],
			'javascript' => 'const page = new Page();',
			'additional_rows' => [
				[
					'position' => 'after_title',
					'value' => '
						<div class="row">
							<div class="col-lg-10">
								<input type="search" name="search" value="' . $this->context['search']['string'] . '" placeholder="' . $this->txt['lp_pages_search'] . '" style="width: 100%">
							</div>
							<div class="col-lg-2">
								<button type="submit" name="is_search" class="button floatnone" style="width: 100%">
									' . $this->context['lp_icon_set']['search'] . $this->txt['search'] . '
								</button>
							</div>
						</div>',
				],
			],
		];

		if ($this->context['user']['is_admin']) {
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
						<option value="delete">' . $this->txt['remove'] . '</option>' . ($this->context['allow_light_portal_approve_pages'] || $this->context['allow_light_portal_manage_pages_any'] ? '
						<option value="toggle">' . $this->txt['lp_action_toggle'] . '</option>' : '') . (! empty($this->modSettings['lp_frontpage_mode']) && $this->modSettings['lp_frontpage_mode'] === 'chosen_pages' ? '
						<option value="promote_up">' . $this->txt['lp_promote_to_fp'] . '</option>
						<option value="promote_down">' . $this->txt['lp_remove_from_fp'] . '</option>' : '') . '
					</select>
					<input type="submit" name="mass_actions" value="' . $this->txt['quick_mod_go'] . '" class="button" onclick="return document.forms[\'manage_pages\'][\'page_actions\'].value && confirm(\'' . $this->txt['quickmod_confirm'] . '\');">',
				'class' => 'floatright',
			];
		}

		$listOptions['title'] = '
			<span class="floatright">
				<a href="' . $this->scripturl . '?action=admin;area=lp_pages;sa=add;' . $this->context['session_var'] . '=' . $this->context['session_id'] . '" x-data>
					' . (str_replace(' class=', ' @mouseover="page.toggleSpin($event.target)" @mouseout="page.toggleSpin($event.target)" title="' . $this->txt['lp_pages_add'] . '" class=', $this->context['lp_icon_set']['plus'])) . '
				</a>
			</span>' . $listOptions['title'];

		if (! (empty($this->modSettings['lp_show_comment_block']) || $this->modSettings['lp_show_comment_block'] === 'default')) {
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

		$this->redirect($redirect);
	}

	public function add(): void
	{
		$this->loadTemplate('LightPortal/ManagePages', 'page_add');

		$this->context['page_title']      = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_pages_add_title'];
		$this->context['page_area_title'] = $this->txt['lp_pages_add_title'];
		$this->context['canonical_url']   = $this->scripturl . '?action=admin;area=lp_pages;sa=add';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_pages_add_description'],
		];

		$this->preparePageList();

		$json = $this->request()->json();
		$type = $json['add_page'] ?? $this->request('add_page', '') ?? '';

		if (empty($type) && empty($json['search']))
			return;

		$this->context['lp_current_page']['type'] = $type;

		$this->prepareForumLanguages();
		$this->validateData();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData();

		$this->context['sub_template'] = 'page_post';
	}

	public function edit(): void
	{
		$item = (int) ($this->request('page_id') ?: $this->request('id'));

		if (empty($item)) {
			$this->fatalLangError('lp_page_not_found', 404);
		}

		$this->loadTemplate('LightPortal/ManagePages', 'page_post');

		$this->context['page_title'] = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_pages_edit_title'];

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_pages_edit_description'],
		];

		$this->context['lp_current_page'] = (new Page)->getDataByItem($item);

		if (empty($this->context['lp_current_page']))
			$this->fatalLangError('lp_page_not_found', 404);

		if ($this->context['lp_current_page']['can_edit'] === false)
			$this->fatalLangError('lp_page_not_editable');

		$this->prepareForumLanguages();

		if ($this->request()->has('remove')) {
			if ($this->context['lp_current_page']['author_id'] !== $this->user_info['id']) {
				$this->logAction('remove_lp_page', [
					'page' => $this->context['lp_current_page']['title'][$this->user_info['language']]
				]);
			}

			$this->remove([$item]);

			$this->cache()->forget('page_' . $this->context['lp_current_page']['alias']);

			$this->redirect('action=admin;area=lp_pages');
		}

		$this->validateData();

		$page_title = $this->context['lp_page']['title'][$this->context['user']['language']] ?? '';
		$this->context['page_area_title'] = $this->txt['lp_pages_edit_title'] . ($page_title ? ' - ' . $page_title : '');
		$this->context['canonical_url'] = $this->scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $this->context['lp_page']['id'];

		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData($this->context['lp_page']['id']);
	}

	private function changeTableTitle(): void
	{
		$titles = [
			'all' => [
				'',
				$this->txt['all'],
				$this->repository->getTotalCount(' AND p.status != 2')
			],
			'own' => [
				';u=' . $this->user_info['id'],
				$this->txt['lp_my_pages'],
				$this->context['lp_quantities']['my_pages']
			],
			'mod' => [
				';moderate',
				$this->txt['awaiting_approval'],
				$this->context['lp_quantities']['unapproved_pages']
			],
			'int' => [
				';internal',
				$this->txt['lp_pages_internal'],
				$this->context['lp_quantities']['internal_pages']
			]
		];

		if (! $this->context['allow_light_portal_manage_pages_any']) {
			unset($titles['all'], $titles['mod'], $titles['int']);
		}

		$this->context['lp_pages']['title'] .= ': ';
		foreach ($titles as $browse_type => $details) {
			if ($this->context['browse_type'] === $browse_type)
				$this->context['lp_pages']['title'] .= '<img src="' . $this->settings['images_url'] . '/selected.png" alt="&gt;"> ';

			$this->context['lp_pages']['title'] .= '<a href="' . $this->scripturl . '?action=admin;area=lp_pages;sa=main' . $details[0] . '">' . $details[1] . ' (' . $details[2] . ')</a>';

			if ($browse_type !== 'int' && count($titles) > 1)
				$this->context['lp_pages']['title'] .= ' | ';
		}
	}

	private function remove(array $items): void
	{
		if (empty($items))
			return;

		$this->hook('onPageRemoving', [$items]);

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_pages
			WHERE page_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_titles
			WHERE item_id IN ({array_int:items})
				AND type = {literal:page}',
			[
				'items' => $items,
			]
		);

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_params
			WHERE item_id IN ({array_int:items})
				AND type = {literal:page}',
			[
				'items' => $items,
			]
		);

		$result = $this->smcFunc['db_query']('', '
			SELECT id FROM {db_prefix}lp_comments
			WHERE page_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		$comments = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($result)) {
			$comments[] = $row['id'];
		}

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries'] += 4;

		if ($comments) {
			$this->smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_comments
				WHERE id IN ({array_int:items})',
				[
					'items' => $comments,
				]
			);

			$this->smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_params
				WHERE item_id IN ({array_int:items})
					AND type = {literal:comment}',
				[
					'items' => $comments,
				]
			);

			$this->context['lp_num_queries'] += 2;
		}
	}

	private function promote(array $items, string $type = 'up'): void
	{
		if (empty($items))
			return;

		if ($type === 'down') {
			$items = array_diff($this->context['lp_frontpage_pages'], $items);
		} else {
			$items = array_merge(array_diff($items, $this->context['lp_frontpage_pages']), $this->context['lp_frontpage_pages']);
		}

		$this->updateSettings(['lp_frontpage_pages' => implode(',', $items)]);
	}

	private function getOptions(): array
	{
		$options = [
			'show_title'           => true,
			'show_in_menu'         => false,
			'page_icon'            => '',
			'show_author_and_date' => true,
			'show_related_pages'   => false,
			'allow_comments'       => false,
		];

		$this->hook('pageOptions', [&$options]);

		return $options;
	}

	private function validateData(): void
	{
		$post_data = [];

		if ($this->request()->only(['save', 'save_exit', 'preview'])) {
			$args = [
				'category'    => FILTER_VALIDATE_INT,
				'page_author' => FILTER_VALIDATE_INT,
				'alias'       => FILTER_DEFAULT,
				'description' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'keywords'    => FILTER_DEFAULT,
				'type'        => FILTER_DEFAULT,
				'permissions' => FILTER_VALIDATE_INT,
				'status'      => FILTER_VALIDATE_INT,
				'date'        => FILTER_DEFAULT,
				'time'        => FILTER_DEFAULT,
				'content'     => FILTER_UNSAFE_RAW,
			];

			foreach ($this->context['languages'] as $lang) {
				$args['title_' . $lang['filename']] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			$parameters = [];

			$this->hook('validatePageData', [&$parameters]);

			$parameters = array_merge(
				[
					'show_title'           => FILTER_VALIDATE_BOOLEAN,
					'show_in_menu'         => FILTER_VALIDATE_BOOLEAN,
					'page_icon'            => FILTER_DEFAULT,
					'show_author_and_date' => FILTER_VALIDATE_BOOLEAN,
					'show_related_pages'   => FILTER_VALIDATE_BOOLEAN,
					'allow_comments'       => FILTER_VALIDATE_BOOLEAN,
				],
				$parameters
			);

			$post_data = filter_input_array(INPUT_POST, array_merge($args, $parameters));
			$post_data['id'] = $this->request('id', 0);
			$post_data['keywords'] = empty($post_data['keywords']) ? [] : explode(',', $post_data['keywords']);

			$this->findErrors($post_data);
		}

		$options = $this->getOptions();
		$page_options = $this->context['lp_current_page']['options'] ?? $options;

		$dateTime = $this->getDateTime();

		$this->context['lp_page'] = [
			'id'          => (int) ($post_data['id'] ?? $this->context['lp_current_page']['id'] ?? 0),
			'title'       => $this->context['lp_current_page']['title'] ?? [],
			'category'    => $post_data['category'] ?? $this->context['lp_current_page']['category_id'] ?? 0,
			'page_author' => (int) ($this->context['lp_current_page']['author_id'] ?? $this->user_info['id']),
			'alias'       => $post_data['alias'] ?? $this->context['lp_current_page']['alias'] ?? '',
			'description' => $post_data['description'] ?? $this->context['lp_current_page']['description'] ?? '',
			'keywords'    => $post_data['keywords'] ?? $this->context['lp_current_page']['tags'] ?? [],
			'type'        => $post_data['type'] ?? $this->context['lp_current_page']['type'] ?? 'bbc',
			'permissions' => $post_data['permissions'] ?? $this->context['lp_current_page']['permissions'] ?? $this->modSettings['lp_permissions_default'] ?? 2,
			'status'      => $post_data['status'] ?? $this->context['lp_current_page']['status'] ?? (int) ($this->context['allow_light_portal_approve_pages'] || $this->context['allow_light_portal_manage_pages_any']),
			'created_at'  => $this->context['lp_current_page']['created_at'] ?? time(),
			'date'        => $post_data['date'] ?? $dateTime->format('Y-m-d'),
			'time'        => $post_data['time'] ?? $dateTime->format('H:i'),
			'content'     => $post_data['content'] ?? $this->context['lp_current_page']['content'] ?? '',
			'options'     => $options,
		];

		$this->context['lp_page']['page_author'] = empty($post_data['page_author']) ? $this->context['lp_page']['page_author'] : $post_data['page_author'];

		foreach ($this->context['lp_page']['options'] as $option => $value) {
			if (isset($parameters[$option]) && isset($post_data) && ! isset($post_data[$option])) {
				$post_data[$option] = 0;

				if ($parameters[$option] === FILTER_DEFAULT)
					$post_data[$option] = '';

				if (is_array($parameters[$option]) && $parameters[$option]['flags'] === FILTER_REQUIRE_ARRAY)
					$post_data[$option] = [];
			}

			$this->context['lp_page']['options'][$option] = $post_data[$option] ?? $page_options[$option] ?? $value;
		}

		foreach ($this->context['languages'] as $lang) {
			$this->context['lp_page']['title'][$lang['filename']] = $post_data['title_' . $lang['filename']] ?? $this->context['lp_page']['title'][$lang['filename']] ?? '';
		}

		$this->cleanBbcode($this->context['lp_page']['title']);
	}

	private function findErrors(array $data): void
	{
		$post_errors = [];

		if (($this->modSettings['userLanguage'] && empty($data['title_' . $this->language])) || empty($data['title_' . $this->context['user']['language']]))
			$post_errors[] = 'no_title';

		if (empty($data['alias']))
			$post_errors[] = 'no_alias';

		if ($data['alias'] && empty($this->validate($data['alias'], ['options' => ['regexp' => '/' . self::ALIAS_PATTERN . '/']])))
			$post_errors[] = 'no_valid_alias';

		if ($data['alias'] && ! $this->isUnique($data))
			$post_errors[] = 'no_unique_alias';

		if (empty($data['content']))
			$post_errors[] = 'no_content';

		$this->hook('findPageErrors', [&$post_errors, $data]);

		if ($post_errors) {
			$this->request()->put('preview', true);
			$this->context['post_errors'] = [];

			foreach ($post_errors as $error)
				$this->context['post_errors'][] = $this->txt['lp_post_error_' . $error];
		}
	}

	private function prepareFormFields(): void
	{
		$this->prepareTitleFields();

		if ($this->context['lp_page']['type'] !== 'bbc') {
			TextareaField::make('content', $this->txt['lp_content'])
				->setTab('content')
				->setAttribute('style', 'height: 300px')
				->setValue($this->prepareContent($this->context['lp_page']));
		} else {
			$this->createBbcEditor($this->context['lp_page']['content']);
		}

		if ($this->context['user']['is_admin']) {
			CustomField::make('show_in_menu', $this->txt['lp_page_show_in_menu'])
				->setTab('access_placement')
				->setValue(fn() => new PageIconSelect);
		}

		CustomField::make('permissions', $this->txt['edit_permissions'])
			->setTab('access_placement')
			->setValue(fn() => new PermissionSelect);

		CustomField::make('category', $this->txt['lp_category'])
			->setTab('access_placement')
			->setValue(fn() => new CategorySelect, [
				'id'         => 'category',
				'multiple'   => false,
				'full_width' => false,
				'data'       => $this->getEntityList('category'),
				'value'      => $this->context['lp_page']['category']
			]);

		if ($this->context['user']['is_admin']) {
			CustomField::make('status', $this->txt['status'])
				->setTab('access_placement')
				->setValue(fn() => new StatusSelect);

			CustomField::make('page_author', $this->txt['lp_page_author'])
				->setTab('access_placement')
				->setAfter($this->txt['lp_page_author_placeholder'])
				->setValue(fn() => new PageAuthorSelect);
		}

		TextField::make('alias', $this->txt['lp_page_alias'])
			->setTab('seo')
			->setAfter($this->txt['lp_page_alias_subtext'])
			->setAttribute('maxlength', 255)
			->setAttribute('required', true)
			->setAttribute('pattern', self::ALIAS_PATTERN)
			->setAttribute('x-slug.lazy.replacement._', empty($this->context['lp_page']['id']) ? 'title_' . $this->user_info['language'] : '{}')
			->setValue($this->context['lp_page']['alias']);

		TextareaField::make('description', $this->txt['lp_page_description'])
			->setTab('seo')
			->setAttribute('maxlength', 255)
			->setValue($this->context['lp_page']['description']);

		CustomField::make('keywords', $this->txt['lp_page_keywords'])
			->setTab('seo')
			->setValue(fn() => new KeywordSelect);

		if ($this->context['lp_page']['created_at'] >= time()) {
			CustomField::make('datetime', $this->txt['lp_page_publish_datetime'])
				->setValue('
			<input type="date" id="datetime" name="date" min="' . date('Y-m-d') . '" value="' . $this->context['lp_page']['date'] . '">
			<input type="time" name="time" value="' . $this->context['lp_page']['time'] . '">');
		}

		CheckboxField::make('show_title', $this->txt['lp_page_show_title'])
			->setValue($this->context['lp_page']['options']['show_title']);

		CheckboxField::make('show_author_and_date', $this->txt['lp_page_show_author_and_date'])
			->setValue($this->context['lp_page']['options']['show_author_and_date']);

		if (! empty($this->modSettings['lp_show_related_pages'])) {
			CheckboxField::make('show_related_pages', $this->txt['lp_page_show_related_pages'])
				->setValue($this->context['lp_page']['options']['show_related_pages']);
		}

		if (! (empty($this->modSettings['lp_show_comment_block']) || $this->modSettings['lp_show_comment_block'] === 'none')) {
			CheckboxField::make('allow_comments', $this->txt['lp_page_allow_comments'])
				->setValue($this->context['lp_page']['options']['allow_comments']);
		}

		$this->hook('preparePageFields');

		$this->preparePostFields();
	}

	private function prepareEditor(): void
	{
		$this->hook('prepareEditor', [$this->context['lp_page']]);
	}

	private function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		$this->checkSubmitOnce('free');

		$this->context['preview_title']   = $this->context['lp_page']['title'][$this->context['user']['language']];
		$this->context['preview_content'] = $this->smcFunc['htmlspecialchars']($this->context['lp_page']['content'], ENT_QUOTES);

		$this->cleanBbcode($this->context['preview_title']);
		$this->censorText($this->context['preview_title']);
		$this->censorText($this->context['preview_content']);

		if ($this->context['preview_content'])
			$this->context['preview_content'] = parse_content($this->context['preview_content'], $this->context['lp_page']['type']);

		$this->context['page_title']    = $this->txt['preview'] . ($this->context['preview_title'] ? ' - ' . $this->context['preview_title'] : '');
		$this->context['preview_title'] = $this->getPreviewTitle();
	}

	private function isUnique(array $data): bool
	{
		$result = $this->smcFunc['db_query']('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE alias = {string:alias}
				AND page_id != {int:item}',
			[
				'alias' => $data['alias'],
				'item'  => $data['id'],
			]
		);

		[$count] = $this->smcFunc['db_fetch_row']($result);

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return $count == 0;
	}

	private function checkUser(): void
	{
		if ($this->context['allow_light_portal_manage_pages_any'] === false && $this->request()->has('sa') && $this->request('sa') === 'main' && $this->request()->hasNot('u'))
			$this->redirect('action=admin;area=lp_pages;u=' . $this->user_info['id']);
	}

	private function preparePageList(): void
	{
		$defaultTypes = $this->getDefaultTypes();

		$this->context['lp_all_pages'] = [];
		foreach ($this->context['lp_content_types'] as $type => $title) {
			$this->context['lp_all_pages'][$type] = [
				'type'  => $type,
				'icon'  => $defaultTypes[$type]['icon'] ?? $this->context['lp_loaded_addons'][$type]['icon'],
				'title' => $this->txt['lp_' . $type]['title'] ?? $title,
				'desc'  => $this->txt['lp_' . $type]['block_desc'] ?? $this->txt['lp_' . $type]['description']
			];
		}

		$titles = array_column($this->context['lp_all_pages'], 'title');
		array_multisort($titles, SORT_ASC, $this->context['lp_all_pages']);
	}
}
