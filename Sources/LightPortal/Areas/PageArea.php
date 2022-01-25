<?php

declare(strict_types=1);

/**
 * PageArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Areas;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Entities\Page;

use function censorText;
use function checkSubmitOnce;
use function createList;
use function fatal_lang_error;
use function loadLanguage;
use function loadTemplate;
use function updateSettings;
use function redirectexit;
use function template_control_richedit;

if (! defined('SMF'))
	die('No direct access...');

final class PageArea extends AbstractArea
{
	public const NUM_PAGES = 20;

	protected string $entity = 'page';

	private const ALIAS_PATTERN = '^[a-z][a-z0-9_]+$';

	public function main()
	{
		loadLanguage('Packages');
		loadTemplate('LightPortal/ManagePages');

		$this->context['page_title'] = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_pages_manage'];

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => '<a href="https://dragomano.github.io/Light-Portal/" target="_blank" rel="noopener"><span class="main_icons help"></span></a> ' . LP_NAME,
			'description' => $this->txt['lp_pages_manage_' . ($this->context['user']['is_admin'] ? 'all' : 'own') . '_pages'] . ' ' . $this->txt['lp_pages_manage_description'],
		];

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

		$listOptions = [
			'id'               => 'lp_pages',
			'items_per_page'   => self::NUM_PAGES,
			'title'            => $this->txt['lp_pages_extra'],
			'no_items_label'   => $this->txt['lp_no_items'],
			'base_href'        => $this->scripturl . '?action=admin;area=lp_pages' . (empty($this->context['search_params']) ? '' : ';params=' . $this->context['search_params']),
			'default_sort_col' => 'date',
			'get_items'        => [
				'function' => [$this, 'getAll'],
				'params'   => [
					(empty($search_params['string']) ? '' : ' (INSTR(LOWER(p.alias), {string:quick_search_string}) > 0 OR INSTR(LOWER(t.title), {string:quick_search_string}) > 0)'),
					['quick_search_string' => $this->smcFunc['strtolower']($search_params['string'])],
				],
			],
			'get_count'        => [
				'function' => [$this, 'getTotalCount'],
				'params'   => [
					(empty($search_params['string']) ? '' : ' (INSTR(LOWER(p.alias), {string:quick_search_string}) > 0 OR INSTR(LOWER(t.title), {string:quick_search_string}) > 0)'),
					['quick_search_string' => $this->smcFunc['strtolower']($search_params['string'])],
				],
			],
			'columns'          => [
				'id'           => [
					'header' => [
						'value' => '#',
						'style' => 'width: 5%',
					],
					'data'   => [
						'db'    => 'id',
						'class' => 'centertext',
					],
					'sort'   => [
						'default' => 'p.page_id',
						'reverse' => 'p.page_id DESC',
					],
				],
				'date'         => [
					'header' => [
						'value' => $this->txt['date'],
					],
					'data'   => [
						'db'    => 'created_at',
						'class' => 'centertext',
					],
					'sort'   => [
						'default' => 'date DESC',
						'reverse' => 'date',
					],
				],
				'num_views'    => [
					'header' => [
						'value' => str_replace(' class=', ' title="' . $this->txt['lp_views'] . '" class=', $this->context['lp_icon_set']['views'])
					],
					'data'   => [
						'db'    => 'num_views',
						'class' => 'centertext',
					],
					'sort'   => [
						'default' => 'p.num_views DESC',
						'reverse' => 'p.num_views',
					],
				],
				'num_comments' => [
					'header' => [
						'value' => str_replace(' class=', '  title="' . $this->txt['lp_comments'] . '" class=', $this->context['lp_icon_set']['replies'])
					],
					'data'   => [
						'db'    => 'num_comments',
						'class' => 'centertext',
					],
					'sort'   => [
						'default' => 'p.num_comments DESC',
						'reverse' => 'p.num_comments',
					],
				],
				'alias'        => [
					'header' => [
						'value' => $this->txt['lp_page_alias'],
					],
					'data'   => [
						'db'    => 'alias',
						'class' => 'centertext word_break',
					],
					'sort'   => [
						'default' => 'p.alias DESC',
						'reverse' => 'p.alias',
					],
				],
				'title'        => [
					'header' => [
						'value' => $this->txt['lp_title'],
					],
					'data'   => [
						'function' => fn($entry) => '<i class="' . ($this->context['lp_loaded_addons'][$entry['type']]['icon'] ?? 'fab fa-bimobject') . '" title="' . ($this->context['lp_content_types'][$entry['type']] ?? strtoupper($entry['type'])) . '"></i> <a class="bbc_link' . (
							$entry['is_front']
								? ' highlight" href="' . $this->scripturl
								: '" href="' . LP_PAGE_URL . $entry['alias']
							) . '">' . $entry['title'] . '</a>',
						'class'    => 'word_break',
					],
					'sort'   => [
						'default' => 't.title DESC',
						'reverse' => 't.title',
					],
				],
				'status'       => [
					'header' => [
						'value' => $this->txt['status'],
					],
					'data'   => [
						'function' => fn($entry) => $this->context['allow_light_portal_approve_pages'] ? '<div data-id="' . $entry['id'] . '" x-data="{status: ' . (empty($entry['status']) ? 'false' : 'true') . '}" x-init="$watch(\'status\', value => page.toggleStatus($el))">
								<span :class="{\'on\': status, \'off\': !status}" :title="status ? \'' . $this->txt['lp_action_off'] . '\' : \'' . $this->txt['lp_action_on'] . '\'" @click.prevent="status = !status"></span>
							</div>' : '<div x-data="{status: ' . (empty($entry['status']) ? 'false' : 'true') . '}">
								<span :class="{\'on\': status, \'off\': !status}" style="cursor: inherit">
							</div>',
						'class'    => 'centertext',
					],
					'sort'   => [
						'default' => 'p.status DESC',
						'reverse' => 'p.status',
					],
				],
				'actions'      => [
					'header' => [
						'value' => $this->txt['lp_actions'],
						'style' => 'width: 8%',
					],
					'data'   => [
						'function' => fn($entry) => '
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
						'class'    => 'centertext',
					],
				],
				'mass'         => [
					'header' => [
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);">',
					],
					'data'   => [
						'function' => fn($entry) => '<input type="checkbox" value="' . $entry['id'] . '" name="items[]">',
						'class'    => 'centertext',
					],
				],
			],
			'form'             => [
				'name'          => 'manage_pages',
				'href'          => $this->scripturl . '?action=admin;area=lp_pages',
				'include_sort'  => true,
				'include_start' => true,
				'hidden_fields' => [
					$this->context['session_var'] => $this->context['session_id'],
					'params'                      => $this->context['search_params'],
				],
			],
			'javascript'       => 'const page = new Page();',
			'additional_rows'  => [
				[
					'position' => 'after_title',
					'value'    => '
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
				[
					'position' => 'below_table_data',
					'value'    => '
						<select name="page_actions">
							<option value="delete">' . $this->txt['remove'] . '</option>' . ($this->context['allow_light_portal_approve_pages'] ? '
							<option value="toggle">' . $this->txt['lp_action_toggle'] . '</option>' : '') . (! empty($this->modSettings['lp_frontpage_mode']) && $this->modSettings['lp_frontpage_mode'] === 'chosen_pages' ? '
							<option value="promote_up">' . $this->txt['lp_promote_to_fp'] . '</option>
							<option value="promote_down">' . $this->txt['lp_remove_from_fp'] . '</option>' : '') . '
						</select>
						<input type="submit" name="mass_actions" value="' . $this->txt['quick_mod_go'] . '" class="button" onclick="return document.forms[\'manage_pages\'][\'page_actions\'].value && confirm(\'' . $this->txt['quickmod_confirm'] . '\');">',
					'class'    => 'floatright',
				],
			],
		];

		$listOptions['title'] = '
			<span class="floatright">
				<a href="' . $this->scripturl . '?action=admin;area=lp_pages;sa=add;' . $this->context['session_var'] . '=' . $this->context['session_id'] . '" x-data>
					' . (str_replace(' class=', ' @mouseover="page.toggleSpin($event.target)" @mouseout="page.toggleSpin($event.target)" title="' . $this->txt['lp_pages_add'] . '" class=', $this->context['lp_icon_set']['plus'])) . '
				</a>
			</span>' . $listOptions['title'];

		if (! (empty($this->modSettings['lp_show_comment_block']) || $this->modSettings['lp_show_comment_block'] === 'default')) {
			unset($listOptions['columns']['num_comments']);
		}

		$this->require('Subs-List');
		createList($listOptions);

		$this->context['lp_pages']['title'] .= ' (' . $this->context['lp_pages']['total_num_items'] . ')';
		$this->context['sub_template'] = 'show_list';
		$this->context['default_list'] = 'lp_pages';
	}

	public function getAll(int $start, int $items_per_page, string $sort, string $query_string = '', array $query_params = []): array
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT p.page_id, p.author_id, p.alias, p.type, p.permissions, p.status, p.num_views, p.num_comments,
				GREATEST(p.created_at, p.updated_at) AS date, mem.real_name AS author_name, t.title, tf.title AS fallback_title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang})
				LEFT JOIN {db_prefix}lp_titles AS tf ON (p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang})' . ($this->user_info['is_admin'] ? '
			WHERE 1=1' : '
			WHERE p.author_id = {int:user_id}') . (empty($query_string) ? '' : '
				AND ' . $query_string) . '
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array_merge($query_params, [
				'lang'          => $this->user_info['language'],
				'fallback_lang' => $this->language,
				'user_id'       => $this->user_info['id'],
				'sort'          => $sort,
				'start'         => $start,
				'limit'         => $items_per_page,
			])
		);

		$items = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$items[$row['page_id']] = [
				'id'           => (int) $row['page_id'],
				'alias'        => $row['alias'],
				'type'         => $row['type'],
				'status'       => (int) $row['status'],
				'num_views'    => (int) $row['num_views'],
				'num_comments' => (int) $row['num_comments'],
				'author_id'    => (int) $row['author_id'],
				'author_name'  => $row['author_name'],
				'created_at'   => $this->getFriendlyTime((int) $row['date']),
				'is_front'     => $this->isFrontpage($row['alias']),
				'title'        => $row['title'] ?: $row['fallback_title'],
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}

	public function getTotalCount(string $query_string = '', array $query_params = []): int
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang})' . ($this->user_info['is_admin'] ? '
			WHERE 1=1' : '
			WHERE p.author_id = {int:user_id}') . (empty($query_string) ? '' : '
				AND ' . $query_string),
			array_merge($query_params, [
				'lang'    => $this->user_info['language'],
				'user_id' => $this->user_info['id'],
			])
		);

		[$num_entries] = $this->smcFunc['db_fetch_row']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return (int) $num_entries;
	}

	/**
	 * Possible actions with pages
	 *
	 * Возможные действия со страницами
	 */
	public function doActions()
	{
		if ($this->request()->has('actions') === false)
			return;

		$data = $this->request()->json();

		if (isset($data['del_item']))
			$this->remove([(int) $data['del_item']]);

		if (isset($data['toggle_item']))
			$this->toggleStatus([(int) $data['toggle_item']], 'page');

		$this->cache()->flush();

		exit;
	}

	public function massActions()
	{
		if ($this->post()->has('mass_actions') === false || $this->post()->isEmpty('items'))
			return;

		$redirect = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_DEFAULT, ['options' => ['default' => 'action=admin;area=lp_pages']]);

		$items = $this->post('items');
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

		redirectexit($redirect);
	}

	public function add()
	{
		loadTemplate('LightPortal/ManagePages');

		$this->context['page_title'] = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_pages_add_title'];
		$this->context['page_area_title'] = $this->txt['lp_pages_add_title'];
		$this->context['canonical_url'] = $this->scripturl . '?action=admin;area=lp_pages;sa=add';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_pages_add_description'],
		];

		$this->prepareForumLanguages();
		$this->validateData();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();
		$this->setData();

		$this->context['sub_template'] = 'page_post';
	}

	public function edit()
	{
		$item = (int) $this->request('id');

		if (empty($item)) {
			fatal_lang_error('lp_page_not_found', false, null, 404);
		}

		loadTemplate('LightPortal/ManagePages');

		$this->context['page_title'] = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_pages_edit_title'];

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_pages_edit_description'],
		];

		$this->context['lp_current_page'] = (new Page)->getDataByItem($item);

		if (empty($this->context['lp_current_page']))
			fatal_lang_error('lp_page_not_found', false, null, 404);

		if ($this->context['lp_current_page']['can_edit'] === false)
			fatal_lang_error('lp_page_not_editable', false);

		$this->prepareForumLanguages();

		if ($this->post()->has('remove')) {
			$this->remove([$item]);
			redirectexit('action=admin;area=lp_pages;sa=main');
		}

		$this->validateData();

		$page_title = $this->context['lp_page']['title'][$this->context['user']['language']] ?? '';
		$this->context['page_area_title'] = $this->txt['lp_pages_edit_title'] . ($page_title ? ' - ' . $page_title : '');
		$this->context['canonical_url'] = $this->scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $this->context['lp_page']['id'];

		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();
		$this->setData($this->context['lp_page']['id']);

		$this->context['sub_template'] = 'page_post';
	}

	private function remove(array $items)
	{
		if (empty($items))
			return;

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
			DELETE FROM {db_prefix}lp_comments
			WHERE page_id IN ({array_int:items})',
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

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}user_likes
			WHERE content_id IN ({array_int:items})
				AND content_type = {literal:lpp}',
			[
				'items' => $items,
			]
		);

		$this->context['lp_num_queries'] += 5;

		$this->hook('onPageRemoving', [$items]);
	}

	private function promote(array $items, string $type = 'up')
	{
		if (empty($items))
			return;

		if ($type === 'down') {
			$items = array_diff($this->context['lp_frontpage_pages'], $items);
		} else {
			$items = array_merge(array_diff($items, $this->context['lp_frontpage_pages']), $this->context['lp_frontpage_pages']);
		}

		updateSettings(['lp_frontpage_pages' => implode(',', $items)]);
	}

	private function getOptions(): array
	{
		$options = [
			'show_title'           => true,
			'show_author_and_date' => true,
			'show_related_pages'   => false,
			'allow_comments'       => false,
		];

		$this->hook('pageOptions', [&$options]);

		return $options;
	}

	private function validateData()
	{
		if ($this->post()->only(['save', 'save_exit', 'preview'])) {
			$args = [
				'category'    => FILTER_VALIDATE_INT,
				'page_author' => FILTER_VALIDATE_INT,
				'alias'       => FILTER_SANITIZE_STRING,
				'description' => FILTER_SANITIZE_STRING,
				'keywords'    => [
					'name'   => 'keywords',
					'filter' => FILTER_SANITIZE_STRING,
					'flags'  => FILTER_REQUIRE_ARRAY,
				],
				'type'        => FILTER_SANITIZE_STRING,
				'permissions' => FILTER_VALIDATE_INT,
				'date'        => FILTER_SANITIZE_STRING,
				'time'        => FILTER_SANITIZE_STRING,
				'content'     => FILTER_UNSAFE_RAW,
			];

			foreach ($this->context['languages'] as $lang) {
				$args['title_' . $lang['filename']] = FILTER_SANITIZE_STRING;
			}

			$parameters = [];

			$this->hook('validatePageData', [&$parameters]);

			$parameters = array_merge(
				[
					'show_title'           => FILTER_VALIDATE_BOOLEAN,
					'show_author_and_date' => FILTER_VALIDATE_BOOLEAN,
					'show_related_pages'   => FILTER_VALIDATE_BOOLEAN,
					'allow_comments'       => FILTER_VALIDATE_BOOLEAN,
				],
				$parameters
			);

			$post_data = filter_input_array(INPUT_POST, array_merge($args, $parameters));
			$post_data['id'] = $this->request('id', 0);

			if (is_null($post_data['keywords'])) {
				$post_data['keywords'] = [];
			}

			$this->findErrors($post_data);
		}

		$options = $this->getOptions();
		$page_options = $this->context['lp_current_page']['options'] ?? $options;

		$dateTime = $this->getDateTime();

		$this->context['lp_page'] = [
			'id'          => (int) ($post_data['id'] ?? $this->context['lp_current_page']['id'] ?? 0),
			'title'       => $this->context['lp_current_page']['title'] ?? [],
			'category'    => $post_data['category'] ?? $this->context['lp_current_page']['category_id'] ?? 0,
			'page_author' => $post_data['page_author'] ?? $this->context['lp_current_page']['author_id'] ?? $this->user_info['id'],
			'alias'       => $post_data['alias'] ?? $this->context['lp_current_page']['alias'] ?? '',
			'description' => $post_data['description'] ?? $this->context['lp_current_page']['description'] ?? '',
			'keywords'    => $post_data['keywords'] ?? $this->context['lp_current_page']['tags'] ?? [],
			'type'        => $post_data['type'] ?? $this->context['lp_current_page']['type'] ?? $this->modSettings['lp_page_editor_type_default'] ?? 'bbc',
			'permissions' => $post_data['permissions'] ?? $this->context['lp_current_page']['permissions'] ?? $this->modSettings['lp_permissions_default'] ?? 2,
			'status'      => $this->context['lp_current_page']['status'] ?? (int) $this->context['allow_light_portal_approve_pages'],
			'created_at'  => $this->context['lp_current_page']['created_at'] ?? time(),
			'date'        => $post_data['date'] ?? $dateTime->format('Y-m-d'),
			'time'        => $post_data['time'] ?? $dateTime->format('H:i'),
			'content'     => $post_data['content'] ?? $this->context['lp_current_page']['content'] ?? '',
			'options'     => $options,
		];

		if (! (empty($this->modSettings['lp_prohibit_php']) || $this->user_info['is_admin']) && $this->context['lp_page']['type'] === 'php') {
			$this->context['lp_page']['type'] = 'bbc';
		}

		foreach ($this->context['lp_page']['options'] as $option => $value) {
			if (isset($parameters[$option]) && isset($post_data) && ! isset($post_data[$option])) {
				if ($parameters[$option] === FILTER_SANITIZE_STRING)
					$post_data[$option] = '';

				if ($parameters[$option] === FILTER_VALIDATE_BOOLEAN)
					$post_data[$option] = 0;

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

	private function findErrors(array $data)
	{
		$post_errors = [];

		if (($this->modSettings['userLanguage'] && empty($data['title_' . $this->language])) || empty($data['title_' .
			$this->context['user']['language']]))
			$post_errors[] = 'no_title';

		if (empty($data['alias']))
			$post_errors[] = 'no_alias';

		$alias_format['options'] = ['regexp' => '/' . self::ALIAS_PATTERN . '/'];
		if ($data['alias'] && empty($this->validate($data['alias'], $alias_format)))
			$post_errors[] = 'no_valid_alias';

		if ($data['alias'] && ! $this->isUnique($data))
			$post_errors[] = 'no_unique_alias';

		if (empty($data['content']))
			$post_errors[] = 'no_content';

		$this->hook('findPageErrors', [$data, &$post_errors]);

		if ($post_errors) {
			$this->post()->put('preview', true);
			$this->context['post_errors'] = [];

			foreach ($post_errors as $error)
				$this->context['post_errors'][] = $this->txt['lp_post_error_' . $error];
		}
	}

	private function prepareFormFields()
	{
		checkSubmitOnce('register');

		$this->prepareIconList();

		$languages = empty($this->modSettings['userLanguage']) ? [$this->language] : [$this->context['user']['language'], $this->language];

		$i = 0;
		foreach ($this->context['languages'] as $lang) {
			$this->context['posting_fields']['title_' . $lang['filename']]['label']['text'] = $this->txt['lp_title'] . (count($this->context['languages']) > 1 ? ' [' . $lang['name'] . ']' : '');
			$this->context['posting_fields']['title_' . $lang['filename']]['input'] = [
				'type'       => 'text',
				'attributes' => [
					'maxlength' => 255,
					'value'     => $this->context['lp_page']['title'][$lang['filename']] ?? '',
					'required'  => in_array($lang['filename'], $languages),
					'style'     => 'width: 100%',
					'x-ref'     => 'title_' . $i++,
				],
				'tab'        => 'content',
			];
		}

		$this->context['posting_fields']['type']['label']['text'] = $this->txt['lp_page_type'];
		$this->context['posting_fields']['type']['input'] = [
			'type'       => 'select',
			'attributes' => [
				'disabled' => empty($this->context['lp_page']['title'][$this->context['user']['language']]) && empty($this->context['lp_page']['alias']),
				'x-ref'    => 'type',
				'@change'  => 'page.toggleType($root)',
			],
			'tab'        => 'content',
		];

		foreach ($this->context['lp_content_types'] as $value => $text) {
			$this->context['posting_fields']['type']['input']['options'][$text] = [
				'value'    => $value,
				'selected' => $value == $this->context['lp_page']['type'],
			];
		}

		$this->context['posting_fields']['content']['label']['html'] = ' ';
		if ($this->context['lp_page']['type'] !== 'bbc') {
			$this->context['posting_fields']['content']['input'] = [
				'type'       => 'textarea',
				'attributes' => [
					'value'    => $this->context['lp_page']['content'],
					'required' => true,
					'style'    => 'height: 300px',
				],
				'tab'        => 'content',
			];
		} else {
			$this->createBbcEditor($this->context['lp_page']['content']);

			ob_start();
			template_control_richedit($this->context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
			$this->context['posting_fields']['content']['input']['html'] = '<div>' . ob_get_clean() . '</div>';

			$this->context['posting_fields']['content']['input']['tab'] = 'content';
		}

		$this->context['posting_fields']['alias']['label']['text'] = $this->txt['lp_page_alias'];
		$this->context['posting_fields']['alias']['input'] = [
			'type'       => 'text',
			'after'      => $this->txt['lp_page_alias_subtext'],
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_page']['alias'],
				'required'  => true,
				'pattern'   => self::ALIAS_PATTERN,
				'style'     => 'width: 100%',
				'x-ref'     => 'alias',
			],
			'tab'        => 'seo',
		];

		$this->context['posting_fields']['description']['label']['text'] = $this->txt['lp_page_description'];
		$this->context['posting_fields']['description']['input'] = [
			'type'       => 'textarea',
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_page']['description'],
			],
			'tab'        => 'seo',
		];

		$this->context['posting_fields']['keywords']['label']['text'] = $this->txt['lp_page_keywords'];
		$this->context['posting_fields']['keywords']['input'] = [
			'type'       => 'select',
			'attributes' => [
				'name'     => 'keywords[]',
				'multiple' => true,
			],
			'options'    => [],
			'tab'        => 'seo',
		];

		$this->context['lp_tags'] = $this->getAllTags();

		foreach ($this->context['lp_tags'] as $value => $text) {
			$this->context['posting_fields']['keywords']['input']['options'][$text] = [
				'value'    => $value,
				'selected' => isset($this->context['lp_page']['keywords'][$value]),
			];
		}

		$this->context['posting_fields']['permissions']['label']['text'] = $this->txt['edit_permissions'];
		$this->context['posting_fields']['permissions']['input'] = [
			'type' => 'select',
		];

		foreach ($this->txt['lp_permissions'] as $level => $title) {
			if (empty($this->context['user']['is_admin']) && empty($level))
				continue;

			$this->context['posting_fields']['permissions']['input']['options'][$title] = [
				'value'    => $level,
				'selected' => $level == $this->context['lp_page']['permissions'],
			];
		}

		$allCategories = $this->getAllCategories();

		$this->context['posting_fields']['category']['label']['text'] = $this->txt['lp_category'];
		$this->context['posting_fields']['category']['input'] = [
			'type'       => 'select',
			'attributes' => [
				'disabled' => count($allCategories) < 2,
			],
		];

		foreach ($allCategories as $value => $category) {
			$this->context['posting_fields']['category']['input']['options'][$category['name']] = [
				'value'    => $value,
				'selected' => $value == $this->context['lp_page']['category'],
			];
		}

		if ($this->context['lp_page']['created_at'] >= time()) {
			$this->context['posting_fields']['datetime']['label']['html'] = '<label for="datetime">' . $this->txt['lp_page_publish_datetime'] . '</label>';
			$this->context['posting_fields']['datetime']['input']['html'] = '
			<input type="date" id="datetime" name="date" min="' . date('Y-m-d') . '" value="' . $this->context['lp_page']['date'] . '">
			<input type="time" name="time" value="' . $this->context['lp_page']['time'] . '">';
		}

		if ($this->context['user']['is_admin']) {
			$this->prepareMemberList();

			$this->context['posting_fields']['page_author']['label']['text'] = $this->txt['lp_page_author'];
			$this->context['posting_fields']['page_author']['input'] = [
				'type'    => 'select',
				'options' => [],
			];
		}

		$this->context['posting_fields']['show_title']['label']['text'] = $this->context['lp_page_options']['show_title'];
		$this->context['posting_fields']['show_title']['input'] = [
			'type'       => 'checkbox',
			'attributes' => [
				'id'      => 'show_title',
				'checked' => (bool) $this->context['lp_page']['options']['show_title'],
			],
		];

		$this->context['posting_fields']['show_author_and_date']['label']['text'] = $this->context['lp_page_options']['show_author_and_date'];
		$this->context['posting_fields']['show_author_and_date']['input'] = [
			'type'       => 'checkbox',
			'attributes' => [
				'id'      => 'show_author_and_date',
				'checked' => (bool) $this->context['lp_page']['options']['show_author_and_date'],
			],
		];

		if (! empty($this->modSettings['lp_show_related_pages'])) {
			$this->context['posting_fields']['show_related_pages']['label']['text'] = $this->context['lp_page_options']['show_related_pages'];
			$this->context['posting_fields']['show_related_pages']['input'] = [
				'type'       => 'checkbox',
				'attributes' => [
					'checked' => (bool) $this->context['lp_page']['options']['show_related_pages'],
				],
			];
		}

		if (! (empty($this->modSettings['lp_show_comment_block']) || $this->modSettings['lp_show_comment_block'] === 'none')) {
			$this->context['posting_fields']['allow_comments']['label']['text'] = $this->context['lp_page_options']['allow_comments'];
			$this->context['posting_fields']['allow_comments']['input'] = [
				'type'       => 'checkbox',
				'attributes' => [
					'checked' => (bool) $this->context['lp_page']['options']['allow_comments'],
				],
			];
		}

		$this->hook('preparePageFields');

		$this->preparePostFields();
	}

	private function prepareMemberList()
	{
		if ($this->request()->has('members') === false)
			return;

		$data = $this->request()->json();

		if (empty($search = $data['search']))
			return;

		$search = trim($this->smcFunc['strtolower']($search)) . '*';
		$search = strtr($search, ['%' => '\%', '_' => '\_', '*' => '%', '?' => '_', '&#038;' => '&amp;']);

		$request = $this->smcFunc['db_query']('', '
			SELECT id_member, real_name
			FROM {db_prefix}members
			WHERE {raw:real_name} LIKE {string:search}
				AND is_activated IN (1, 11)
			LIMIT 1000',
			[
				'real_name' => $this->smcFunc['db_case_sensitive'] ? 'LOWER(real_name)' : 'real_name',
				'search'    => $search,
			]
		);

		$members = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$row['real_name'] = strtr($row['real_name'], ['&amp;' => '&#038;', '&lt;' => '&#060;', '&gt;' => '&#062;', '&quot;' => '&#034;']);

			$members[] = [
				'text'  => $row['real_name'],
				'value' => $row['id_member'],
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		exit(json_encode($members));
	}

	private function prepareEditor()
	{
		$this->hook('prepareEditor', [$this->context['lp_page']]);
	}

	private function preparePreview()
	{
		if ($this->post()->has('preview') === false)
			return;

		checkSubmitOnce('free');

		$this->context['preview_title'] = $this->context['lp_page']['title'][$this->context['user']['language']];
		$this->context['preview_content'] = $this->smcFunc['htmlspecialchars']($this->context['lp_page']['content'], ENT_QUOTES);

		$this->cleanBbcode($this->context['preview_title']);
		censorText($this->context['preview_title']);
		censorText($this->context['preview_content']);

		if ($this->context['preview_content'])
			$this->context['preview_content'] = parse_content($this->context['preview_content'], $this->context['lp_page']['type']);

		$this->context['page_title'] = $this->txt['preview'] . ($this->context['preview_title'] ? ' - ' . $this->context['preview_title'] : '');
		$this->context['preview_title'] = $this->getPreviewTitle();
	}

	private function prepareDescription()
	{
		$this->cleanBbcode($this->context['lp_page']['description']);

		$this->context['lp_page']['description'] = strip_tags($this->context['lp_page']['description']);
	}

	private function prepareKeywords()
	{
		// Remove all punctuation symbols
		$this->context['lp_page']['keywords'] = preg_replace("#[[:punct:]]#", "", $this->context['lp_page']['keywords']);
	}

	private function getPublishTime(): int
	{
		$publish_time = time();

		if ($this->context['lp_page']['date'])
			$publish_time = strtotime($this->context['lp_page']['date']);

		if ($this->context['lp_page']['time'])
			$publish_time = strtotime(date('Y-m-d', $publish_time) . ' ' . $this->context['lp_page']['time']);

		return $publish_time;
	}

	private function setData(int $item = 0)
	{
		if (isset($this->context['post_errors']) || (
			$this->post()->has('save') === false &&
			$this->post()->has('save_exit') === false)
		)
			return;

		checkSubmitOnce('check');

		$this->prepareDescription();
		$this->prepareKeywords();

		$this->prepareBbcContent($this->context['lp_page']);

		if (empty($item)) {
			$item = $this->addData();
		} else {
			$this->updateData($item);
		}

		$this->cache()->flush();

		if ($this->post()->has('save_exit'))
			redirectexit('action=admin;area=lp_pages;sa=main');

		if ($this->post()->has('save'))
			redirectexit('action=admin;area=lp_pages;sa=edit;id=' . $item);
	}

	private function addData(): int
	{
		$this->smcFunc['db_transaction']('begin');

		$item = $this->smcFunc['db_insert']('',
			'{db_prefix}lp_pages',
			array_merge([
				'category_id' => 'int',
				'author_id'   => 'int',
				'alias'       => 'string-255',
				'description' => 'string-255',
				'content'     => 'string',
				'type'        => 'string',
				'permissions' => 'int',
				'status'      => 'int',
				'created_at'  => 'int',
			], $this->db_type === 'postgresql' ? ['page_id' => 'int'] : []),
			array_merge([
				$this->context['lp_page']['category'],
				$this->context['lp_page']['page_author'],
				$this->context['lp_page']['alias'],
				$this->context['lp_page']['description'],
				$this->context['lp_page']['content'],
				$this->context['lp_page']['type'],
				$this->context['lp_page']['permissions'],
				$this->context['lp_page']['status'],
				$this->getPublishTime(),
			], $this->db_type === 'postgresql' ? [$this->getAutoIncrementValue()] : []),
			['page_id'],
			1
		);

		$this->context['lp_num_queries']++;

		if (empty($item)) {
			$this->smcFunc['db_transaction']('rollback');
			return 0;
		}

		$this->hook('onPageSaving', [$item]);

		$this->saveTitles($item);
		$this->saveTags();
		$this->saveOptions($item);

		$this->smcFunc['db_transaction']('commit');

		return $item;
	}

	private function updateData(int $item)
	{
		$this->smcFunc['db_transaction']('begin');

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_pages
			SET category_id = {int:category_id}, author_id = {int:author_id}, alias = {string:alias}, description = {string:description}, content = {string:content}, type = {string:type}, permissions = {int:permissions}, status = {int:status}, updated_at = {int:updated_at}
			WHERE page_id = {int:page_id}',
			[
				'category_id' => $this->context['lp_page']['category'],
				'author_id'   => $this->context['lp_page']['page_author'],
				'alias'       => $this->context['lp_page']['alias'],
				'description' => $this->context['lp_page']['description'],
				'content'     => $this->context['lp_page']['content'],
				'type'        => $this->context['lp_page']['type'],
				'permissions' => $this->context['lp_page']['permissions'],
				'status'      => $this->context['lp_page']['status'],
				'updated_at'  => time(),
				'page_id'     => $item,
			]
		);

		$this->context['lp_num_queries']++;

		$this->hook('onPageSaving', [$item]);

		$this->saveTitles($item, 'replace');
		$this->saveTags();
		$this->saveOptions($item, 'replace');

		$this->smcFunc['db_transaction']('commit');
	}

	private function saveTags()
	{
		$newTagIds = array_diff($this->context['lp_page']['keywords'], array_keys($this->context['lp_tags']));
		$oldTagIds = array_intersect($this->context['lp_page']['keywords'], array_keys($this->context['lp_tags']));

		array_walk($newTagIds, function (&$item) {
			$item = ['value' => $item];
		});

		if ($newTagIds) {
			$newTagIds = $this->smcFunc['db_insert']('',
				'{db_prefix}lp_tags',
				[
					'value' => 'string',
				],
				$newTagIds,
				['tag_id'],
				2
			);

			$this->context['lp_num_queries']++;
		}

		$this->context['lp_page']['options']['keywords'] = array_merge($oldTagIds, $newTagIds);
	}

	private function getAutoIncrementValue(): int
	{
		$request = $this->smcFunc['db_query']('', /** @lang text */ "SELECT setval('{db_prefix}lp_pages_seq', (SELECT MAX(page_id) FROM {db_prefix}lp_pages))");
		[$value] = $this->smcFunc['db_fetch_row']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return (int) $value + 1;
	}

	private function isUnique(array $data): bool
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE alias = {string:alias}
				AND page_id != {int:item}',
			[
				'alias' => $data['alias'],
				'item'  => $data['id'],
			]
		);

		[$count] = $this->smcFunc['db_fetch_row']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $count == 0;
	}
}