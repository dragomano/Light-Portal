<?php

namespace Bugo\LightPortal;

/**
 * ManagePages.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ManagePages
{
	/**
	 * The page name must begin with a Latin letter and consist of lowercase Latin letters, numbers, and underscore
	 *
	 * Имя страницы должно начинаться с латинской буквы и состоять из строчных латинских букв, цифр и знака подчеркивания
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
		global $context, $txt, $scripturl, $sourcedir;

		loadTemplate('LightPortal/ManagePages');

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_pages_manage'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_manage_tab_description']
		);

		$context['lp_fontawesome_enabled'] = Helpers::doesCurrentThemeContainFontAwesome();

		loadJavaScriptFile('light_portal/manage_pages.js');

		self::doActions();
		self::massActions();

		$listOptions = array(
			'id' => 'pages',
			'items_per_page' => 30,
			'title' => $txt['lp_extra_pages'],
			'no_items_label' => $txt['lp_no_items'],
			'base_href' => $scripturl . '?action=admin;area=lp_pages',
			'default_sort_col' => 'date',
			'get_items' => array(
				'function' => __CLASS__ . '::getAll'
			),
			'get_count' => array(
				'function' => __CLASS__ . '::getTotalQuantity'
			),
			'columns' => array(
				'date' => array(
					'header' => array(
						'value' => $txt['date']
					),
					'data' => array(
						'db'    => 'created_at',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.created_at DESC',
						'reverse' => 'p.created_at'
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
						'value' => $txt['lp_page_type']
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
						'value' => $txt['lp_page_alias']
					),
					'data' => array(
						'db'    => 'alias',
						'class' => 'centertext'
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
							$title = Helpers::getPublicTitle($entry);
							return $entry['status'] && !empty($title) ? ('<a class="bbc_link' . ($entry['is_front'] ? ' noticebox" style="display: block" href="' . $scripturl : '" href="' . $scripturl . '?page=' . $entry['alias']) . '" style="float: none">' . $title . '</a>') : $title;
						}
					)
				),
				'actions' => array(
					'header' => array(
						'value' => $txt['lp_actions'],
						'style' => 'width: 14%'
					),
					'data' => array(
						'function' => function ($entry) use ($txt, $context, $scripturl)
						{
							global $settings;

							$actions = (empty($entry['status']) ? '
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
				'href' => $scripturl . '?action=admin;area=lp_pages',
				'name' => 'pages'
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '
				<div class="floatright">
					<select name="page_actions">
						<option value="delete">' . $txt['remove'] . '</option>
						<option value="action_on">' . $txt['lp_action_on'] . '</option>
						<option value="action_off">' . $txt['lp_action_off'] . '</option>
					</select>
					<input type="submit" name="mass_actions" value="' . $txt['quick_mod_go'] . '" class="button" onclick="return document.forms.pages.actions.value != \'\' && confirm(\'' . $txt['quickmod_confirm'] . '\');">
				</div>'
				)
			)
		);

		$listOptions['title'] = '
			<span class="floatright">
				<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=add;' . $context['session_var'] . '=' . $context['session_id'] . '">
					' . (Helpers::isFontAwesomeEnabled() ? ('<i class="fas fa-plus" title="' . $txt['lp_pages_add'] . '"></i>') : ('<span class="main_icons post_moderation_allow" title="' . $txt['lp_pages_add'] . '"></span>')) . '
				</a>
			</span>' . $listOptions['title'];

		require_once($sourcedir . '/Subs-List.php');
		createList($listOptions);

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
	 * @return array
	 */
	public static function getAll(int $start, int $items_per_page, string $sort)
	{
		global $smcFunc, $user_info;

		$titles = Helpers::useCache('all_titles', 'getAllTitles', '\Bugo\LightPortal\Subs', 3600, 'page');

		$request = $smcFunc['db_query']('', '
			SELECT p.page_id, p.author_id, p.alias, p.type, p.permissions, p.status, p.num_views, p.created_at, mem.real_name AS author_name
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)' . (allowedTo('admin_forum') ? '' : '
			WHERE p.author_id = {int:user_id}') . '
			ORDER BY ' . $sort . '
			LIMIT ' . $start . ', ' . $items_per_page,
			array(
				'user_id' => $user_info['id']
			)
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
				'created_at'  => Helpers::getFriendlyTime($row['created_at']),
				'is_front'    => Helpers::isFrontpage($row['page_id']),
				'title'       => $titles[$row['page_id']] ?? []
			);
		}

		$smcFunc['db_free_result']($request);

		return $items;
	}

	/**
	 * Get the total number of pages
	 *
	 * Подсчитываем общее количество страниц
	 *
	 * @return int
	 */
	public static function getTotalQuantity()
	{
		global $smcFunc, $user_info;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages' . (allowedTo('admin_forum') ? '' : '
			WHERE author_id = {int:user_id}'),
			array(
				'user_id' => $user_info['id']
			)
		);

		list ($num_entries) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return $num_entries;
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
		if (!isset($_REQUEST['actions']))
			return;

		$item = filter_input(INPUT_POST, 'del_page_id', FILTER_VALIDATE_INT);
		if (!empty($item))
			self::remove([$item]);

		if (!empty($_POST['toggle_status']) && !empty($_POST['item'])) {
			$item   = (int) $_POST['item'];
			$status = str_replace('toggle_status ', '', $_POST['toggle_status']);
			self::toggleStatus([$item], $status == 'off' ? Page::STATUS_ACTIVE : Page::STATUS_INACTIVE);
		}

		clean_cache();
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
			DELETE FROM {db_prefix}lp_params
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
			DELETE FROM {db_prefix}lp_tags
			WHERE page_id IN ({array_int:items})',
			array(
				'items' => $items
			)
		);
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
		if (!isset($_POST['mass_actions']) || empty($_POST['items']))
			return;

		$redirect = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_DEFAULT, array('options' => array('default' => 'action=admin;area=lp_pages')));

		$items = $_POST['items'];
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

		clean_cache();
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
		self::showPreview();
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

		loadTemplate('LightPortal/ManagePages');

		$item = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : null;

		if (empty($item))
			fatal_lang_error('lp_page_not_found', false, null, 404);

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_pages_edit_title'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_edit_tab_description']
		);

		$context['lp_current_page'] = Page::getData($item);

		if (empty($context['lp_current_page']))
			fatal_lang_error('lp_page_not_found', false, null, 404);

		if ($context['lp_current_page']['can_edit'] === false)
			fatal_lang_error('lp_page_not_editable', false);

		Subs::getForumLanguages();

		self::validateData();

		$page_title = $context['lp_page']['title'][Helpers::getUserLanguage()] ?? '';
		$context['page_area_title'] = $txt['lp_pages_edit_title'] . (!empty($page_title) ? ' - ' . $page_title : '');
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $context['lp_page']['id'];

		self::prepareFormFields();
		self::prepareEditor();
		self::showPreview();
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

		if (isset($_POST['save']) || isset($_POST['preview'])) {
			$args = array(
				'alias'       => FILTER_SANITIZE_STRING,
				'description' => FILTER_SANITIZE_STRING,
				'keywords'    => FILTER_SANITIZE_STRING,
				'content'     => FILTER_UNSAFE_RAW,
				'type'        => FILTER_SANITIZE_STRING,
				'permissions' => FILTER_VALIDATE_INT
			);

			$source_args = $args;
			Subs::runAddons('validatePageData', array(&$args));
			$parameters = array_merge(array('show_author_and_date' => FILTER_VALIDATE_BOOLEAN, 'allow_comments' => FILTER_VALIDATE_BOOLEAN), array_diff($args, $source_args));

			foreach ($context['languages'] as $lang)
				$args['title_' . $lang['filename']] = FILTER_SANITIZE_STRING;

			$post_data = filter_input_array(INPUT_POST, array_merge($args, $parameters));
			$post_data['id'] = !empty($_GET['id']) ? (int) $_GET['id'] : 0;

			self::findErrors($post_data);
		}

		$options = self::getOptions();
		$page_options = $context['lp_current_page']['options'] ?? $options;

		if (!empty($context['lp_current_page']['keywords']))
			$context['lp_current_page']['keywords'] = implode(', ', $context['lp_current_page']['keywords']);

		$context['lp_page'] = array(
			'id'          => $post_data['id'] ?? $context['lp_current_page']['id'] ?? 0,
			'title'       => $context['lp_current_page']['title'] ?? [],
			'alias'       => $post_data['alias'] ?? $context['lp_current_page']['alias'] ?? '',
			'description' => $post_data['description'] ?? $context['lp_current_page']['description'] ?? '',
			'keywords'    => $post_data['keywords'] ?? $context['lp_current_page']['keywords'] ?? '',
			'content'     => $post_data['content'] ?? $context['lp_current_page']['content'] ?? '',
			'type'        => $post_data['type'] ?? $context['lp_current_page']['type'] ?? $modSettings['lp_page_editor_type_default'] ?? 'bbc',
			'permissions' => $post_data['permissions'] ?? $context['lp_current_page']['permissions'] ?? ($user_info['is_admin'] ? 0 : 2),
			'options'     => $options
		);

		foreach ($context['lp_page']['options'] as $option => $value) {
			if (!empty($parameters[$option]) && $parameters[$option] == FILTER_VALIDATE_BOOLEAN && is_null($post_data[$option]))
				$post_data[$option] = 0;

			$context['lp_page']['options'][$option] = $post_data[$option] ?? $page_options[$option] ?? $value;
		}

		foreach ($context['languages'] as $lang)
			$context['lp_page']['title'][$lang['filename']] = $post_data['title_' . $lang['filename']] ?? $context['lp_page']['title'][$lang['filename']] ?? '';

		$context['lp_page']['title'] = Helpers::cleanBbcode($context['lp_page']['title']);
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
		global $context, $txt;

		$post_errors = [];

		if (empty($data['title_english']) || empty($data['title_' . Helpers::getUserLanguage()]))
			$post_errors[] = 'no_title';

		if (empty($data['alias']))
			$post_errors[] = 'no_alias';

		$alias_format = array(
			'options' => array("regexp" => '/' . static::$alias_pattern . '/')
		);
		if (!empty($data['alias']) && empty(filter_var($data['alias'], FILTER_VALIDATE_REGEXP, $alias_format)))
			$post_errors[] = 'no_valid_alias';

		if (!empty($data['alias']) && self::isUnique($data))
			$post_errors[] = 'no_unique_alias';

		if (empty($data['content']))
			$post_errors[] = 'no_content';

		if (!empty($post_errors)) {
			$_POST['preview'] = true;
			$context['post_errors'] = [];

			foreach ($post_errors as $error)
				$context['post_errors'][] = $txt['lp_post_error_' . $error];
		}
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
		global $context, $txt, $language, $modSettings;

		checkSubmitOnce('register');

		$context['posting_fields']['subject'] = ['no'];

		foreach ($context['languages'] as $lang) {
			$context['posting_fields']['title_' . $lang['filename']]['label']['text'] = $txt['lp_title'] . (count($context['languages']) > 1 ? ' [<strong>' . $lang['filename'] . '</strong>]' : '');
			$context['posting_fields']['title_' . $lang['filename']]['input'] = array(
				'type' => 'text',
				'attributes' => array(
					'id'        => 'title_' . $lang['filename'],
					'maxlength' => 255,
					'value'     => $context['lp_page']['title'][$lang['filename']] ?? '',
					'required'  => in_array($lang['filename'], array('english', $language)),
					'style'     => 'width: 100%'
				)
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
			)
		);

		$context['posting_fields']['type']['label']['text'] = $txt['lp_page_type'];
		$context['posting_fields']['type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id'       => 'type',
				'disabled' => empty($context['lp_page']['title'][Helpers::getUserLanguage()]) && empty($context['lp_page']['alias'])
			),
			'options' => array()
		);

		foreach ($txt['lp_page_types'] as $type => $title) {
			if (!defined('JQUERY_VERSION')) {
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
			)
		);

		$context['posting_fields']['keywords']['label']['text'] = $txt['lp_page_keywords'];
		$context['posting_fields']['keywords']['input'] = array(
			'type' => 'textarea',
			'attributes' => array(
				'id'        => 'keywords',
				'maxlength' => 255,
				'value'     => $context['lp_page']['keywords']
			)
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
			if (!defined('JQUERY_VERSION')) {
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

		if ($context['lp_page']['type'] !== 'bbc') {
			$context['posting_fields']['content']['label']['text'] = $txt['lp_page_content'];
			$context['posting_fields']['content']['input'] = array(
				'type' => 'textarea',
				'attributes' => array(
					'id'        => 'content',
					'maxlength' => Helpers::getMaxMessageLength(),
					'value'     => $context['lp_page']['content'],
					'required'  => true
				)
			);
		}

		$context['posting_fields']['show_author_and_date']['label']['text'] = $txt['lp_page_options']['show_author_and_date'];
		$context['posting_fields']['show_author_and_date']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id' => 'show_author_and_date',
				'checked' => !empty($context['lp_page']['options']['show_author_and_date'])
			)
		);

		if (!empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] != 'none') {
			$context['posting_fields']['allow_comments']['label']['text'] = $txt['lp_page_options']['allow_comments'];
			$context['posting_fields']['allow_comments']['input'] = array(
				'type' => 'checkbox',
				'attributes' => array(
					'id' => 'allow_comments',
					'checked' => !empty($context['lp_page']['options']['allow_comments'])
				)
			);
		}

		Subs::runAddons('preparePageFields');

		foreach ($context['posting_fields'] as $item => $data) {
			if (!empty($data['input']['after']))
				$context['posting_fields'][$item]['input']['after'] = '<div class="information alternative smalltext">' . $data['input']['after'] . '</div>';
		}

		loadTemplate('Post');
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
	private static function showPreview()
	{
		global $context, $smcFunc, $txt;

		if (!isset($_POST['preview']))
			return;

		checkSubmitOnce('free');

		$context['preview_title']   = Helpers::cleanBbcode($context['lp_page']['title'][Helpers::getUserLanguage()]);
		$context['preview_content'] = $smcFunc['htmlspecialchars']($context['lp_page']['content'], ENT_QUOTES);

		if (!empty($context['preview_content']))
			Subs::parseContent($context['preview_content'], $context['lp_page']['type']);

		censorText($context['preview_title']);
		censorText($context['preview_content']);

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
			return trim($item);
		}, $keywords);
	}

	/**
	 * Creating or updating a page
	 *
	 * Создаем или обновляем страницу
	 *
	 * @param int $item
	 * @return void
	 */
	public static function setData(int $item = 0)
	{
		global $context, $smcFunc, $db_type, $modSettings;

		if (!empty($context['post_errors']) || !isset($_POST['save']))
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
					'content'     => 'string-' . Helpers::getMaxMessageLength(),
					'type'        => 'string-4',
					'permissions' => 'int',
					'created_at'  => 'int'
				), $db_type == 'postgresql' ? array('page_id' => 'int') : array()),
				array_merge(array(
					$context['user']['id'],
					$context['lp_page']['alias'],
					$context['lp_page']['description'],
					$context['lp_page']['content'],
					$context['lp_page']['type'],
					$context['lp_page']['permissions'],
					time()
				), $db_type == 'postgresql' ? array(self::getAutoIncrementValue()) : array()),
				array('page_id'),
				1
			);

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
			}

			if (!empty($context['lp_page']['options'])) {
				$parameters = [];
				foreach ($context['lp_page']['options'] as $param_name => $value) {
					$parameters[] = array(
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
					$parameters,
					array('item_id', 'type', 'name')
				);
			}

			if (!empty($context['lp_page']['keywords'])) {
				$keywords = [];
				foreach ($context['lp_page']['keywords'] as $value) {
					$keywords[] = array(
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
					$keywords,
					array('page_id', 'value')
				);
			}
		} else {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET alias = {string:alias}, description = {string:description}, content = {string:content}, type = {string:type}, permissions = {int:permissions}, updated_at = {int:updated_at}
				WHERE page_id = {int:page_id}',
				array(
					'page_id'     => $item,
					'alias'       => $context['lp_page']['alias'],
					'description' => $context['lp_page']['description'],
					'content'     => $context['lp_page']['content'],
					'type'        => $context['lp_page']['type'],
					'permissions' => $context['lp_page']['permissions'],
					'updated_at'  => time()
				)
			);

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
			}

			if (!empty($context['lp_page']['options'])) {
				$parameters = [];
				foreach ($context['lp_page']['options'] as $param_name => $value) {
					$parameters[] = array(
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
					$parameters,
					array('item_id', 'type', 'name')
				);
			}

			if (!empty($context['lp_page']['keywords'])) {
				$keywords = [];
				foreach ($context['lp_page']['keywords'] as $value) {
					$keywords[] = array(
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
					$keywords,
					array('page_id', 'value')
				);
			}
		}

		clean_cache();
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

		$request = $smcFunc['db_query']('', 'SELECT setval(\'{db_prefix}lp_pages_seq\', (SELECT MAX(page_id) FROM {db_prefix}lp_pages))',
			array()
		);

		list ($value) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

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

		list ($count) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return (bool) $count;
	}

	/**
	 * Page export
	 *
	 * Экспорт страниц
	 *
	 * @return void
	 */
	public static function export()
	{
		global $context, $txt, $scripturl, $sourcedir;

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_pages_export'];
		$context['page_area_title'] = $txt['lp_pages_export'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_pages;sa=export';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_export_tab_description']
		);

		Subs::runExport(self::getXmlFile());

		$listOptions = array(
			'id' => 'pages',
			'items_per_page' => 10,
			'title' => $txt['lp_pages_export'],
			'no_items_label' => $txt['lp_no_items'],
			'base_href' => $scripturl . '?action=admin;area=lp_pages;sa=export',
			'default_sort_col' => 'id',
			'get_items' => array(
				'function' => __CLASS__ . '::getAll'
			),
			'get_count' => array(
				'function' => __CLASS__ . '::getTotalQuantity'
			),
			'columns' => array(
				'id' => array(
					'header' => array(
						'value' => '#'
					),
					'data' => array(
						'db'    => 'id',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.page_id DESC',
						'reverse' => 'p.page_id'
					)
				),
				'alias' => array(
					'header' => array(
						'value' => $txt['lp_page_alias']
					),
					'data' => array(
						'db'    => 'alias',
						'class' => 'centertext'
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
							$title = Helpers::getPublicTitle($entry);
							return $entry['status'] && !empty($title) ? ('<a class="button' . ($entry['is_front'] ? ' active" href="' . $scripturl : '" href="' . $scripturl . '?page=' . $entry['alias']) . '" style="float: none">' . $title . '</a>') : $title;
						},
						'class' => 'centertext'
					)
				),
				'actions' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" checked>',
						'style' => 'width: 14%'
					),
					'data' => array(
						'function' => function ($entry)
						{
							return '<input type="checkbox" value="' . $entry['id'] . '" name="items[]" checked>';
						},
						'class' => 'centertext'
					)
				)
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=lp_pages;sa=export'
			),
			'additional_rows' => array(
				array(
					'position' => 'bottom_of_list',
					'value'    => '<input type="submit" name="export_selection" value="' . $txt['lp_export_run'] . '" class="button">'
				)
			)
		);

		require_once($sourcedir . '/Subs-List.php');
		createList($listOptions);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'pages';
	}

	/**
	 * Creating data in XML format
	 *
	 * Формируем данные в XML-формате
	 *
	 * @return array
	 */
	private static function getDataForXml()
	{
		global $smcFunc;

		if (empty($_POST['items']))
			return;

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.alias, p.description, p.content, p.type, p.permissions, p.status, p.num_views, p.num_comments, p.created_at, p.updated_at,
				pt.lang, pt.title, pp.name, pp.value, t.value AS keyword, com.id, com.parent_id, com.author_id AS com_author_id, com.message, com.start, com.created_at AS com_created_at
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS pt ON (pt.item_id = p.page_id AND pt.type = {string:type})
				LEFT JOIN {db_prefix}lp_params AS pp ON (pp.item_id = p.page_id AND pp.type = {string:type})
				LEFT JOIN {db_prefix}lp_tags AS t ON (t.page_id = p.page_id)
				LEFT JOIN {db_prefix}lp_comments AS com ON (com.page_id = p.page_id)
			WHERE p.page_id IN ({array_int:pages})',
			array(
				'type'  => 'page',
				'pages' => $_POST['items']
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (!isset($items[$row['page_id']]))
				$items[$row['page_id']] = array(
					'page_id'      => $row['page_id'],
					'author_id'    => $row['author_id'],
					'alias'        => $row['alias'],
					'description'  => $row['description'],
					'content'      => $row['content'],
					'type'         => $row['type'],
					'permissions'  => $row['permissions'],
					'status'       => $row['status'],
					'num_views'    => $row['num_views'],
					'num_comments' => $row['num_comments'],
					'created_at'   => $row['created_at'],
					'updated_at'   => $row['updated_at']
				);

			if (!empty($row['lang']))
				$items[$row['page_id']]['titles'][$row['lang']] = $row['title'];

			if (!empty($row['name']))
				$items[$row['page_id']]['params'][$row['name']] = $row['value'];

			if (!empty($row['keyword']))
				$items[$row['page_id']]['keywords'][] = $row['keyword'];

			if (!empty($row['message'])) {
				$items[$row['page_id']]['comments'][$row['id']] = array(
					'id'         => $row['id'],
					'parent_id'  => $row['parent_id'],
					'author_id'  => $row['com_author_id'],
					'message'    => $row['message'],
					'start'      => $row['start'],
					'created_at' => $row['com_created_at']
				);
			}
		}

		$smcFunc['db_free_result']($request);

		return $items;
	}

	/**
	 * Get filename with XML data
	 *
	 * Получаем имя файла с XML-данными
	 *
	 * @return string
	 */
	private static function getXmlFile()
	{
		$items = self::getDataForXml();

		if (empty($items))
			return '';

		$xml = new \DomDocument('1.0', 'utf-8');
		$root = $xml->appendChild($xml->createElement('light_portal'));

		$xml->formatOutput = true;

		$xmlElements = $root->appendChild($xml->createElement('pages'));
		foreach ($items as $item) {
			$xmlElement = $xmlElements->appendChild($xml->createElement('item'));
			foreach ($item as $key => $val) {
				$xmlName = $xmlElement->appendChild(in_array($key, ['page_id', 'author_id', 'permissions', 'status', 'num_views', 'num_comments', 'created_at', 'updated_at']) ? $xml->createAttribute($key) : $xml->createElement($key));

				if (in_array($key, ['titles', 'params'])) {
					foreach ($item[$key] as $k => $v) {
						$xmlTitle = $xmlName->appendChild($xml->createElement($k));
						$xmlTitle->appendChild($xml->createTextNode($v));
					}
				} elseif ($key == 'keywords' && !empty($val)) {
					$xmlName->appendChild($xml->createTextNode(implode(', ', array_unique($val))));
				} elseif ($key == 'comments') {
					foreach ($item[$key] as $k => $comment) {
						$xmlComment = $xmlName->appendChild($xml->createElement('comment'));
						foreach ($comment as $label => $text) {
							$xmlCommentElem = $xmlComment->appendChild($label == 'message' ? $xml->createElement($label) : $xml->createAttribute($label));
							$xmlCommentElem->appendChild($xml->createTextNode($text));
						}
					}
				} else {
					$xmlName->appendChild($xml->createTextNode($val));
				}
			}
		}

		$file = sys_get_temp_dir() . '/lp_pages_backup.xml';
		$xml->save($file);

		return $file;
	}

	/**
	 * Page import
	 *
	 * Импорт страниц
	 *
	 * @return void
	 */
	public static function import()
	{
		global $context, $txt, $scripturl;

		loadTemplate('LightPortal/ManageImport');

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_pages_import'];
		$context['page_area_title'] = $txt['lp_pages_import'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_pages;sa=import';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_import_tab_description']
		);

		$context['sub_template'] = 'manage_import';

		self::runImport();
	}

	/**
	 * Import from an XML file
	 *
	 * Импорт из XML-файла
	 *
	 * @return void
	 */
	private static function runImport()
	{
		global $boarddir, $smcFunc;

		if (empty($_FILES['import_file']))
			return;

		$file = $_FILES['import_file'];

		if ($file['type'] !== 'text/xml')
			return;

		$xml = simplexml_load_file($file['tmp_name']);

		if ($xml === false)
			return;

		$items = $titles = $params = $keywords = $comments = [];

		foreach ($xml as $element) {
			foreach ($element->item as $item) {
				$items[] = [
					'page_id'      => $page_id = intval($item['page_id']),
					'author_id'    => intval($item['author_id']),
					'alias'        => $item->alias,
					'description'  => $item->description,
					'content'      => $item->content,
					'type'         => $item->type,
					'permissions'  => intval($item['permissions']),
					'status'       => intval($item['status']),
					'num_views'    => intval($item['num_views']),
					'num_comments' => intval($item['num_comments']),
					'created_at'   => intval($item['created_at']),
					'updated_at'   => intval($item['updated_at'])
				];

				if (!empty($item->titles)) {
					foreach ($item->titles as $title) {
						foreach ($title as $k => $v) {
							$titles[] = [
								'item_id' => $page_id,
								'type'    => 'page',
								'lang'    => $k,
								'title'   => $v
							];
						}
					}
				}

				if (!empty($item->params)) {
					foreach ($item->params as $param) {
						foreach ($param as $k => $v) {
							$params[] = [
								'item_id' => $page_id,
								'type'    => 'page',
								'name'    => $k,
								'value'   => $v
							];
						}
					}
				}

				if (!empty($item->keywords)) {
					foreach (explode(', ', $item->keywords) as $value) {
						$keywords[] = [
							'page_id' => $page_id,
							'value'   => $value
						];
					}
				}

				if (!empty($item->comments)) {
					foreach ($item->comments as $comment) {
						foreach ($comment as $k => $v) {
							$comments[] = [
								'id'         => $v['id'],
								'parent_id'  => $v['parent_id'],
								'page_id'    => $page_id,
								'author_id'  => $v['author_id'],
								'message'    => $v->message,
								'start'      => $v['start'],
								'created_at' => $v['created_at']
							];
						}
					}
				}
			}
		}

		if (!empty($items)) {
			$sql = "REPLACE INTO {db_prefix}lp_pages (`page_id`, `author_id`, `alias`, `description`, `content`, `type`, `permissions`, `status`, `num_views`, `num_comments`, `created_at`, `updated_at`)
				VALUES ";

			$sql .= Subs::getValues($items);

			$smcFunc['db_query']('', $sql);
		}

		if (!empty($titles)) {
			$sql = "REPLACE INTO {db_prefix}lp_titles (`item_id`, `type`, `lang`, `title`)
				VALUES ";

			$sql .= Subs::getValues($titles);

			$smcFunc['db_query']('', $sql);
		}

		if (!empty($params)) {
			$sql = "REPLACE INTO {db_prefix}lp_params (`item_id`, `type`, `name`, `value`)
				VALUES ";

			$sql .= Subs::getValues($params);

			$smcFunc['db_query']('', $sql);
		}

		if (!empty($keywords)) {
			$sql = "REPLACE INTO {db_prefix}lp_tags (`page_id`, `value`)
				VALUES ";

			$sql .= Subs::getValues($keywords);

			$smcFunc['db_query']('', $sql);
		}

		if (!empty($comments)) {
			$sql = "REPLACE INTO {db_prefix}lp_comments (`id`, `parent_id`, `page_id`, `author_id`, `message`, `start`, `created_at`)
				VALUES ";

			$sql .= Subs::getValues($comments);

			$smcFunc['db_query']('', $sql);
		}

		clean_cache();
	}
}
