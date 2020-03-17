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

		loadJavaScriptFile('light_portal/manage_pages.js');

		self::postActions();

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
					),
					'sort' => array(
						'default' => 'pt.title DESC',
						'reverse' => 'pt.title'
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

							if (strpos($settings['name'], 'Lunarfall') !== false) {
								$actions .= '<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $entry['id'] . '"><span class="fas fa-tools" title="' . $txt['edit'] . '"></span></a>' . '
							<span class="fas fa-trash del_page" data-id="' . $entry['id'] . '" title="' . $txt['remove'] . '"></span>';
							} else {
								$actions .= '<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $entry['id'] . '"><span class="main_icons settings" title="' . $txt['edit'] . '"></span></a>' . '
							<span class="main_icons unread_button del_page" data-id="' . $entry['id'] . '" data-alias="' . $entry['alias'] . '" title="' . $txt['remove'] . '"></span>';
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

		$listOptions['title'] = '
			<span class="floatright">
				<a href="' . $scripturl . '?action=admin;area=lp_pages;sa=add;' . $context['session_var'] . '=' . $context['session_id'] . '">
					<i class="fas fa-plus" title="' . $txt['lp_pages_add'] . '"></i>
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

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.alias, p.type, p.permissions, p.status, p.num_views,
				GREATEST(p.created_at, p.updated_at) AS date, pt.lang, pt.title, mem.real_name AS author_name
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS pt ON (pt.item_id = p.page_id AND pt.type = {string:type})
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)' . (allowedTo('admin_forum') ? '' : '
			WHERE p.author_id = {int:user_id}') . '
			ORDER BY ' . $sort . ', p.page_id
			LIMIT ' . $start . ', ' . $items_per_page,
			array(
				'type'    => 'page',
				'user_id' => $user_info['id']
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (!isset($items[$row['page_id']]))
				$items[$row['page_id']] = array(
					'id'          => $row['page_id'],
					'alias'       => $row['alias'],
					'type'        => $row['type'],
					'status'      => $row['status'],
					'num_views'   => $row['num_views'],
					'author_id'   => $row['author_id'],
					'author_name' => $row['author_name'],
					'created_at'  => Helpers::getFriendlyTime($row['date']),
					'is_front'    => Helpers::isFrontpage($row['page_id'])
				);

			if (!empty($row['lang']))
				$items[$row['page_id']]['title'][$row['lang']] = $row['title'];
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
	private static function postActions()
	{
		if (!isset($_REQUEST['actions']))
			return;

		self::remove();

		if (!empty($_POST['toggle_status']) && !empty($_POST['item'])) {
			$item   = (int) $_POST['item'];
			$status = str_replace('toggle_status ', '', $_POST['toggle_status']);
			self::toggleStatus($item, $status == 'off' ? Page::STATUS_ACTIVE : Page::STATUS_INACTIVE);
		}

		clean_cache();
		exit;
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

		$item  = filter_input(INPUT_POST, 'del_page_id', FILTER_VALIDATE_INT);

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
			DELETE FROM {db_prefix}lp_titles
			WHERE item_id = {int:id}
				AND type = {string:type}',
			array(
				'id'   => $item,
				'type' => 'page'
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

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_comments
			WHERE page_id = {int:id}',
			array(
				'id' => $item
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_tags
			WHERE page_id = {int:id}',
			array(
				'id' => $item
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
	public static function toggleStatus(int $item, int $status = 0)
	{
		global $smcFunc;

		if (empty($item))
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
		global $context, $modSettings;

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
			'permissions' => $post_data['permissions'] ?? $context['lp_current_page']['permissions'] ?? 0,
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
		global $context, $txt, $modSettings;

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
					'required'  => in_array($lang['filename'], array('english', Helpers::getUserLanguage())),
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

		if (!empty($modSettings['lp_show_comment_block'])) {
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
}
