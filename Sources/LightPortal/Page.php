<?php

namespace Bugo\LightPortal;

/**
 * Page.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.9.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Page
{
	/**
	 * The page name must begin with a Latin letter and consist of lowercase Latin letters and numbers
	 *
	 * Имя страницы должно начинаться с латинской буквы и состоять из строчных латинских букв и цифр
	 *
	 * @var string
	 */
	private static $alias_pattern = '^[a-z][a-z0-9]+$';

	/**
	 * Display the page by its alias
	 *
	 * Просматриваем страницу по её алиасу
	 *
	 * @param string $alias
	 * @return void
	 */
	public static function show($alias = '/')
	{
		global $context, $modSettings, $txt, $scripturl;

		isAllowedTo('light_portal_view');

		if (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'tags')
			Tag::show();

		$alias = explode(';', $alias)[0];

		$context['lp_page'] = self::getData($alias);

		if ($context['lp_page']['can_show'] === false && !$context['user']['is_admin'])
			fatal_lang_error('cannot_light_portal_view_page', false);

		Subs::parseContent($context['lp_page']['content'], $context['lp_page']['type']);
		Subs::setMeta();

		if (empty($context['current_action']))
			Block::display();

		if ($alias === '/') {
			$context['page_title'] = $modSettings['lp_frontpage_title_' . $context['user']['language']] ?? $txt['lp_portal'];
		} else {
			$context['page_title']    = $context['lp_page']['title'];
			$context['canonical_url'] = $scripturl . '?page=' . $alias;
		}

		$context['linktree'][] = array(
			'name' => $context['page_title']
		);

		loadTemplate('LightPortal/ViewPage');

		if (!empty($modSettings['lp_frontpage_mode']) && empty($_GET['page'])) {
			$limit = !empty($modSettings['lp_num_per_page']) ? (int) $modSettings['lp_num_per_page'] : 10;
			self::calculateNumColumns();

			if ($modSettings['lp_frontpage_mode'] == 1) {
				Subs::prepareArticles('topics');
				$context['sub_template'] = 'show_topics_as_articles';
			} elseif ($modSettings['lp_frontpage_mode'] == 2) {
				Subs::prepareArticles();
				$context['sub_template'] = 'show_pages_as_articles';
			} else {
				Subs::prepareArticles('boards');
				$context['sub_template'] = 'show_boards_as_articles';
			}
		} else {
			$context['sub_template'] = 'show_page';
			self::updateNumViews();
		}

		if (isset($_REQUEST['page'])) {
			if ($_REQUEST['page'] !== $alias)
				redirectexit('page=' . $alias);
			elseif ($_REQUEST['page'] === '/')
				redirectexit();
		}
	}

	/**
	 * Calculate the number columns for the frontpage layout
	 *
	 * Подсчитываем количество колонок для макета главной страницы
	 *
	 * @return void
	 */
	private static function calculateNumColumns()
	{
		global $modSettings, $context;

		$num_columns = 12;

		if (!empty($modSettings['lp_frontpage_layout'])) {
			switch ($modSettings['lp_frontpage_layout']) {
				case '1':
					$num_columns = 12 / 2;
					break;
				case '2':
					$num_columns = 12 / 3;
					break;
				case '3':
					$num_columns = 12 / 4;
					break;
				default:
					$num_columns = 12 / 6;
			}
		}

		$context['lp_frontpage_layout'] = $num_columns;
	}

	/**
	 * Manage pages
	 *
	 * Управление страницами
	 *
	 * @return void
	 */
	public static function manage()
	{
		global $context, $txt, $modSettings, $scripturl, $sourcedir;

		loadTemplate('LightPortal/ManagePages');

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_pages_manage'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_manage_tab_description']
		);

		if (allowedTo('admin_forum'))
			$context['template_layers'][] = 'manage_pages';

		self::postActions();

		$context['lp_main_page'] = self::getData('/');

		$listOptions = array(
			'id' => 'pages',
			'items_per_page' => 10,
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
						'function' => function ($entry) use ($scripturl)
						{
							return $entry['status'] ? '<a href="' . $scripturl . '?page=' . $entry['alias'] . '">' . $entry['alias'] . '</a>' : $entry['alias'];
						},
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
							return $entry['status'] ? '<a href="' . $scripturl . '?page=' . $entry['alias'] . '">' . $entry['title'] . '</a>' : $entry['title'];
						},
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'p.title DESC',
						'reverse' => 'p.title'
					)
				),
				'actions' => array(
					'header' => array(
						'value' => $txt['lp_actions'],
						'style' => 'width: 14%'
					),
					'data' => array(
						'function' => function ($entry) use ($txt, $scripturl)
						{
							global $settings;

							$actions = (empty($entry['status']) ? '
							<span class="toggle_status off" data-id="' . $entry['id'] . '" title="' . $txt['lp_action_on'] . '"></span>' : '<span class="toggle_status on" data-id="' . $entry['id'] . '" title="' . $txt['lp_action_off'] . '"></span>&nbsp;');

							if ($settings['name'] == 'Lunarfall') {
								$actions .= '<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $entry['id'] . '"><span class="fas fa-edit settings" title="' . $txt['edit'] . '"></span></a>' . '
							<span class="fas fa-trash unread_button del_page" data-id="' . $entry['id'] . '" title="' . $txt['remove'] . '"></span>';
							} else {
								$actions .= '<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $entry['id'] . '"><span class="main_icons settings" title="' . $txt['edit'] . '"></span></a>' . '
							<span class="main_icons unread_button del_page" data-id="' . $entry['id'] . '" title="' . $txt['remove'] . '"></span>';
							}

							return $actions;
						},
						'class' => 'centertext',
						'style' => 'cursor: pointer'
					)
				)
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=lp_pages'
			)
		);

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
	public static function getAll($start, $items_per_page, $sort)
	{
		global $smcFunc, $user_info;

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.title, p.alias, p.type, p.permissions, p.status, p.num_views,
				GREATEST(p.created_at, p.updated_at) AS date, mem.real_name AS author_name
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)
			WHERE p.alias != {string:alias}' . (allowedTo('admin_forum') ? '' : '
				AND p.author_id = {int:user_id}') . '
			ORDER BY ' . $sort . ', p.page_id
			LIMIT ' . $start . ', ' . $items_per_page,
			array(
				'alias'   => '/',
				'user_id' => $user_info['id']
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$items[] = array(
				'id'          => $row['page_id'],
				'title'       => $row['title'],
				'alias'       => $row['alias'],
				'type'        => $row['type'],
				'status'      => $row['status'],
				'num_views'   => $row['num_views'],
				'author_id'   => $row['author_id'],
				'author_name' => $row['author_name'],
				'created_at'  => Helpers::getFriendlyTime($row['date'])
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
			FROM {db_prefix}lp_pages
			WHERE alias != {string:alias}' . (allowedTo('admin_forum') ? '' : '
				AND author_id = {int:user_id}'),
			array(
				'alias'   => '/',
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
	private static function postActions()
	{
		if (!isset($_REQUEST['actions']))
			return;

		self::remove();

		if (!empty($_POST['toggle_status']) && !empty($_POST['item'])) {
			$item   = (int) $_POST['item'];
			$status = str_replace('toggle_status ', '', $_POST['toggle_status']);

			if ($item == 1)
				updateSettings(array('lp_frontpage_disable' => $status == 'off' ? 0 : 1));

			self::toggleStatus($item, $status == 'off' ? 1 : 0);
		}

		clean_cache();
	}

	/**
	 * Deleting a page
	 *
	 * Удаление страницы
	 *
	 * @return void
	 */
	private static function remove()
	{
		global $smcFunc;

		$item = filter_input(INPUT_POST, 'del_page', FILTER_VALIDATE_INT);

		if (empty($item))
			return;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_pages
			WHERE page_id = {int:id}',
			array(
				'id' => $item
			)
		);
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_params
			WHERE item_id = {int:id}
				AND type = {string:type}',
			array(
				'id'   => $item,
				'type' => 'page'
			)
		);
	}

	/**
	 * Changing the page status
	 *
	 * Смена статуса страницы
	 *
	 * @param int $item
	 * @param int $status
	 * @return void
	 */
	public static function toggleStatus($item, $status)
	{
		global $smcFunc;

		if (empty($item) || !isset($status))
			return;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_pages
			SET status = {int:status}
			WHERE page_id = {int:id}',
			array(
				'status' => $status,
				'id'     => $item
			)
		);
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

		$item = !empty($_REQUEST['page_id']) ? (int) $_REQUEST['page_id'] : null;
		$item = $item ?: (!empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : null);

		if (empty($item))
			fatal_lang_error('lp_page_not_found', false, null, 404);

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_pages_edit_title'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_pages_edit_tab_description']
		);

		$context['lp_current_page'] = self::getData($item, is_int($item) ? false : true);

		if ($context['lp_current_page']['can_edit'] === false)
			fatal_lang_error('lp_page_not_editable', false);

		Subs::getForumLanguages();

		self::validateData();

		$context['page_area_title'] = $txt['lp_pages_edit_title'] . ' - ' . $context['lp_page']['title'];
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
			'show_author_and_date' => true
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
		global $context, $modSettings;

		if (isset($_POST['save']) || isset($_POST['preview'])) {
			$args = array(
				'page_id'     => FILTER_VALIDATE_INT,
				'title'       => FILTER_SANITIZE_STRING,
				'alias'       => FILTER_SANITIZE_STRING,
				'description' => FILTER_SANITIZE_STRING,
				'keywords'    => FILTER_SANITIZE_STRING,
				'content'     => FILTER_UNSAFE_RAW,
				'type'        => FILTER_SANITIZE_STRING,
				'permissions' => FILTER_VALIDATE_INT
			);

			$source_args = $args;

			Subs::runAddons('validatePageData', array(&$args));

			$parameters = array_merge(array('show_author_and_date' => FILTER_VALIDATE_BOOLEAN), array_diff($args, $source_args));

			foreach ($context['languages'] as $lang)
				$args['title_' . $lang['filename']] = FILTER_SANITIZE_STRING;

			$post_data = filter_input_array(INPUT_POST, array_merge($args, $parameters));

			self::findErrors($post_data);
		}

		$options = self::getOptions();

		$page_options = $context['lp_current_page']['options'] ?? $options;

		$context['lp_page'] = array(
			'id'          => $post_data['page_id'] ?? $context['lp_current_page']['id'] ?? 0,
			'title'       => $post_data['title'] ?? $context['lp_current_page']['title'] ?? '',
			'alias'       => $post_data['alias'] ?? $context['lp_current_page']['alias'] ?? '',
			'description' => $post_data['description'] ?? $context['lp_current_page']['description'] ?? '',
			'keywords'    => $post_data['keywords'] ?? $context['lp_current_page']['keywords'] ?? '',
			'content'     => $post_data['content'] ?? $context['lp_current_page']['content'] ?? '',
			'type'        => $post_data['type'] ?? $context['lp_current_page']['type'] ?? $modSettings['lp_page_editor_type_default'] ?? 'bbc',
			'permissions' => $post_data['permissions'] ?? $context['lp_current_page']['permissions'] ?? 0,
			'options'     => $options
		);

		foreach ($context['lp_page']['options'] as $option => $value) {
			if (!empty($parameters[$option]) && $parameters[$option] == FILTER_VALIDATE_BOOLEAN && is_null($post_data[$option]))
				$post_data[$option] = 0;

			$context['lp_page']['options'][$option] = $post_data[$option] ?? $page_options[$option] ?? $value;
		}

		if ($context['lp_page']['alias'] === '/') {
			foreach ($context['languages'] as $lang)
				$context['lp_page']['title_' . $lang['filename']] = $post_data['title_' . $lang['filename']] ?? $modSettings['lp_frontpage_title_' . $lang['filename']] ?? '';
		}
	}

	/**
	 * Check that the fields are filled in correctly
	 *
	 * Проверям правильность заполнения полей
	 *
	 * @param array $data
	 * @return void
	 */
	private static function findErrors($data)
	{
		global $context, $txt;

		$post_errors = [];

		if (!empty($context['lp_current_page']) && $context['lp_current_page']['alias'] === '/') {
			if (empty($data['title_' . $context['user']['language']]))
				$post_errors[] = 'no_title';
		} else {
			if (empty($data['title']))
				$post_errors[] = 'no_title';
		}

		if (empty($data['alias']) && !empty($context['lp_current_page']) && $context['lp_current_page']['alias'] !== '/')
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
		global $context, $txt;

		checkSubmitOnce('register');

		$context['posting_fields']['subject'] = ['no'];

		if ($context['lp_page']['alias'] === '/') {
			foreach ($context['languages'] as $lang) {
				$context['posting_fields']['title_' . $lang['filename']]['label']['text'] = $txt['lp_title'] . ' [<strong>' . $lang['filename'] . '</strong>]';
				$context['posting_fields']['title_' . $lang['filename']]['input'] = array(
					'type' => 'text',
					'attributes' => array(
						'maxlength' => 255,
						'value'     => $context['lp_page']['title_' . $lang['filename']],
						'style'     => 'width: 100%'
					)
				);
			}
		} else {
			$context['posting_fields']['title']['label']['text'] = $txt['lp_title'];
			$context['posting_fields']['title']['input'] = array(
				'type' => 'text',
				'attributes' => array(
					'maxlength' => 255,
					'value'     => $context['lp_page']['title'],
					'required'  => true,
					'style'     => 'width: 100%'
				)
			);
		}

		$context['posting_fields']['alias']['label']['text'] = $txt['lp_page_alias'];
		$context['posting_fields']['alias']['input'] = array(
			'type' => 'text',
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_page']['alias'],
				'required'  => true,
				'disabled'  => $context['lp_page']['alias'] === '/',
				'pattern'   => static::$alias_pattern,
				'style'     => 'width: 100%'
			)
		);

		$context['posting_fields']['type']['label']['text'] = $txt['lp_page_type'];
		$context['posting_fields']['type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id'       => 'type',
				'disabled' => empty($context['lp_page']['title']) && empty($context['lp_page']['alias'])
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
				'maxlength' => 255,
				'value'     => $context['lp_page']['description']
			)
		);

		$context['posting_fields']['keywords']['label']['text'] = $txt['lp_page_keywords'];
		$context['posting_fields']['keywords']['input'] = array(
			'type' => 'textarea',
			'attributes' => array(
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

		Subs::runAddons('preparePageFields');

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

		$lang  = Helpers::getUserLanguage();
		$title = isset($context['lp_page']['title_' . $lang]) ? $context['lp_page']['title_' . $lang] : $context['lp_page']['title'];
		$context['preview_title']   = Helpers::cleanBbcode($title);
		$context['preview_content'] = $smcFunc['htmlspecialchars']($context['lp_page']['content'], ENT_QUOTES);

		if (!empty($context['preview_content']))
			Subs::parseContent($context['preview_content'], $context['lp_page']['type']);

		censorText($context['preview_title']);
		censorText($context['preview_content']);

		$context['page_title']    = $txt['preview'] . ' - ' . $context['preview_title'];
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

		$context['lp_page']['keywords'] = explode(',', $context['lp_page']['keywords']);
		$context['lp_page']['keywords'] = array_map(function ($item) {
			return trim($item);
		}, $context['lp_page']['keywords']);
		$context['lp_page']['keywords'] = implode(', ', $context['lp_page']['keywords']);
	}

	/**
	 * Creating or updating a page
	 *
	 * Создаем или обновляем страницу
	 *
	 * @param int $item
	 * @return void
	 */
	public static function setData($item = null)
	{
		global $context, $smcFunc, $db_type;

		if (!empty($context['post_errors']) || !isset($_POST['save']))
			return;

		checkSubmitOnce('check');

		self::prepareKeywords();

		if (empty($item)) {
			$item = $smcFunc['db_insert']('',
				'{db_prefix}lp_pages',
				array_merge(array(
					'author_id'   => 'int',
					'title'       => 'string-255',
					'alias'       => 'string-255',
					'description' => 'string-255',
					'keywords'    => 'string-255',
					'content'     => 'string-' . Helpers::getMaxMessageLength(),
					'type'        => 'string-4',
					'permissions' => 'int',
					'created_at'  => 'int'
				), $db_type == 'postgresql' ? array('page_id' => 'int') : array()),
				array_merge(array(
					$context['user']['id'],
					$context['lp_page']['title'],
					$context['lp_page']['alias'],
					$context['lp_page']['description'],
					$context['lp_page']['keywords'],
					$context['lp_page']['content'],
					$context['lp_page']['type'],
					$context['lp_page']['permissions'],
					time()
				), $db_type == 'postgresql' ? array($page_id = self::getAutoIncrementValue()) : array()),
				array('page_id'),
				1
			);

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
					array('item_id', 'name')
				);
			}
		} else {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET title = {string:title}, alias = {string:alias}, description = {string:description}, keywords = {string:keywords}, content = {string:content}, type = {string:type}, permissions = {int:permissions}, updated_at = {int:updated_at}
				WHERE page_id = {int:page_id}',
				array(
					'page_id'     => $item,
					'title'	      => $context['lp_page']['title'],
					'alias'       => $context['lp_page']['alias'],
					'description' => $context['lp_page']['description'],
					'keywords'    => $context['lp_page']['keywords'],
					'content'     => $context['lp_page']['content'],
					'type'        => $context['lp_page']['type'],
					'permissions' => $context['lp_page']['permissions'],
					'updated_at'  => time()
				)
			);

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
					array('item_id', 'name')
				);
			}
		}

		if ($context['lp_page']['alias'] === '/') {
			$main_page_title = [];

			foreach ($context['languages'] as $lang)
				$main_page_title['lp_frontpage_title_' . $lang['filename']] = $context['lp_page']['title_' . $lang['filename']];

			updateSettings($main_page_title);
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
	 * Get the page data from lp_pages table
	 *
	 * Получаем данные страницы из таблицы в базе данных
	 *
	 * @param array $params
	 * @return void
	 */
	public static function getFromDB($params)
	{
		global $smcFunc, $modSettings;

		[$item, $useAlias] = $params;

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.title, p.alias, p.description, p.keywords, p.content, p.type, p.permissions, p.status, p.num_views, p.created_at, p.updated_at,
				mem.real_name AS author_name, pp.name, pp.value
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)
				LEFT JOIN {db_prefix}lp_params AS pp ON (pp.item_id = p.page_id AND pp.type = {string:type})
			WHERE ' . ($useAlias ? 'p.alias = {string' : 'p.page_id = {int') . ':item}',
			array(
				'type' => 'page',
				'item' => $item
			)
		);

		if ($smcFunc['db_num_rows']($request) == 0)
			fatal_lang_error('lp_page_not_found', false, null, 404);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['content']);

			$og_image = null;
			if (!empty($modSettings['lp_page_og_image'])) {
				$content = $row['content'];
				Subs::parseContent($content, $row['type']);
				$image_found = preg_match_all('/<img(.*)src(.*)=(.*)"(.*)"/U', $content, $values);

				if ($image_found && is_array($values)) {
					$all_images = array_pop($values);
					$image      = $modSettings['lp_page_og_image'] == 1 ? array_shift($all_images) : array_pop($all_images);
					$og_image   = $smcFunc['htmlspecialchars']($image);
				}
			}

			if (!isset($data))
				$data = array(
					'id'          => $row['page_id'],
					'author_id'   => $row['author_id'],
					'author'      => $row['author_name'],
					'title'       => $row['title'],
					'alias'       => $row['alias'],
					'description' => $row['description'],
					'keywords'    => $row['keywords'],
					'content'     => $row['content'],
					'type'        => $row['type'],
					'permissions' => $row['permissions'],
					'status'      => $row['status'],
					'num_views'   => $row['num_views'],
					'created_at'  => $row['created_at'],
					'updated_at'  => $row['updated_at'],
					'image'       => $og_image
				);

			if (!empty($row['name']))
				$data['options'][$row['name']] = $row['value'];
		}

		$smcFunc['db_free_result']($request);

		return $data;
	}

	/**
	 * Get the page fields
	 *
	 * Получаем поля страницы
	 *
	 * @param mixed $item
	 * @param bool $useAlias
	 * @return array
	 */
	public static function getData($item, $useAlias = true)
	{
		global $user_info;

		if (empty($item))
			return;

		$data = Helpers::useCache('page_' . ($item == '/' ? 'main' : $item), 'getFromDB', __CLASS__, 3600, array($item, $useAlias));

		if (!empty($data)) {
			$data['created']  = Helpers::getFriendlyTime($data['created_at']);
			$data['updated']  = Helpers::getFriendlyTime($data['updated_at']);
			$data['can_show'] = Helpers::canShowItem($data['permissions']);
			$data['can_edit'] = $user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $data['author_id'] == $user_info['id']);
		}

		return $data;
	}

	/**
	 * We check whether there is already such an alias in the database
	 *
	 * Проверяем, нет ли уже такого алиаса в базе
	 *
	 * @param array $data
	 * @return bool
	 */
	private static function isUnique($data)
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE alias = {string:alias}
				AND page_id != {int:item}',
			array(
				'alias' => $data['alias'],
				'item'  => $data['page_id']
			)
		);

		list ($count) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return (bool) $count;
	}

	/**
	 * Increasing the number of page views
	 *
	 * Увеличиваем количество просмотров страницы
	 *
	 * @return void
	 */
	private static function updateNumViews()
	{
		global $context, $smcFunc;

		if (empty($context['lp_page']['id']))
			return;

		if (empty($_SESSION['light_portal_last_page_viewed']) || $_SESSION['light_portal_last_page_viewed'] != $context['lp_page']['id'])	{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_pages
				SET num_views = num_views + 1
				WHERE page_id = {int:item}',
				array(
					'item' => $context['lp_page']['id']
				)
			);

			$_SESSION['light_portal_last_page_viewed'] = $context['lp_page']['id'];
		}
	}
}
