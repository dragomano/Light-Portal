<?php

namespace Bugo\LightPortal;

/**
 * ManagePages.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ManagePages
{
	use Manageable;

	/**
	 * Number pages within tables
	 *
	 * Количество страниц в таблицах
	 *
	 * @var int
	 */
	public const NUM_PAGES = 20;

	/**
	 * The page name must begin with a Latin letter and may consist of lowercase Latin letters, numbers, and underscore
	 *
	 * Имя страницы должно начинаться с латинской буквы и может состоять из строчных латинских букв, цифр и знака подчеркивания
	 *
	 * @var string
	 */
	private const ALIAS_PATTERN = '^[a-z][a-z0-9_]+$';

	/**
	 * Manage pages
	 *
	 * Управление страницами
	 *
	 * @return void
	 */
	public function main()
	{
		global $context, $txt, $smcFunc, $scripturl;

		loadLanguage('Packages');
		loadTemplate('LightPortal/ManagePages');

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_pages_manage'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => '<a href="https://dragomano.github.io/Light-Portal/" target="_blank" rel="noopener"><span class="main_icons help"></span></a> ' . LP_NAME,
			'description' => $txt['lp_pages_manage_' . ($context['user']['is_admin'] ? 'all' : 'own') . '_pages'] . ' ' . $txt['lp_pages_manage_description']
		);

		$this->doActions();
		$this->massActions();

		$search_params_string = trim(Helpers::request('search', ''));
		$search_params = array(
			'string' => $smcFunc['htmlspecialchars']($search_params_string)
		);

		$context['search_params'] = empty($search_params_string) ? '' : base64_encode($smcFunc['json_encode']($search_params));
		$context['search'] = array(
			'string' => $search_params['string']
		);

		$listOptions = array(
			'id' => 'lp_pages',
			'items_per_page' => self::NUM_PAGES,
			'title' => $txt['lp_pages_extra'],
			'no_items_label' => $txt['lp_no_items'],
			'base_href' => $scripturl . '?action=admin;area=lp_pages' . (!empty($context['search_params']) ? ';params=' . $context['search_params'] : ''),
			'default_sort_col' => 'date',
			'get_items' => array(
				'function' => array($this, 'getAll'),
				'params' => array(
					(!empty($search_params['string']) ? ' (INSTR(LOWER(p.alias), {string:quick_search_string}) > 0 OR INSTR(LOWER(t.title), {string:quick_search_string}) > 0)' : ''),
					array('quick_search_string' => $smcFunc['strtolower']($search_params['string']))
				)
			),
			'get_count' => array(
				'function' => array($this, 'getTotalCount'),
				'params' => array(
					(!empty($search_params['string']) ? ' (INSTR(LOWER(p.alias), {string:quick_search_string}) > 0 OR INSTR(LOWER(t.title), {string:quick_search_string}) > 0)' : ''),
					array('quick_search_string' => $smcFunc['strtolower']($search_params['string']))
				)
			),
			'columns' => array(
				'id' => array(
					'header' => array(
						'value' => '#',
						'style' => 'width: 5%'
					),
					'data' => array(
						'db'    => 'id',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.page_id',
						'reverse' => 'p.page_id DESC'
					)
				),
				'date' => array(
					'header' => array(
						'value' => $txt['date']
					),
					'data' => array(
						'db'    => 'created_at',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'date DESC',
						'reverse' => 'date'
					)
				),
				'num_views' => array(
					'header' => array(
						'value' => $txt['views']
					),
					'data' => array(
						'db'    => 'num_views',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.num_views DESC',
						'reverse' => 'p.num_views'
					)
				),
				'alias' => array(
					'header' => array(
						'value' => $txt['lp_page_alias'],
					),
					'data' => array(
						'db'    => 'alias',
						'class' => 'centertext word_break'
					),
					'sort' => array(
						'default' => 'p.alias DESC',
						'reverse' => 'p.alias'
					)
				),
				'title' => array(
					'header' => array(
						'value' => $txt['lp_title']
					),
					'data' => array(
						'function' => function ($entry) use ($txt, $context, $scripturl)
						{
							$type_hint = $context['lp_page_types'][$entry['type']] ?? strtoupper($entry['type']);

							return '<i class="' . ($context['lp_' . $entry['type']]['icon'] ?? 'fab fa-bimobject') . '" title="' . $type_hint . '"></i> <a class="bbc_link' . (
								$entry['is_front']
									? ' new_posts" href="' . $scripturl
									: '" href="' . $scripturl . '?' . LP_PAGE_ACTION . '=' . $entry['alias']
							) . '">' . $entry['title'] . '</a>';
						},
						'class' => 'word_break'
					),
					'sort' => array(
						'default' => 't.title DESC',
						'reverse' => 't.title'
					)
				),
				'status' => array(
					'header' => array(
						'value' => $txt['status']
					),
					'data' => array(
						'function' => function ($entry) use ($txt)
						{
							if (allowedTo('light_portal_approve_pages')) {
								return '<div data-id="' . $entry['id'] . '" x-data="{status: ' . (empty($entry['status']) ? 'false' : 'true') . '}" x-init="$watch(\'status\', value => page.toggleStatus($el))">
								<span :class="{\'on\': status, \'off\': !status}" :title="status ? \'' . $txt['lp_action_off'] . '\' : \'' . $txt['lp_action_on'] . '\'" @click.prevent="status = !status"></span>
							</div>';
							} else {
								return '<div x-data="{status: ' . (empty($entry['status']) ? 'false' : 'true') . '}">
								<span :class="{\'on\': status, \'off\': !status}" style="cursor: inherit">
							</div>';
							}
						},
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.status DESC',
						'reverse' => 'p.status'
					)
				),
				'actions' => array(
					'header' => array(
						'value' => $txt['lp_actions'],
						'style' => 'width: 8%'
					),
					'data' => array(
						'function' => function ($entry) use ($scripturl, $txt)
						{
							$actions = '<div data-id="' . $entry['id'] . '" x-data="{showContextMenu: false}">
							<div class="context_menu" @click.away="showContextMenu = false">
								<button class="button floatnone" @click.prevent="showContextMenu = true"><i class="fas fa-ellipsis-h"></i></button>
								<div class="roundframe" x-show="showContextMenu">
									<ul>
										<li>
											<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $entry['id'] . '" class="button">' . $txt['modify'] . '</a>
										</li>
										<li>
											<a @click.prevent="showContextMenu = false; page.remove($el)" class="button error">' . $txt['remove'] . '</a>
										</li>
									</ul>
								</div>
							</div>
						</div>';

							return $actions;
						},
						'class' => 'centertext'
					)
				),
				'mass' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);">'
					),
					'data' => array(
						'function' => function ($entry)
						{
							return '<input type="checkbox" value="' . $entry['id'] . '" name="items[]">';
						},
						'class' => 'centertext'
					)
				)
			),
			'form' => array(
				'name' => 'manage_pages',
				'href' => $scripturl . '?action=admin;area=lp_pages',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
					'params' => $context['search_params']
				)
			),
			'javascript' => 'const page = new Page();',
			'additional_rows' => array(
				array(
					'position' => 'after_title',
					'value' => '
						<div class="row">
							<div class="col-lg-10">
								<input type="search" name="search" value="' . $context['search']['string'] . '" placeholder="' . $txt['lp_pages_search'] . '" style="width: 100%">
							</div>
							<div class="col-lg-2">
								<button type="submit" name="is_search" class="button floatnone" style="width: 100%"><i class="fas fa-search"></i> ' . $txt['search'] . '</button>
							</div>
						</div>',
					'class' => 'righttext'
				),
				array(
					'position' => 'below_table_data',
					'value' => '
						<select name="page_actions">
							<option value="delete">' . $txt['remove'] . '</option>' . (allowedTo('light_portal_approve_pages') ? '
							<option value="toggle">' . $txt['lp_action_toggle'] . '</option>' : '') . '
						</select>
						<input type="submit" name="mass_actions" value="' . $txt['quick_mod_go'] . '" class="button" onclick="return document.forms.manage_pages.page_actions.value && confirm(\'' . $txt['quickmod_confirm'] . '\');">',
					'class' => 'floatright'
				)
			)
		);

		$listOptions['title'] = '
			<span class="floatright">
				<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=add;' . $context['session_var'] . '=' . $context['session_id'] . '" x-data>
					<i class="fas fa-plus" @mouseover="page.toggleSpin($event.target)" @mouseout="page.toggleSpin($event.target)" title="' . $txt['lp_pages_add'] . '"></i>
				</a>
			</span>' . $listOptions['title'];



		Helpers::require('Subs-List');
		createList($listOptions);

		$context['lp_pages']['title'] .= ' (' . $context['lp_pages']['total_num_items'] . ')';
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'lp_pages';
	}

	/**
	 * Get the list of pages
	 *
	 * Получаем список страниц
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 * @param string $query_string
	 * @param array $query_params
	 * @return array
	 */
	public function getAll(int $start, int $items_per_page, string $sort, string $query_string = '', array $query_params = []): array
	{
		global $smcFunc, $user_info;

		$request = $smcFunc['db_query']('', '
			SELECT p.page_id, p.author_id, p.alias, p.type, p.permissions, p.status, p.num_views, GREATEST(p.created_at, p.updated_at) AS date, mem.real_name AS author_name, t.title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang})' . ($user_info['is_admin'] ? '
			WHERE 1=1' : '
			WHERE p.author_id = {int:user_id}') . (!empty($query_string) ? '
				AND ' . $query_string : '') . '
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array_merge($query_params, array(
				'lang'    => $user_info['language'],
				'user_id' => $user_info['id'],
				'sort'    => $sort,
				'start'   => $start,
				'limit'   => $items_per_page
			))
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$items[$row['page_id']] = array(
				'id'          => $row['page_id'],
				'alias'       => $row['alias'],
				'type'        => $row['type'],
				'status'      => $row['status'],
				'num_views'   => $row['num_views'],
				'author_id'   => $row['author_id'],
				'author_name' => $row['author_name'],
				'created_at'  => Helpers::getFriendlyTime($row['date']),
				'is_front'    => Helpers::isFrontpage($row['alias']),
				'title'       => $row['title']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $items;
	}

	/**
	 * Get the total number of pages
	 *
	 * Подсчитываем общее количество страниц
	 *
	 * @param string $query_string
	 * @param array $query_params
	 * @return int
	 */
	public function getTotalCount(string $query_string = '', array $query_params = []): int
	{
		global $smcFunc, $user_info;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang})' . ($user_info['is_admin'] ? '
			WHERE 1=1' : '
			WHERE p.author_id = {int:user_id}') . (!empty($query_string) ? '
				AND ' . $query_string : ''),
			array_merge($query_params, array(
				'lang'    => $user_info['language'],
				'user_id' => $user_info['id']
			))
		);

		[$num_entries] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return (int) $num_entries;
	}

	/**
	 * Possible actions with pages
	 *
	 * Возможные действия со страницами
	 *
	 * @return void
	 */
	private function doActions()
	{
		if (Helpers::request()->has('actions') === false)
			return;

		$data = Helpers::request()->json();

		if (!empty($data['del_item']))
			$this->remove([(int) $data['del_item']]);

		if (!empty($data['toggle_item']))
			self::toggleStatus([(int) $data['toggle_item']]);

		Helpers::cache()->flush();

		exit;
	}

	/**
	 * @param array $items
	 * @return void
	 */
	private function remove(array $items)
	{
		global $smcFunc;

		if (empty($items))
			return;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_pages
			WHERE page_id IN ({array_int:items})',
			array(
				'items' => $items
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_titles
			WHERE item_id IN ({array_int:items})
				AND type = {literal:page}',
			array(
				'items' => $items
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_comments
			WHERE page_id IN ({array_int:items})',
			array(
				'items' => $items
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_params
			WHERE item_id IN ({array_int:items})
				AND type = {literal:page}',
			array(
				'items' => $items
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}user_likes
			WHERE content_id IN ({array_int:items})
				AND content_type = {literal:lpp}',
			array(
				'items' => $items
			)
		);

		$smcFunc['lp_num_queries'] += 5;

		Addons::run('onPageRemoving', array($items));
	}

	/**
	 * @param array $items
	 * @return void
	 */
	public static function toggleStatus(array $items = [])
	{
		global $smcFunc;

		if (empty($items))
			return;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_pages
			SET status = !status
			WHERE page_id IN ({array_int:items})',
			array(
				'items' => $items
			)
		);

		$smcFunc['lp_num_queries']++;
	}

	/**
	 * @return void
	 */
	public function massActions()
	{
		if (Helpers::post()->has('mass_actions') === false || Helpers::post()->isEmpty('items'))
			return;

		$redirect = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_DEFAULT, array('options' => array('default' => 'action=admin;area=lp_pages')));

		$items = Helpers::post('items');
		switch (filter_input(INPUT_POST, 'page_actions')) {
			case 'delete':
				$this->remove($items);
				break;

			case 'toggle':
				self::toggleStatus($items);
				break;
		}

		redirectexit($redirect);
	}

	/**
	 * @return void
	 */
	public function add()
	{
		global $context, $txt, $scripturl;

		loadTemplate('LightPortal/ManagePages');

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_pages_add_title'];
		$context['page_area_title'] = $txt['lp_pages_add_title'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_pages;sa=add';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_add_description']
		);

		Helpers::prepareForumLanguages();

		$this->validateData();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();
		$this->setData();

		$context['sub_template'] = 'page_post';
	}

	/**
	 * @return void
	 */
	public function edit()
	{
		global $context, $txt, $scripturl;

		$item = Helpers::request('id');

		if (empty($item))
			fatal_lang_error('lp_page_not_found', false, null, 404);

		loadTemplate('LightPortal/ManagePages');

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_pages_edit_title'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_edit_description']
		);

		$context['lp_current_page'] = (new Page)->getDataByItem($item);

		if (empty($context['lp_current_page']))
			fatal_lang_error('lp_page_not_found', false, null, 404);

		if ($context['lp_current_page']['can_edit'] === false)
			fatal_lang_error('lp_page_not_editable', false);

		Helpers::prepareForumLanguages();

		if (Helpers::post()->has('remove')) {
			$this->remove([$item]);
			redirectexit('action=admin;area=lp_pages;sa=main');
		}

		$this->validateData();

		$page_title = $context['lp_page']['title'][$context['user']['language']] ?? '';
		$context['page_area_title'] = $txt['lp_pages_edit_title'] . (!empty($page_title) ? ' - ' . $page_title : '');
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $context['lp_page']['id'];

		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();
		$this->setData($context['lp_page']['id']);

		$context['sub_template'] = 'page_post';
	}

	/**
	 * @return array
	 */
	private function getOptions(): array
	{
		$options = [
			'show_author_and_date' => true,
			'show_related_pages'   => false,
			'allow_comments'       => false,
			'main_menu_item'       => '',
			'icon'                 => ''
		];

		Addons::run('pageOptions', array(&$options));

		return $options;
	}

	/**
	 * @return void
	 */
	private function validateData()
	{
		global $context, $user_info, $modSettings;

		if (Helpers::post()->only(['save', 'save_exit', 'preview'])) {
			$args = array(
				'category'    => FILTER_VALIDATE_INT,
				'page_author' => FILTER_VALIDATE_INT,
				'alias'       => FILTER_SANITIZE_STRING,
				'description' => FILTER_SANITIZE_STRING,
				'keywords'    => array(
					'name'   => 'keywords',
					'filter' => FILTER_SANITIZE_STRING,
					'flags'  => FILTER_REQUIRE_ARRAY
				),
				'type'        => FILTER_SANITIZE_STRING,
				'permissions' => FILTER_VALIDATE_INT,
				'date'        => FILTER_SANITIZE_STRING,
				'time'        => FILTER_SANITIZE_STRING,
				'content'     => FILTER_UNSAFE_RAW
			);

			foreach ($context['languages'] as $lang) {
				$args['title_' . $lang['filename']]          = FILTER_SANITIZE_STRING;
				$args['main_menu_item_' . $lang['filename']] = FILTER_SANITIZE_STRING;
			}

			$parameters = [];

			Addons::run('validatePageData', array(&$parameters));

			$parameters = array_merge(
				array(
					'show_author_and_date' => FILTER_VALIDATE_BOOLEAN,
					'show_related_pages'   => FILTER_VALIDATE_BOOLEAN,
					'allow_comments'       => FILTER_VALIDATE_BOOLEAN,
					'main_menu_item'       => FILTER_SANITIZE_STRING,
					'icon'                 => FILTER_SANITIZE_STRING
				),
				$parameters
			);

			$post_data = filter_input_array(INPUT_POST, array_merge($args, $parameters));
			$post_data['id'] = Helpers::request('id', 0);

			$this->findErrors($post_data);
		}

		$options = $this->getOptions();
		$page_options = $context['lp_current_page']['options'] ?? $options;

		$context['lp_page'] = array(
			'id'          => $post_data['id'] ?? $context['lp_current_page']['id'] ?? 0,
			'title'       => $context['lp_current_page']['title'] ?? [],
			'category'    => $post_data['category'] ?? $context['lp_current_page']['category_id'] ?? 0,
			'page_author' => $post_data['page_author'] ?? $context['lp_current_page']['author_id'] ?? $user_info['id'],
			'alias'       => $post_data['alias'] ?? $context['lp_current_page']['alias'] ?? '',
			'description' => $post_data['description'] ?? $context['lp_current_page']['description'] ?? '',
			'keywords'    => $post_data['keywords'] ?? $context['lp_current_page']['keywords'] ?? [],
			'type'        => $post_data['type'] ?? $context['lp_current_page']['type'] ?? $modSettings['lp_page_editor_type_default'] ?? 'bbc',
			'permissions' => $post_data['permissions'] ?? $context['lp_current_page']['permissions'] ?? $modSettings['lp_permissions_default'] ?? 2,
			'status'      => $context['lp_current_page']['status'] ?? (int) allowedTo('light_portal_approve_pages'),
			'created_at'  => $context['lp_current_page']['created_at'] ?? time(),
			'date'        => $post_data['date'] ?? $context['lp_current_page']['date'] ?? date('Y-m-d'),
			'time'        => $post_data['time'] ?? $context['lp_current_page']['time'] ?? date('H:i'),
			'content'     => $post_data['content'] ?? $context['lp_current_page']['content'] ?? '',
			'options'     => $options
		);

		if (!empty($modSettings['lp_prohibit_php']) && !$user_info['is_admin'] && $context['lp_page']['type'] == 'php') {
			$context['lp_page']['type'] = 'bbc';
		}

		foreach ($context['lp_page']['options'] as $option => $value) {
			if (!empty($parameters[$option]) && !empty($post_data) && !isset($post_data[$option])) {
				if ($parameters[$option] == FILTER_SANITIZE_STRING)
					$post_data[$option] = '';

				if ($parameters[$option] == FILTER_VALIDATE_BOOLEAN)
					$post_data[$option] = 0;

				if (is_array($parameters[$option]) && $parameters[$option]['flags'] == FILTER_REQUIRE_ARRAY)
					$post_data[$option] = [];
			}

			$context['lp_page']['options'][$option] = $post_data[$option] ?? $page_options[$option] ?? $value;
		}

		if (!empty($context['lp_page']['options']['main_menu_item']))
			$context['lp_page']['options']['main_menu_item'] = json_decode($context['lp_page']['options']['main_menu_item'], true);
		else
			$context['lp_page']['options']['main_menu_item'] = [];

		$context['lp_page']['options']['icon'] = $context['lp_page']['options']['icon'] === 'undefined' ? '' : $context['lp_page']['options']['icon'];

		foreach ($context['languages'] as $lang) {
			$context['lp_page']['title'][$lang['filename']] = $post_data['title_' . $lang['filename']] ?? $context['lp_page']['title'][$lang['filename']] ?? '';
			$context['lp_page']['options']['main_menu_item'][$lang['filename']] = $post_data['main_menu_item_' . $lang['filename']] ?? $context['lp_page']['options']['main_menu_item'][$lang['filename']] ?? '';
		}

		$context['lp_page']['options']['main_menu_item'] = array_filter($context['lp_page']['options']['main_menu_item']);

		Helpers::cleanBbcode($context['lp_page']['title']);
	}

	/**
	 * @param array $data
	 * @return void
	 */
	private function findErrors(array $data)
	{
		global $modSettings, $language, $context, $txt;

		$post_errors = [];

		if ((!empty($modSettings['userLanguage']) && empty($data['title_' . $language])) || empty($data['title_' . $context['user']['language']]))
			$post_errors[] = 'no_title';

		if (empty($data['alias']))
			$post_errors[] = 'no_alias';

		$alias_format = array(
			'options' => array("regexp" => '/' . self::ALIAS_PATTERN . '/')
		);
		if (!empty($data['alias']) && empty(Helpers::validate($data['alias'], $alias_format)))
			$post_errors[] = 'no_valid_alias';

		if (!empty($data['alias']) && !$this->isUnique($data))
			$post_errors[] = 'no_unique_alias';

		if (empty($data['content']))
			$post_errors[] = 'no_content';

		Addons::run('findPageErrors', array($data, &$post_errors));

		if (!empty($post_errors)) {
			Helpers::post()->put('preview', true);
			$context['post_errors'] = [];

			foreach ($post_errors as $error)
				$context['post_errors'][] = $txt['lp_post_error_' . $error];
		}
	}

	/**
	 * @return void
	 */
	private function prepareFormFields()
	{
		global $modSettings, $language, $context, $txt;

		checkSubmitOnce('register');

		$this->improveSelectFields();

		$languages = empty($modSettings['userLanguage']) ? [$language] : [$context['user']['language'], $language];

		$i = 0;
		foreach ($context['languages'] as $lang) {
			$context['posting_fields']['title_' . $lang['filename']]['label']['text'] = $txt['lp_title'] . (count($context['languages']) > 1 ? ' [' . $lang['name'] . ']' : '');
			$context['posting_fields']['title_' . $lang['filename']]['input'] = array(
				'type' => 'text',
				'attributes' => array(
					'maxlength' => 255,
					'value'     => $context['lp_page']['title'][$lang['filename']] ?? '',
					'required'  => in_array($lang['filename'], $languages),
					'style'     => 'width: 100%',
					'x-ref'     => 'title_' . $i++
				),
				'tab' => 'content'
			);
		}

		$context['posting_fields']['type']['label']['text'] = $txt['lp_page_type'];
		$context['posting_fields']['type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'disabled' => empty($context['lp_page']['title'][$context['user']['language']]) && empty($context['lp_page']['alias']),
				'x-ref'    => 'type',
				'@change'  => 'page.toggleType($el)'
			),
			'tab' => 'content'
		);

		foreach ($context['lp_page_types'] as $value => $text) {
			$context['posting_fields']['type']['input']['options'][$text] = array(
				'value'    => $value,
				'selected' => $value == $context['lp_page']['type']
			);
		}

		if ($context['lp_page']['type'] !== 'bbc') {
			$context['posting_fields']['content']['label']['text'] = '';
			$context['posting_fields']['content']['input'] = array(
				'type' => 'textarea',
				'attributes' => array(
					'value'    => $context['lp_page']['content'],
					'required' => true,
					'style'    => 'height: 300px'
				),
				'tab' => 'content'
			);
		}

		$context['posting_fields']['alias']['label']['text'] = $txt['lp_page_alias'];
		$context['posting_fields']['alias']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_page_alias_subtext'],
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_page']['alias'],
				'required'  => true,
				'pattern'   => self::ALIAS_PATTERN,
				'style'     => 'width: 100%',
				'x-ref'     => 'alias'
			),
			'tab' => 'seo'
		);

		$context['posting_fields']['description']['label']['text'] = $txt['lp_page_description'];
		$context['posting_fields']['description']['input'] = array(
			'type' => 'textarea',
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_page']['description']
			),
			'tab' => 'seo'
		);

		$context['posting_fields']['keywords']['label']['text'] = $txt['lp_page_keywords'];
		$context['posting_fields']['keywords']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'name'     => 'keywords[]',
				'multiple' => true
			),
			'options' => [],
			'tab' => 'seo'
		);

		$context['lp_tags'] = Helpers::getAllTags();

		foreach ($context['lp_tags'] as $value => $text) {
			$context['posting_fields']['keywords']['input']['options'][$text] = array(
				'value'    => $value,
				'selected' => isset($context['lp_page']['keywords'][$value])
			);
		}

		if ($context['user']['is_admin']) {
			foreach ($context['languages'] as $lang) {
				$context['posting_fields']['main_menu_item_' . $lang['filename']]['label']['text'] = $context['lp_page_options']['main_menu_item'] . (count($context['languages']) > 1 ? ' [' . $lang['name'] . ']' : '');
				$context['posting_fields']['main_menu_item_' . $lang['filename']]['input'] = array(
					'type' => 'text',
					'attributes' => array(
						'maxlength' => 255,
						'value'     => $context['lp_page']['options']['main_menu_item'][$lang['filename']] ?? '',
						'style'     => 'width: 100%'
					),
					'tab' => 'menu'
				);
			}
		}

		$context['posting_fields']['icon']['label']['text'] = $txt['current_icon'];
		$context['posting_fields']['icon']['input'] = array(
			'type'    => 'select',
			'options' => [],
			'tab'     => 'menu'
		);

		$context['posting_fields']['permissions']['label']['text'] = $txt['edit_permissions'];
		$context['posting_fields']['permissions']['input'] = array(
			'type' => 'select'
		);

		foreach ($txt['lp_permissions'] as $level => $title) {
			if (empty($context['user']['is_admin']) && empty($level))
				continue;

			$context['posting_fields']['permissions']['input']['options'][$title] = array(
				'value'    => $level,
				'selected' => $level == $context['lp_page']['permissions']
			);
		}

		$allCategories = Helpers::getAllCategories();

		$context['posting_fields']['category']['label']['text'] = $txt['lp_category'];
		$context['posting_fields']['category']['input'] = array(
			'type'     => 'select',
			'attributes' => array(
				'disabled' => count($allCategories) < 2
			)
		);

		foreach ($allCategories as $value => $category) {
			$context['posting_fields']['category']['input']['options'][$category['name']] = array(
				'value'    => $value,
				'selected' => $value == $context['lp_page']['category']
			);
		}

		if ($context['lp_page']['created_at'] >= time()) {
			$context['posting_fields']['datetime']['label']['html'] = '<label for="datetime">' . $txt['lp_page_publish_datetime'] . '</label>';
			$context['posting_fields']['datetime']['input']['html'] = '
			<input type="date" id="datetime" name="date" min="' . date('Y-m-d') . '" value="' . $context['lp_page']['date'] . '">
			<input type="time" name="time" value="' . $context['lp_page']['time'] . '">';
		}

		if ($context['user']['is_admin']) {
			$this->prepareMemberList();

			$context['posting_fields']['page_author']['label']['text'] = $txt['lp_page_author'];
			$context['posting_fields']['page_author']['input'] = array(
				'type'    => 'select',
				'options' => []
			);
		}

		$context['posting_fields']['show_author_and_date']['label']['text'] = $context['lp_page_options']['show_author_and_date'];
		$context['posting_fields']['show_author_and_date']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_author_and_date',
				'checked' => !empty($context['lp_page']['options']['show_author_and_date'])
			)
		);

		if (!empty($modSettings['lp_show_related_pages'])) {
			$context['posting_fields']['show_related_pages']['label']['text'] = $context['lp_page_options']['show_related_pages'];
			$context['posting_fields']['show_related_pages']['input'] = array(
				'type' => 'checkbox',
				'attributes' => array(
					'checked' => !empty($context['lp_page']['options']['show_related_pages'])
				)
			);
		}

		if (!empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] != 'none') {
			$context['posting_fields']['allow_comments']['label']['text'] = $context['lp_page_options']['allow_comments'];
			$context['posting_fields']['allow_comments']['input'] = array(
				'type' => 'checkbox',
				'attributes' => array(
					'checked' => !empty($context['lp_page']['options']['allow_comments'])
				)
			);
		}

		Addons::run('preparePageFields');

		$this->preparePostFields();
	}

	/**
	 * @return void
	 */
	private function prepareMemberList()
	{
		global $smcFunc;

		if (Helpers::request()->has('members') === false)
			return;

		$data = Helpers::request()->json();

		if (empty($search = $data['search']))
			return;

		$search = trim($smcFunc['strtolower']($search)) . '*';
		$search = strtr($search, array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_', '&#038;' => '&amp;'));

		$request = $smcFunc['db_query']('', '
			SELECT id_member, real_name
			FROM {db_prefix}members
			WHERE {raw:real_name} LIKE {string:search}
				AND is_activated IN (1, 11)
			LIMIT 1000',
			array(
				'real_name' => $smcFunc['db_case_sensitive'] ? 'LOWER(real_name)' : 'real_name',
				'search'    => $search
			)
		);

		$members = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$row['real_name'] = strtr($row['real_name'], array('&amp;' => '&#038;', '&lt;' => '&#060;', '&gt;' => '&#062;', '&quot;' => '&#034;'));

			$members[] = [
				'text'  => $row['real_name'],
				'value' => $row['id_member']
			];
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		exit(json_encode($members));
	}

	/**
	 * @return void
	 */
	private function prepareEditor()
	{
		global $context;

		if ($context['lp_page']['type'] === 'bbc')
			Helpers::createBbcEditor($context['lp_page']['content']);

		Addons::run('prepareEditor', array($context['lp_page']));
	}

	/**
	 * @return void
	 */
	private function preparePreview()
	{
		global $context, $smcFunc, $txt;

		if (Helpers::post()->has('preview') === false)
			return;

		checkSubmitOnce('free');

		$context['preview_title']   = $context['lp_page']['title'][$context['user']['language']];
		$context['preview_content'] = $smcFunc['htmlspecialchars']($context['lp_page']['content'], ENT_QUOTES);

		Helpers::cleanBbcode($context['preview_title']);
		censorText($context['preview_title']);
		censorText($context['preview_content']);

		if (!empty($context['preview_content']))
			Helpers::parseContent($context['preview_content'], $context['lp_page']['type']);

		$context['page_title']    = $txt['preview'] . ($context['preview_title'] ? ' - ' . $context['preview_title'] : '');
		$context['preview_title'] = Helpers::getPreviewTitle();
	}

	/**
	 * @return void
	 */
	private function prepareDescription()
	{
		global $context;

		Helpers::cleanBbcode($context['lp_page']['description']);

		$context['lp_page']['description'] = strip_tags($context['lp_page']['description']);
	}

	/**
	 * @return void
	 */
	private function prepareKeywords()
	{
		global $context;

		// Remove all punctuation symbols
		$context['lp_page']['keywords'] = preg_replace("#[[:punct:]]#", "", $context['lp_page']['keywords']);
	}

	/**
	 * @return int
	 */
	private function getPublishTime(): int
	{
		global $context;

		$publish_time = time();

		if (!empty($context['lp_page']['date']))
			$publish_time = strtotime($context['lp_page']['date']);

		if (!empty($context['lp_page']['time']))
			$publish_time = strtotime(date('Y-m-d', $publish_time) . ' ' . $context['lp_page']['time']);

		return $publish_time;
	}

	/**
	 * @param int $item
	 * @return void
	 */
	private function setData(int $item = 0)
	{
		global $context;

		if (!empty($context['post_errors']) || (Helpers::post()->has('save') === false && Helpers::post()->has('save_exit') === false))
			return;

		checkSubmitOnce('check');

		$this->prepareDescription();
		$this->prepareKeywords();

		if (empty($item)) {
			$item = $this->addData();
		} else {
			$this->updateData($item);
		}

		Helpers::cache()->flush();

		if (Helpers::post()->has('save_exit'))
			redirectexit('action=admin;area=lp_pages;sa=main');

		if (Helpers::post()->has('save'))
			redirectexit('action=admin;area=lp_pages;sa=edit;id=' . $item);
	}

	/**
	 * @return int
	 */
	private function addData(): int
	{
		global $smcFunc, $db_type, $context;

		$item = $smcFunc['db_insert']('',
			'{db_prefix}lp_pages',
			array_merge(array(
				'category_id' => 'int',
				'author_id'   => 'int',
				'alias'       => 'string-255',
				'description' => 'string-255',
				'content'     => 'string',
				'type'        => 'string',
				'permissions' => 'int',
				'status'      => 'int',
				'created_at'  => 'int'
			), $db_type == 'postgresql' ? array('page_id' => 'int') : array()),
			array_merge(array(
				$context['lp_page']['category'],
				$context['lp_page']['page_author'],
				$context['lp_page']['alias'],
				$context['lp_page']['description'],
				$context['lp_page']['content'],
				$context['lp_page']['type'],
				$context['lp_page']['permissions'],
				$context['lp_page']['status'],
				$this->getPublishTime()
			), $db_type == 'postgresql' ? array($this->getAutoIncrementValue()) : array()),
			array('page_id'),
			1
		);

		$smcFunc['lp_num_queries']++;

		if (empty($item))
			return 0;

		Addons::run('onPageSaving', array($item));

		if (!empty($context['lp_page']['title'])) {
			$titles = [];
			foreach ($context['lp_page']['title'] as $lang => $title) {
				$titles[] = array(
					'item_id' => $item,
					'type'    => 'page',
					'lang'    => $lang,
					'title'   => $title
				);
			}

			$smcFunc['db_insert']('',
				'{db_prefix}lp_titles',
				array(
					'item_id' => 'int',
					'type'    => 'string',
					'lang'    => 'string',
					'title'   => 'string'
				),
				$titles,
				array('item_id', 'type', 'lang')
			);

			$smcFunc['lp_num_queries']++;
		}

		if (!empty($context['lp_page']['keywords'])) {
			$tags = $keywords = [];

			$new_tags = array_diff($context['lp_page']['keywords'], array_keys($context['lp_tags']));
			$old_tags = array_intersect($context['lp_page']['keywords'], array_keys($context['lp_tags']));
			foreach ($new_tags as $value) {
				$tags[] = array(
					'value' => $value
				);
			}

			if (!empty($tags)) {
				$keywords = $smcFunc['db_insert']('',
					'{db_prefix}lp_tags',
					array(
						'value' => 'string'
					),
					$tags,
					array('tag_id'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}

			$context['lp_page']['options']['keywords'] = array_merge($old_tags, $keywords);
		}

		if (!empty($context['lp_page']['options'])) {
			$params = [];
			foreach ($context['lp_page']['options'] as $param_name => $value) {
				if ($param_name == 'main_menu_item') {
					$value = empty($value) ? '' : json_encode($value);
				}

				$value = is_array($value) ? implode(',', $value) : $value;

				$params[] = array(
					'item_id' => $item,
					'type'    => 'page',
					'name'    => $param_name,
					'value'   => $value
				);
			}

			$smcFunc['db_insert']('',
				'{db_prefix}lp_params',
				array(
					'item_id' => 'int',
					'type'    => 'string',
					'name'    => 'string',
					'value'   => 'string'
				),
				$params,
				array('item_id', 'type', 'name')
			);

			$smcFunc['lp_num_queries']++;
		}

		return $item;
	}

	/**
	 * @param int $item
	 */
	private function updateData(int $item)
	{
		global $smcFunc, $context;

		$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET category_id = {int:category_id}, author_id = {int:author_id}, alias = {string:alias}, description = {string:description}, content = {string:content}, type = {string:type}, permissions = {int:permissions}, status = {int:status}, created_at = {int:created_at}, updated_at = {int:updated_at}
				WHERE page_id = {int:page_id}',
			array(
				'page_id'     => $item,
				'category_id' => $context['lp_page']['category'],
				'author_id'   => $context['lp_page']['page_author'],
				'alias'       => $context['lp_page']['alias'],
				'description' => $context['lp_page']['description'],
				'content'     => $context['lp_page']['content'],
				'type'        => $context['lp_page']['type'],
				'permissions' => $context['lp_page']['permissions'],
				'status'      => $context['lp_page']['status'],
				'created_at'  => !empty($context['lp_page']['date']) && !empty($context['lp_page']['time']) ? $this->getPublishTime() : $context['lp_page']['created_at'],
				'updated_at'  => time()
			)
		);

		$smcFunc['lp_num_queries']++;

		Addons::run('onPageSaving', array($item));

		if (!empty($context['lp_page']['title'])) {
			$titles = [];
			foreach ($context['lp_page']['title'] as $lang => $title) {
				$titles[] = array(
					'item_id' => $item,
					'type'    => 'page',
					'lang'    => $lang,
					'title'   => $title
				);
			}

			$smcFunc['db_insert']('replace',
				'{db_prefix}lp_titles',
				array(
					'item_id' => 'int',
					'type'    => 'string',
					'lang'    => 'string',
					'title'   => 'string'
				),
				$titles,
				array('item_id', 'type', 'lang')
			);

			$smcFunc['lp_num_queries']++;
		}

		if (!empty($context['lp_page']['keywords'])) {
			$tags = $keywords = [];

			$new_tags = array_diff($context['lp_page']['keywords'], array_keys($context['lp_tags']));
			$old_tags = array_intersect($context['lp_page']['keywords'], array_keys($context['lp_tags']));
			foreach ($new_tags as $value) {
				$tags[] = array(
					'value' => $value
				);
			}

			if (!empty($tags)) {
				$keywords = $smcFunc['db_insert']('',
					'{db_prefix}lp_tags',
					array(
						'value' => 'string'
					),
					$tags,
					array('tag_id'),
					2
				);

				$smcFunc['lp_num_queries']++;
			}

			$context['lp_page']['options']['keywords'] = array_merge($old_tags, $keywords);
		}

		if (!empty($context['lp_page']['options'])) {
			$params = [];
			foreach ($context['lp_page']['options'] as $param_name => $value) {
				if ($param_name == 'main_menu_item') {
					$value = empty($value) ? '' : json_encode($value);
				}

				$value = is_array($value) ? implode(',', $value) : $value;

				$params[] = array(
					'item_id' => $item,
					'type'    => 'page',
					'name'    => $param_name,
					'value'   => $value
				);
			}

			$smcFunc['db_insert']('replace',
				'{db_prefix}lp_params',
				array(
					'item_id' => 'int',
					'type'    => 'string',
					'name'    => 'string',
					'value'   => 'string'
				),
				$params,
				array('item_id', 'type', 'name')
			);

			$smcFunc['lp_num_queries']++;
		}
	}

	/**
	 * Get the correct autoincrement value from lp_pages table
	 *
	 * Получаем правильное значение столбца page_id для создания новой записи
	 *
	 * @return int
	 */
	private function getAutoIncrementValue(): int
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', 'SELECT setval(\'{db_prefix}lp_pages_seq\', (SELECT MAX(page_id) FROM {db_prefix}lp_pages))');
		[$value] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return (int) $value + 1;
	}

	/**
	 * Check the uniqueness of the alias
	 *
	 * Проверяем уникальность алиаса
	 *
	 * @param array $data
	 * @return bool
	 */
	private function isUnique(array $data): bool
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE alias = {string:alias}
				AND page_id != {int:item}',
			array(
				'alias' => $data['alias'],
				'item'  => $data['id']
			)
		);

		[$count] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $count == 0;
	}
}
