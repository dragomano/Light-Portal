<?php

namespace Bugo\LightPortal;

/**
 * ManagePages.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ManagePages
{
	/**
	 * Number pages within tables
	 *
	 * Количество страниц в таблицах
	 *
	 * @var int
	 */
	public static $num_pages = 20;

	/**
	 * The page name must begin with a Latin letter and may consist of lowercase Latin letters, numbers, and underscore
	 *
	 * Имя страницы должно начинаться с латинской буквы и может состоять из строчных латинских букв, цифр и знака подчеркивания
	 *
	 * @var string
	 */
	private static $alias_pattern = '^[a-z][a-z0-9_]+$';

	/**
	 * Manage pages
	 *
	 * Управление страницами
	 *
	 * @return void
	 */
	public static function main()
	{
		global $context, $txt, $smcFunc, $scripturl, $sourcedir;

		loadLanguage('Packages');
		loadTemplate('LightPortal/ManagePages');

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_pages_manage'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_manage_tab_description']
		);

		loadJavaScriptFile('light_portal/manage_pages.js');

		self::doActions();
		self::massActions();

		if (Helpers::request()->filled('params') && Helpers::request()->isEmpty('is_search')) {
			$search_params = base64_decode(strtr(Helpers::request('params'), array(' ' => '+')));
			$search_params = $smcFunc['json_decode']($search_params, true);
		}

		$search_params_string = trim(Helpers::request('search', ''));
		$search_params = array(
			'string' => $smcFunc['htmlspecialchars']($search_params_string)
		);

		$context['search_params'] = empty($search_params_string) ? '' : base64_encode($smcFunc['json_encode']($search_params));
		$context['search'] = array(
			'string' => $search_params['string']
		);

		$listOptions = array(
			'id' => 'pages',
			'items_per_page' => self::$num_pages,
			'title' => $txt['lp_extra_pages'],
			'no_items_label' => $txt['lp_no_items'],
			'base_href' => $scripturl . '?action=admin;area=lp_pages' . (!empty($context['search_params']) ? ';params=' . $context['search_params'] : ''),
			'default_sort_col' => 'date',
			'get_items' => array(
				'function' => __CLASS__ . '::getAll',
				'params' => array(
					(!empty($search_params['string']) ? ' INSTR(LOWER(p.alias), {string:quick_search_string}) > 0 OR INSTR(LOWER(t.title), {string:quick_search_string}) > 0' : ''),
					array('quick_search_string' => $smcFunc['strtolower']($search_params['string']))
				)
			),
			'get_count' => array(
				'function' => __CLASS__ . '::getTotalQuantity',
				'params' => array(
					(!empty($search_params['string']) ? ' INSTR(LOWER(p.alias), {string:quick_search_string}) > 0 OR INSTR(LOWER(t.title), {string:quick_search_string}) > 0' : ''),
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
				'type' => array(
					'header' => array(
						'value' => $txt['package_install_type']
					),
					'data' => array(
						'function' => function ($entry) use ($txt)
						{
							return $txt['lp_page_types'][$entry['type']] ?? strtoupper($entry['type']);
						},
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.type DESC',
						'reverse' => 'p.type'
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
						'function' => function ($entry) use ($scripturl)
						{
							$title = Helpers::getTitle($entry);

							return '<a class="bbc_link' . (
								$entry['is_front']
									? ' new_posts" href="' . $scripturl
									: '" href="' . $scripturl . '?page=' . $entry['alias']
							) . '">' . $title . '</a>';
						},
						'class' => 'word_break'
					),
					'sort' => array(
						'default' => 't.title DESC',
						'reverse' => 't.title'
					)
				),
				'actions' => array(
					'header' => array(
						'value' => $txt['lp_actions'],
						'style' => 'width: 8%'
					),
					'data' => array(
						'function' => function ($entry) use ($txt, $context, $scripturl)
						{
							$actions = '';

							if (allowedTo('light_portal_approve_pages'))
								$actions .= (empty($entry['status']) ? '
							<span class="toggle_status off" data-id="' . $entry['id'] . '" title="' . $txt['lp_action_on'] . '"></span>&nbsp;' : '<span class="toggle_status on" data-id="' . $entry['id'] . '" title="' . $txt['lp_action_off'] . '"></span>&nbsp;');

							if ($context['lp_fontawesome_enabled']) {
								$actions .= '<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $entry['id'] . '"><span class="fas fa-tools" title="' . $txt['edit'] . '"></span></a>' . '
							<span class="fas fa-trash del_page" data-id="' . $entry['id'] . '" title="' . $txt['remove'] . '"></span>';
							} else {
								$actions .= '<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $entry['id'] . '"><span class="main_icons settings" title="' . $txt['edit'] . '"></span></a>' . '
							<span class="main_icons unread_button del_page" data-id="' . $entry['id'] . '" data-alias="' . $entry['alias'] . '" title="' . $txt['remove'] . '"></span>';
							}

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
			'additional_rows' => array(
				array(
					'position' => 'after_title',
					'value' => '
						<i class="fas fa-search centericon"></i>
						<input type="search" name="search" value="' . $context['search']['string'] . '" placeholder="' . $txt['lp_search_pages'] . '">
						<input type="submit" name="is_search" value="' . $txt['search'] . '" class="button" style="float:none">',
					'class' => 'floatright'
				),
				array(
					'position' => 'below_table_data',
					'value' => '
						<select name="page_actions">
							<option value="delete">' . $txt['remove'] . '</option>' . (allowedTo('light_portal_approve_pages') ? '
							<option value="action_on">' . $txt['lp_action_on'] . '</option>
							<option value="action_off">' . $txt['lp_action_off'] . '</option>' : '') . '
						</select>
						<input type="submit" name="mass_actions" value="' . $txt['quick_mod_go'] . '" class="button" onclick="return document.forms.manage_pages.page_actions.value != \'\' && confirm(\'' . $txt['quickmod_confirm'] . '\');">',
					'class' => 'floatright'
				)
			)
		);

		$listOptions['title'] = '
			<span class="floatright">
				<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=add;' . $context['session_var'] . '=' . $context['session_id'] . '">
					<i class="fas fa-plus" title="' . $txt['lp_pages_add'] . '"></i>
				</a>
			</span>' . $listOptions['title'];



		require_once($sourcedir . '/Subs-List.php');
		createList($listOptions);

		$context['pages']['title'] .= ' (' . $context['pages']['total_num_items'] . ')';
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'pages';
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
	public static function getAll(int $start, int $items_per_page, string $sort, string $query_string = '', array $query_params = [])
	{
		global $smcFunc, $user_info;

		$titles = Helpers::cache('all_titles', 'getAllTitles', '\Bugo\LightPortal\Subs', LP_CACHE_TIME, 'page');

		$request = $smcFunc['db_query']('', '
			SELECT p.page_id, p.author_id, p.alias, p.type, p.permissions, p.status, p.num_views, GREATEST(p.created_at, p.updated_at) AS date, mem.real_name AS author_name
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {string:type} AND t.lang = {string:lang})' . ($user_info['is_admin'] ? '
			WHERE 1=1' : '
			WHERE p.author_id = {int:user_id}') . (!empty($query_string) ? '
				AND ' . $query_string : '') . '
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array_merge($query_params, array(
				'type'    => 'page',
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
				'title'       => $titles[$row['page_id']] ?? []
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
	public static function getTotalQuantity(string $query_string = '', array $query_params = [])
	{
		global $smcFunc, $user_info;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {string:type} AND t.lang = {string:lang})' . ($user_info['is_admin'] ? '
			WHERE 1=1' : '
			WHERE p.author_id = {int:user_id}') . (!empty($query_string) ? '
				AND ' . $query_string : ''),
			array_merge($query_params, array(
				'type'    => 'page',
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
	private static function doActions()
	{
		if (Helpers::request()->has('actions') === false)
			return;

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		if (!empty($data['del_item']))
			self::remove([(int) $data['del_item']]);

		if (!empty($data['toggle_status']) && !empty($data['item'])) {
			$item   = (int) $data['item'];
			$status = $data['toggle_status'];

			self::toggleStatus([$item], $status == 'off' ? Page::STATUS_ACTIVE : Page::STATUS_INACTIVE);
		}

		Helpers::cache()->flush();

		exit;
	}

	/**
	 * Removing pages
	 *
	 * Удаление страниц
	 *
	 * @param array $items
	 * @return void
	 */
	private static function remove(array $items)
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
				AND type = {string:type}',
			array(
				'items' => $items,
				'type'  => 'page'
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
				AND type = {string:type}',
			array(
				'items' => $items,
				'type'  => 'page'
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_tags
			WHERE page_id IN ({array_int:items})',
			array(
				'items' => $items
			)
		);

		Subs::runAddons('onPageRemoving', array(&$items));

		$smcFunc['lp_num_queries'] += 5;
	}

	/**
	 * Pages status changing
	 *
	 * Смена статуса страниц
	 *
	 * @param array $items
	 * @param int $status
	 * @return void
	 */
	public static function toggleStatus(array $items, int $status = 0)
	{
		global $smcFunc;

		if (empty($items))
			return;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_pages
			SET status = {int:status}
			WHERE page_id IN ({array_int:items})',
			array(
				'status' => $status,
				'items'  => $items
			)
		);
	}

	/**
	 * Mass actions with pages
	 *
	 * Массовые действия со страницами
	 *
	 * @return void
	 */
	public static function massActions()
	{
		if (Helpers::post()->has('mass_actions') === false || Helpers::post()->isEmpty('items'))
			return;

		$redirect = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_DEFAULT, array('options' => array('default' => 'action=admin;area=lp_pages')));

		$items = Helpers::post('items');
		switch (filter_input(INPUT_POST, 'page_actions')) {
			case 'delete':
				self::remove($items);
				break;

			case 'action_on':
				self::toggleStatus($items, Page::STATUS_ACTIVE);
				break;

			case 'action_off':
				self::toggleStatus($items);
				break;
		}

		redirectexit($redirect);
	}

	/**
	 * Adding a page
	 *
	 * Добавление страницы
	 *
	 * @return void
	 */
	public static function add()
	{
		global $context, $txt, $scripturl;

		loadTemplate('LightPortal/ManagePages');

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_pages_add_title'];
		$context['page_area_title'] = $txt['lp_pages_add_title'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_pages;sa=add';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_add_tab_description']
		);

		Subs::getForumLanguages();

		self::validateData();
		self::prepareFormFields();
		self::prepareEditor();
		self::preparePreview();
		self::setData();

		$context['sub_template'] = 'page_post';
	}

	/**
	 * Editing a page
	 *
	 * Редактирование страницы
	 *
	 * @return void
	 */
	public static function edit()
	{
		global $context, $txt, $scripturl;

		$item = Helpers::request('id');

		if (empty($item))
			fatal_lang_error('lp_page_not_found', false, null, 404);

		loadTemplate('LightPortal/ManagePages');

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_pages_edit_title'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_edit_tab_description']
		);

		$context['lp_current_page'] = Page::getDataByItem($item);

		if (empty($context['lp_current_page']))
			fatal_lang_error('lp_page_not_found', false, null, 404);

		if ($context['lp_current_page']['can_edit'] === false)
			fatal_lang_error('lp_page_not_editable', false);

		Subs::getForumLanguages();

		if (Helpers::post()->has('remove')) {
			self::remove([$item]);
			redirectexit('action=admin;area=lp_pages;sa=main');
		}

		self::validateData();

		$page_title = $context['lp_page']['title'][$context['user']['language']] ?? '';
		$context['page_area_title'] = $txt['lp_pages_edit_title'] . (!empty($page_title) ? ' - ' . $page_title : '');
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $context['lp_page']['id'];

		self::prepareFormFields();
		self::prepareEditor();
		self::preparePreview();
		self::setData($context['lp_page']['id']);

		$context['sub_template'] = 'page_post';
	}

	/**
	 * Get the parameters of all pages
	 *
	 * Получаем параметры всех страниц
	 *
	 * @return array
	 */
	private static function getOptions()
	{
		$options = [
			'show_author_and_date' => true,
			'show_related_pages'   => false,
			'allow_comments'       => false
		];

		Subs::runAddons('pageOptions', array(&$options));

		return $options;
	}

	/**
	 * Validating the sent data
	 *
	 * Валидируем отправляемые данные
	 *
	 * @return void
	 */
	private static function validateData()
	{
		global $context, $modSettings, $user_info;

		if (Helpers::post()->has('save') || Helpers::post()->has('preview')) {
			$args = array(
				'alias'       => FILTER_SANITIZE_STRING,
				'description' => FILTER_SANITIZE_STRING,
				'keywords'    => FILTER_SANITIZE_STRING,
				'type'        => FILTER_SANITIZE_STRING,
				'permissions' => FILTER_VALIDATE_INT,
				'date'        => FILTER_SANITIZE_STRING,
				'time'        => FILTER_SANITIZE_STRING,
				'content'     => FILTER_UNSAFE_RAW
			);

			foreach ($context['languages'] as $lang)
				$args['title_' . $lang['filename']] = FILTER_SANITIZE_STRING;

			$parameters = [];

			Subs::runAddons('validatePageData', array(&$parameters));

			$parameters = array_merge(
				array(
					'show_author_and_date' => FILTER_VALIDATE_BOOLEAN,
					'show_related_pages'   => FILTER_VALIDATE_BOOLEAN,
					'allow_comments'       => FILTER_VALIDATE_BOOLEAN
				),
				$parameters
			);

			$post_data = filter_input_array(INPUT_POST, array_merge($args, $parameters));
			$post_data['id'] = Helpers::request('id', 0);

			self::findErrors($post_data);
		}

		$options = self::getOptions();
		$page_options = $context['lp_current_page']['options'] ?? $options;

		if (!empty($context['lp_current_page']['keywords'])) {
			$context['lp_current_page']['keywords'] = implode(', ', $context['lp_current_page']['keywords']);
		} else {
			$context['lp_current_page']['keywords'] = '';
		}

		$context['lp_page'] = array(
			'id'          => $post_data['id'] ?? $context['lp_current_page']['id'] ?? 0,
			'title'       => $context['lp_current_page']['title'] ?? [],
			'alias'       => $post_data['alias'] ?? $context['lp_current_page']['alias'] ?? '',
			'description' => $post_data['description'] ?? $context['lp_current_page']['description'] ?? '',
			'keywords'    => $post_data['keywords'] ?? $context['lp_current_page']['keywords'] ?? '',
			'type'        => $post_data['type'] ?? $context['lp_current_page']['type'] ?? $modSettings['lp_page_editor_type_default'] ?? 'bbc',
			'permissions' => $post_data['permissions'] ?? $context['lp_current_page']['permissions'] ?? ($user_info['is_admin'] ? 0 : 2),
			'status'      => $user_info['is_admin'] ? 1 : (int) allowedTo('light_portal_approve_pages'),
			'created_at'  => $context['lp_current_page']['created_at'] ?? time(),
			'date'        => $post_data['date'] ?? $context['lp_current_page']['date'] ?? date('Y-m-d'),
			'time'        => $post_data['time'] ?? $context['lp_current_page']['time'] ?? date('H:i'),
			'content'     => $post_data['content'] ?? $context['lp_current_page']['content'] ?? '',
			'options'     => $options
		);

		$context['lp_page']['content'] = Helpers::getShortenText($context['lp_page']['content']);

		foreach ($context['lp_page']['options'] as $option => $value) {
			if (!empty($parameters[$option]) && $parameters[$option] == FILTER_VALIDATE_BOOLEAN && !empty($post_data) && $post_data[$option] === null) {
				$post_data[$option] = 0;
			}

			$context['lp_page']['options'][$option] = $post_data[$option] ?? $page_options[$option] ?? $value;
		}

		foreach ($context['languages'] as $lang)
			$context['lp_page']['title'][$lang['filename']] = $post_data['title_' . $lang['filename']] ?? $context['lp_page']['title'][$lang['filename']] ?? '';

		Helpers::cleanBbcode($context['lp_page']['title']);
	}

	/**
	 * Check that the fields are filled in correctly
	 *
	 * Проверям правильность заполнения полей
	 *
	 * @param array $data
	 * @return void
	 */
	private static function findErrors(array $data)
	{
		global $modSettings, $context, $txt;

		$post_errors = [];

		if ((!empty($modSettings['userLanguage']) ? empty($data['title_english']) : false) || empty($data['title_' . $context['user']['language']]))
			$post_errors[] = 'no_title';

		if (empty($data['alias']))
			$post_errors[] = 'no_alias';

		$alias_format = array(
			'options' => array("regexp" => '/' . static::$alias_pattern . '/')
		);
		if (!empty($data['alias']) && empty(Helpers::validate($data['alias'], $alias_format)))
			$post_errors[] = 'no_valid_alias';

		if (!empty($data['alias']) && self::isUnique($data))
			$post_errors[] = 'no_unique_alias';

		if (empty($data['content']))
			$post_errors[] = 'no_content';

		if (!empty($post_errors)) {
			Helpers::post()->put('preview', true);
			$context['post_errors'] = [];

			foreach ($post_errors as $error)
				$context['post_errors'][] = $txt['lp_post_error_' . $error];
		}
	}

	/**
	 * https://github.com/jshjohnson/Choices
	 *
	 * @return void
	 */
	private static function improveKeywordsField()
	{
		loadCssFile('https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css', array('external' => true));

		addInlineCss('
		.choices__list {
			position: relative;
		}
		.choices__input {
			box-shadow: none;
		}');
	}

	/**
	 * Adding special fields to the form
	 *
	 * Добавляем свои поля для формы
	 *
	 * @return void
	 */
	private static function prepareFormFields()
	{
		global $context, $txt, $modSettings, $language;

		checkSubmitOnce('register');

		$languages = empty($modSettings['userLanguage']) ? [$language] : ['english', $language];

		foreach ($context['languages'] as $lang) {
			$context['posting_fields']['title_' . $lang['filename']]['label']['text'] = $txt['lp_title'] . (count($context['languages']) > 1 ? ' [' . $lang['filename'] . ']' : '');
			$context['posting_fields']['title_' . $lang['filename']]['input'] = array(
				'type' => 'text',
				'attributes' => array(
					'id'        => 'title_' . $lang['filename'],
					'maxlength' => 255,
					'value'     => $context['lp_page']['title'][$lang['filename']] ?? '',
					'required'  => in_array($lang['filename'], $languages),
					'style'     => 'width: 100%'
				),
				'tab' => 'content'
			);
		}

		$context['posting_fields']['alias']['label']['text'] = $txt['lp_page_alias'];
		$context['posting_fields']['alias']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_page_alias_subtext'],
			'attributes' => array(
				'id'        => 'alias',
				'maxlength' => 255,
				'value'     => $context['lp_page']['alias'],
				'required'  => true,
				'pattern'   => static::$alias_pattern,
				'style'     => 'width: 100%'
			),
			'tab' => 'seo'
		);

		$context['posting_fields']['type']['label']['text'] = $txt['lp_page_type'];
		$context['posting_fields']['type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id'       => 'type',
				'disabled' => empty($context['lp_page']['title'][$context['user']['language']]) && empty($context['lp_page']['alias'])
			),
			'options' => array(),
			'tab' => 'content'
		);

		foreach ($txt['lp_page_types'] as $type => $title) {
			if (RC2_CLEAN) {
				$context['posting_fields']['type']['input']['options'][$title]['attributes'] = array(
					'value'    => $type,
					'selected' => $type == $context['lp_page']['type']
				);
			} else {
				$context['posting_fields']['type']['input']['options'][$title] = array(
					'value'    => $type,
					'selected' => $type == $context['lp_page']['type']
				);
			}
		}

		$context['posting_fields']['description']['label']['text'] = $txt['lp_page_description'];
		$context['posting_fields']['description']['input'] = array(
			'type' => 'textarea',
			'attributes' => array(
				'id'        => 'description',
				'maxlength' => 255,
				'value'     => $context['lp_page']['description']
			),
			'tab' => 'seo'
		);

		self::improveKeywordsField();

		$context['posting_fields']['keywords']['label']['text'] = $txt['lp_page_keywords'];
		$context['posting_fields']['keywords']['input'] = array(
			'type' => 'text',
			'attributes' => array(
				'id'    => 'keywords',
				'value' => $context['lp_page']['keywords'],
				'style' => 'width: 100%',
				'dir'   => $context['right_to_left'] ? 'rtl' : 'ltr'
			),
			'tab' => 'seo'
		);

		$context['posting_fields']['permissions']['label']['text'] = $txt['edit_permissions'];
		$context['posting_fields']['permissions']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'permissions'
			),
			'options' => array()
		);

		foreach ($txt['lp_permissions'] as $level => $title) {
			if (RC2_CLEAN) {
				$context['posting_fields']['permissions']['input']['options'][$title]['attributes'] = array(
					'value'    => $level,
					'selected' => $level == $context['lp_page']['permissions']
				);
			} else {
				$context['posting_fields']['permissions']['input']['options'][$title] = array(
					'value'    => $level,
					'selected' => $level == $context['lp_page']['permissions']
				);
			}
		}

		if ($context['lp_page']['created_at'] >= time()) {
			$context['posting_fields']['datetime']['label']['html'] = '<label for="datetime">' . $txt['lp_page_publish_datetime'] . '</label>';
			$context['posting_fields']['datetime']['input']['html'] = '
			<input type="date" id="datetime" name="date" min="' . date('Y-m-d') . '" value="' . $context['lp_page']['date'] . '">
			<input type="time" name="time" value="' . $context['lp_page']['time'] . '">';
		}

		if ($context['lp_page']['type'] !== 'bbc') {
			$context['posting_fields']['content']['label']['text'] = $txt['lp_content'];
			$context['posting_fields']['content']['input'] = array(
				'type' => 'textarea',
				'attributes' => array(
					'id'        => 'content',
					'maxlength' => MAX_MSG_LENGTH,
					'value'     => $context['lp_page']['content'],
					'required'  => true
				),
				'tab' => 'content'
			);
		}

		$context['posting_fields']['show_author_and_date']['label']['text'] = $txt['lp_page_options']['show_author_and_date'];
		$context['posting_fields']['show_author_and_date']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_author_and_date',
				'checked' => !empty($context['lp_page']['options']['show_author_and_date'])
			)
		);

		if (!empty($modSettings['lp_show_related_pages'])) {
			$context['posting_fields']['show_related_pages']['label']['text'] = $txt['lp_page_options']['show_related_pages'];
			$context['posting_fields']['show_related_pages']['input'] = array(
				'type' => 'checkbox',
				'attributes' => array(
					'id'      => 'show_related_pages',
					'checked' => !empty($context['lp_page']['options']['show_related_pages'])
				)
			);
		}

		if (!empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] != 'none') {
			$context['posting_fields']['allow_comments']['label']['text'] = $txt['lp_page_options']['allow_comments'];
			$context['posting_fields']['allow_comments']['input'] = array(
				'type' => 'checkbox',
				'attributes' => array(
					'id'      => 'allow_comments',
					'checked' => !empty($context['lp_page']['options']['allow_comments'])
				)
			);
		}

		Subs::runAddons('preparePageFields');

		foreach ($context['posting_fields'] as $item => $data) {
			if (!empty($data['input']['after']))
				$context['posting_fields'][$item]['input']['after'] = '<div class="descbox alternative smalltext">' . $data['input']['after'] . '</div>';
		}

		loadTemplate('LightPortal/ManageSettings');
	}

	/**
	 * Run the desired editor
	 *
	 * Подключаем нужный редактор
	 *
	 * @return void
	 */
	private static function prepareEditor()
	{
		global $context;

		if ($context['lp_page']['type'] === 'bbc')
			Subs::createBbcEditor($context['lp_page']['content']);

		Subs::runAddons('prepareEditor', array($context['lp_page']));
	}

	/**
	 * Preview
	 *
	 * Предварительный просмотр
	 *
	 * @return void
	 */
	private static function preparePreview()
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
	 * Prepare keywords for saving
	 *
	 * Готовим ключевые слова для сохранения
	 *
	 * @return void
	 */
	private static function prepareKeywords()
	{
		global $context;

		$keywords = !empty($context['lp_page']['keywords']) ? explode(',', $context['lp_page']['keywords']) : [];
		$context['lp_page']['keywords'] = array_map(function ($item) {
			$stop_chars = ['-', '"', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '_', '+', '{', '}', '|', ':', '"', '<', '>', '?', '[', ']', ';', "'", ',', '.', '/', '', '~', '`', '='];
			$new_item   = str_replace($stop_chars, '', $item);

			return trim($new_item);
		}, $keywords);
	}

	/**
	 * Get the date and time of the page publish
	 *
	 * Получаем дату и время публикации страницы
	 *
	 * @return int
	 */
	private static function getPublishTime()
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
	 * Creating or updating a page
	 *
	 * Создаем или обновляем страницу
	 *
	 * @param int $item
	 * @return void
	 */
	private static function setData(int $item = 0)
	{
		global $context, $smcFunc, $db_type;

		if (!empty($context['post_errors']) || Helpers::post()->has('save') === false)
			return;

		checkSubmitOnce('check');

		self::prepareKeywords();

		if (empty($item)) {
			$item = $smcFunc['db_insert']('',
				'{db_prefix}lp_pages',
				array_merge(array(
					'author_id'   => 'int',
					'alias'       => 'string-255',
					'description' => 'string-255',
					'content'     => 'string-' . MAX_MSG_LENGTH,
					'type'        => 'string-4',
					'permissions' => 'int',
					'status'      => 'int',
					'created_at'  => 'int'
				), $db_type == 'postgresql' ? array('page_id' => 'int') : array()),
				array_merge(array(
					$context['user']['id'],
					$context['lp_page']['alias'],
					$context['lp_page']['description'],
					$context['lp_page']['content'],
					$context['lp_page']['type'],
					$context['lp_page']['permissions'],
					$context['lp_page']['status'],
					self::getPublishTime()
				), $db_type == 'postgresql' ? array(self::getAutoIncrementValue()) : array()),
				array('page_id'),
				1
			);

			$smcFunc['lp_num_queries']++;

			Subs::runAddons('onPageSaving', array($item));

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

			if (!empty($context['lp_page']['options'])) {
				$params = [];
				foreach ($context['lp_page']['options'] as $param_name => $value) {
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

			if (!empty($context['lp_page']['keywords'])) {
				$tags = [];
				foreach ($context['lp_page']['keywords'] as $value) {
					$tags[] = array(
						'page_id' => $item,
						'value'   => $value
					);
				}

				$smcFunc['db_insert']('',
					'{db_prefix}lp_tags',
					array(
						'page_id' => 'int',
						'value'   => 'string'
					),
					$tags,
					array('page_id', 'value')
				);

				$smcFunc['lp_num_queries']++;
			}
		} else {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET alias = {string:alias}, description = {string:description}, content = {string:content}, type = {string:type}, permissions = {int:permissions}, status = {int:status}, created_at = {int:created_at}, updated_at = {int:updated_at}
				WHERE page_id = {int:page_id}',
				array(
					'page_id'     => $item,
					'alias'       => $context['lp_page']['alias'],
					'description' => $context['lp_page']['description'],
					'content'     => $context['lp_page']['content'],
					'type'        => $context['lp_page']['type'],
					'permissions' => $context['lp_page']['permissions'],
					'status'      => $context['lp_page']['status'],
					'created_at'  => !empty($context['lp_page']['date']) && !empty($context['lp_page']['time']) ? self::getPublishTime() : $context['lp_page']['created_at'],
					'updated_at'  => time()
				)
			);

			$smcFunc['lp_num_queries']++;

			Subs::runAddons('onPageSaving', array($item));

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

			if (!empty($context['lp_page']['options'])) {
				$params = [];
				foreach ($context['lp_page']['options'] as $param_name => $value) {
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

			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}lp_tags
				WHERE page_id = {int:page_id}',
				array(
					'page_id' => $item
				)
			);

			$smcFunc['lp_num_queries']++;

			if (!empty($context['lp_page']['keywords'])) {
				$tags = [];
				foreach ($context['lp_page']['keywords'] as $value) {
					$tags[] = array(
						'page_id' => $item,
						'value'   => $value
					);
				}

				$smcFunc['db_insert']($db_type == 'postgresql' ? 'ignore' : 'replace',
					'{db_prefix}lp_tags',
					array(
						'page_id' => 'int',
						'value'   => 'string'
					),
					$tags,
					array('page_id', 'value')
				);

				$smcFunc['lp_num_queries']++;
			}
		}

		Helpers::cache()->flush();

		redirectexit('action=admin;area=lp_pages;sa=main');
	}

	/**
	 * Get the correct autoincrement value from lp_pages table
	 *
	 * Получаем правильное значение столбца page_id для создания новой записи
	 *
	 * @return int
	 */
	private static function getAutoIncrementValue()
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', 'SELECT setval(\'{db_prefix}lp_pages_seq\', (SELECT MAX(page_id) FROM {db_prefix}lp_pages))');
		[$value] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return (int) $value + 1;
	}

	/**
	 * We check whether there is already such an alias in the database
	 *
	 * Проверяем, нет ли уже такого алиаса в базе
	 *
	 * @param array $data
	 * @return bool
	 */
	private static function isUnique(array $data)
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

		return (bool) $count;
	}
}
