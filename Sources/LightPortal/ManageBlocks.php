<?php

namespace Bugo\LightPortal;

/**
 * ManageBlocks.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ManageBlocks
{
	/**
	 * Areas for block output must begin with a Latin letter and may consist of lowercase Latin letters, numbers, and some characters
	 *
	 * Области для вывода блока должны начинаться с латинской буквы и могут состоять из строчных латинских букв, цифр и некоторых знаков
	 *
	 * @var string
	 */
	private static $areas_pattern = '^[a-z][a-z0-9=|\-,\$]+$';

	/**
	 * Manage blocks
	 *
	 * Управление блоками
	 *
	 * @return void
	 */
	public static function main()
	{
		global $context, $txt;

		loadTemplate('LightPortal/ManageBlocks');

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_blocks_manage'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_blocks_manage_tab_description']
		);

		self::doActions();

		$context['lp_current_blocks'] = self::getAll();
		$context['lp_current_blocks'] = array_merge(array_flip(array_keys($txt['lp_block_placement_set'])), $context['lp_current_blocks']);

		$context['sub_template'] = 'manage_blocks';
	}

	/**
	 * Get a list of all blocks sorted by placement
	 *
	 * Получаем список всех блоков с разбивкой по размещению
	 *
	 * @return array
	 */
	public static function getAll()
	{
		global $smcFunc, $context;

		$request = $smcFunc['db_query']('', '
			SELECT b.block_id, b.icon, b.icon_type, b.type, b.placement, b.priority, b.permissions, b.status, b.areas, bt.lang, bt.title
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {string:type})
			ORDER BY b.placement DESC, b.priority',
			array(
				'type' => 'block'
			)
		);

		$current_blocks = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (!isset($current_blocks[$row['placement']][$row['block_id']]))
				$current_blocks[$row['placement']][$row['block_id']] = array(
					'icon'        => Helpers::getIcon($row['icon'], $row['icon_type']),
					'type'        => $row['type'],
					'priority'    => $row['priority'],
					'permissions' => $row['permissions'],
					'status'      => $row['status'],
					'areas'       => str_replace(',', PHP_EOL, $row['areas'])
				);

			$current_blocks[$row['placement']][$row['block_id']]['title'][$row['lang']] = $row['title'];
			Helpers::findMissingBlockTypes($row['type']);
		}

		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

		return $current_blocks;
	}

	/**
	 * Possible actions with blocks
	 *
	 * Возможные действия с блоками
	 *
	 * @return void
	 */
	public static function doActions()
	{
		if (!isset($_REQUEST['actions']))
			return;

		$item = filter_input(INPUT_POST, 'del_block', FILTER_VALIDATE_INT);

		if (!empty($item))
			self::remove([$item]);

		self::makeCopy();

		if (!empty($_POST['toggle_status']) && !empty($_POST['item'])) {
			$item   = (int) $_POST['item'];
			$status = str_replace('toggle_status ', '', $_POST['toggle_status']);

			self::toggleStatus([$item], $status == 'off' ? Block::STATUS_ACTIVE : Block::STATUS_INACTIVE);
		}

		self::updatePriority();

		Helpers::getFromCache('active_blocks', null);

		exit;
	}

	/**
	 * Block deleting
	 *
	 * Удаление блоков
	 *
	 * @param array $items
	 * @return void
	 */
	private static function remove(array $items)
	{
		global $smcFunc, $context;

		if (empty($items))
			return;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_blocks
			WHERE block_id IN ({array_int:items})',
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
				'type'  => 'block'
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_params
			WHERE item_id IN ({array_int:items})
				AND type = {string:type}',
			array(
				'items' => $items,
				'type'  => 'block'
			)
		);

		$context['lp_num_queries'] += 3;
	}

	/**
	 * Cloning a block
	 *
	 * Клонирование блока
	 *
	 * @return void
	 */
	private static function makeCopy()
	{
		global $context;

		$item = filter_input(INPUT_POST, 'clone_block', FILTER_VALIDATE_INT);

		if (empty($item))
			return;

		$_POST['clone']    = true;
		$result['success'] = false;

		$context['lp_block']         = self::getData($item);
		$context['lp_block']['id']   = self::setData();
		$context['lp_block']['icon'] = Helpers::getIcon();

		if (!empty($context['lp_block']['id'])) {
			loadTemplate('LightPortal/ManageBlocks');

			ob_start();
			show_block_entry($context['lp_block']['id'], $context['lp_block']);
			$new_block = ob_get_clean();

			$result = [
				'success' => true,
				'block'   => $new_block
			];
		}

		Helpers::getFromCache('active_blocks', null);

		exit(json_encode($result));
	}

	/**
	 * Changing the block status
	 *
	 * Смена статуса блока
	 *
	 * @param array $items
	 * @param int $status
	 * @return void
	 */
	public static function toggleStatus(array $items, int $status = 0)
	{
		global $smcFunc, $context;

		if (empty($items))
			return;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_blocks
			SET status = {int:status}
			WHERE block_id IN ({array_int:items})',
			array(
				'status' => $status,
				'items'  => $items
			)
		);

		$context['lp_num_queries']++;
	}

	/**
	 * Update priority
	 *
	 * Обновление приоритета
	 *
	 * @return void
	 */
	private static function updatePriority()
	{
		global $smcFunc, $context;

		if (!isset($_POST['update_priority']))
			return;

		$blocks = $_POST['update_priority'];

		$conditions = '';
		foreach ($blocks as $priority => $item)
			$conditions .= ' WHEN block_id = ' . $item . ' THEN ' . $priority;

		if (empty($conditions))
			return;

		if (!empty($blocks) && is_array($blocks)) {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_blocks
				SET priority = CASE ' . $conditions . '
					ELSE priority
					END
				WHERE block_id IN ({array_int:blocks})',
				array(
					'blocks' => $blocks
				)
			);

			$context['lp_num_queries']++;

			if (!empty($_POST['update_placement'])) {
				$placement = (string) $_POST['update_placement'];

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}lp_blocks
					SET placement = {string:placement}
					WHERE block_id IN ({array_int:blocks})',
					array(
						'placement' => $placement,
						'blocks'    => $blocks
					)
				);

				$context['lp_num_queries']++;
			}
		}
	}

	/**
	 * Adding a block
	 *
	 * Добавление блока
	 *
	 * @return void
	 */
	public static function add()
	{
		global $context, $txt, $scripturl;

		loadTemplate('LightPortal/ManageBlocks');

		$context['page_title']    = $txt['lp_portal'] . ' - ' . $txt['lp_blocks_add_title'];
		$context['canonical_url'] = $scripturl . '?action=admin;area=lp_blocks;sa=add';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_blocks_add_tab_description']
		);

		$context['current_block']['placement'] = $_REQUEST['placement'] ?? '';

		$context['sub_template'] = 'block_add';

		if (!isset($_POST['add_block']))
			return;

		$type = (string) $_POST['add_block'];
		$context['current_block']['type'] = $type;

		Subs::getForumLanguages();

		$context['sub_template'] = 'block_post';

		self::validateData();
		self::prepareFormFields();
		self::prepareEditor();
		self::preparePreview();
		self::setData();
	}

	/**
	 * Editing a block
	 *
	 * Редактирование блока
	 *
	 * @return void
	 */
	public static function edit()
	{
		global $context, $txt, $scripturl;

		$item = !empty($_REQUEST['block_id']) ? (int) $_REQUEST['block_id'] : null;
		$item = $item ?: (!empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : null);

		if (empty($item))
			fatal_lang_error('lp_block_not_found', false, null, 404);

		loadTemplate('LightPortal/ManageBlocks');

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_blocks_edit_title'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_blocks_edit_tab_description']
		);

		Subs::getForumLanguages();

		$context['sub_template']  = 'block_post';
		$context['current_block'] = self::getData($item);

		self::validateData();

		$context['canonical_url'] = $scripturl . '?action=admin;area=lp_blocks;sa=edit;id=' . $context['lp_block']['id'];

		self::prepareFormFields();
		self::prepareEditor();
		self::preparePreview();
		self::setData($context['lp_block']['id']);
	}

	/**
	 * Get the parameters of all blocks
	 *
	 * Получаем параметры всех блоков
	 *
	 * @return array
	 */
	private static function getOptions()
	{
		$options = [
			'bbc' => [
				'content' => true
			],
			'html' => [
				'content' => true
			],
			'php' => [
				'content' => true
			]
		];

		Subs::runAddons('blockOptions', array(&$options));

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
		global $context, $user_info;

		if (isset($_POST['save']) || isset($_POST['preview'])) {
			$args = array(
				'block_id'      => FILTER_VALIDATE_INT,
				'icon'          => FILTER_SANITIZE_STRING,
				'icon_type'     => FILTER_SANITIZE_STRING,
				'type'          => FILTER_SANITIZE_STRING,
				'content'       => FILTER_UNSAFE_RAW,
				'placement'     => FILTER_SANITIZE_STRING,
				'priority'      => FILTER_VALIDATE_INT,
				'permissions'   => FILTER_VALIDATE_INT,
				'areas'         => FILTER_SANITIZE_STRING,
				'title_class'   => FILTER_SANITIZE_STRING,
				'title_style'   => FILTER_SANITIZE_STRING,
				'content_class' => FILTER_SANITIZE_STRING,
				'content_style' => FILTER_SANITIZE_STRING
			);

			Subs::runAddons('validateBlockData', array(&$args));

			foreach ($context['languages'] as $lang)
				$args['title_' . $lang['filename']] = FILTER_SANITIZE_STRING;

			$parameters = $args['parameters'] ?? [];
			unset($args['parameters']);
			$post_data = filter_input_array(INPUT_POST, $args);
			$post_data['parameters'] = filter_input_array(INPUT_POST, $parameters);

			self::findErrors($post_data);
		}

		$options = self::getOptions();

		if (empty($options[$context['current_block']['type']]))
			$options[$context['current_block']['type']] = [];

		$block_options = $context['current_block']['options'] ?? $options[$context['current_block']['type']];

		$context['lp_block'] = array(
			'id'            => $post_data['block_id'] ?? $context['current_block']['id'] ?? 0,
			'title'         => $context['current_block']['title'] ?? [],
			'icon'          => trim($post_data['icon'] ?? $context['current_block']['icon'] ?? ''),
			'icon_type'     => $post_data['icon_type'] ?? $context['current_block']['icon_type'] ?? 'fas',
			'type'          => $post_data['type'] ?? $context['current_block']['type'] ?? '',
			'content'       => $post_data['content'] ?? $context['current_block']['content'] ?? '',
			'placement'     => $post_data['placement'] ?? $context['current_block']['placement'] ?? '',
			'priority'      => $post_data['priority'] ?? $context['current_block']['priority'] ?? 0,
			'permissions'   => $post_data['permissions'] ?? $context['current_block']['permissions'] ?? ($user_info['is_admin'] ? 0 : 2),
			'areas'         => $post_data['areas'] ?? $context['current_block']['areas'] ?? 'all',
			'title_class'   => $post_data['title_class'] ?? $context['current_block']['title_class'] ?? '',
			'title_style'   => $post_data['title_style'] ?? $context['current_block']['title_style'] ?? '',
			'content_class' => $post_data['content_class'] ?? $context['current_block']['content_class'] ?? '',
			'content_style' => $post_data['content_style'] ?? $context['current_block']['content_style'] ?? '',
			'options'       => $options[$context['current_block']['type']]
		);

		if (!empty($context['lp_block']['options']['parameters'])) {
			foreach ($context['lp_block']['options']['parameters'] as $option => $value) {
				if (!empty($post_data['parameters'])) {
					if (!empty($parameters[$option]) && $parameters[$option] == FILTER_VALIDATE_BOOLEAN && $post_data['parameters'][$option] === null)
						$post_data['parameters'][$option] = 0;

					if (is_array($parameters[$option]) && $parameters[$option]['filter'] == FILTER_SANITIZE_STRING && $post_data['parameters'][$option] === null)
						$post_data['parameters'][$option] = [];
				}

				$context['lp_block']['options']['parameters'][$option] = $post_data['parameters'][$option] ?? $block_options['parameters'][$option] ?? $value;
			}
		}

		foreach ($context['languages'] as $lang)
			$context['lp_block']['title'][$lang['filename']] = $post_data['title_' . $lang['filename']] ?? $context['lp_block']['title'][$lang['filename']] ?? '';

		Helpers::cleanBbcode($context['lp_block']['title']);
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

		if (empty($data['areas']))
			$post_errors[] = 'no_areas';

		$areas_format = array(
			'options' => array("regexp" => '/' . static::$areas_pattern . '/')
		);
		if (!empty($data['areas']) && empty(filter_var($data['areas'], FILTER_VALIDATE_REGEXP, $areas_format)))
			$post_errors[] = 'no_valid_areas';

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

		foreach ($context['languages'] as $lang) {
			$context['posting_fields']['title_' . $lang['filename']]['label']['text'] = $txt['lp_title'] . (count($context['languages']) > 1 ? ' [' . $lang['filename'] . ']' : '');
			$context['posting_fields']['title_' . $lang['filename']]['input'] = array(
				'type' => 'text',
				'attributes' => array(
					'maxlength' => 255,
					'value'     => $context['lp_block']['title'][$lang['filename']] ?? '',
					'style'     => 'width: 100%'
				),
				'tab' => 'content'
			);
		}

		$context['posting_fields']['icon']['label']['text'] = $txt['current_icon'];
		$context['posting_fields']['icon']['label']['after'] = '<br><span class="smalltext"><a href="https://fontawesome.com/cheatsheet/free" target="_blank" rel="noopener">' . $txt['lp_block_icon_cheatsheet'] . '</a></span>';
		$context['posting_fields']['icon']['input'] = array(
			'type' => 'text',
			'after' => '<span id="block_icon">' . Helpers::getIcon() . '</span>',
			'attributes' => array(
				'id'        => 'icon',
				'maxlength' => 30,
				'value'     => $context['lp_block']['icon']
			),
			'tab' => 'appearance'
		);

		$context['posting_fields']['icon_type']['label']['text'] = $txt['lp_block_icon_type'];
		$context['posting_fields']['icon_type']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id' => 'icon_type'
			),
			'options' => array(),
			'tab' => 'appearance'
		);

		foreach ($txt['lp_block_icon_type_set'] as $type => $title) {
			if (RC2_CLEAN) {
				$context['posting_fields']['icon_type']['input']['options'][$title]['attributes'] = array(
					'value'   => $type,
					'checked' => $type == $context['lp_block']['icon_type']
				);
			} else {
				$context['posting_fields']['icon_type']['input']['options'][$title] = array(
					'value'   => $type,
					'checked' => $type == $context['lp_block']['icon_type']
				);
			}
		}

		$context['posting_fields']['placement']['label']['text'] = $txt['lp_block_placement'];
		$context['posting_fields']['placement']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'placement'
			),
			'options' => array(),
			'tab' => 'access_placement'
		);

		foreach ($txt['lp_block_placement_set'] as $level => $title) {
			if (RC2_CLEAN) {
				$context['posting_fields']['placement']['input']['options'][$title]['attributes'] = array(
					'value'    => $level,
					'selected' => $level == $context['lp_block']['placement']
				);
			} else {
				$context['posting_fields']['placement']['input']['options'][$title] = array(
					'value'    => $level,
					'selected' => $level == $context['lp_block']['placement']
				);
			}
		}

		$context['posting_fields']['permissions']['label']['text'] = $txt['edit_permissions'];
		$context['posting_fields']['permissions']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'permissions'
			),
			'options' => array(),
			'tab' => 'access_placement'
		);

		foreach ($txt['lp_permissions'] as $level => $title) {
			if (RC2_CLEAN) {
				$context['posting_fields']['permissions']['input']['options'][$title]['attributes'] = array(
					'value'    => $level,
					'selected' => $level == $context['lp_block']['permissions']
				);
			} else {
				$context['posting_fields']['permissions']['input']['options'][$title] = array(
					'value'    => $level,
					'selected' => $level == $context['lp_block']['permissions']
				);
			}
		}

		$context['posting_fields']['areas']['label']['text'] = $txt['lp_block_areas'];
		$context['posting_fields']['areas']['input'] = array(
			'type' => 'text',
			'after' => self::getAreasInfo(),
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['areas'],
				'required'  => true,
				'pattern'   => static::$areas_pattern,
				'style'     => 'width: 100%'
			),
			'tab' => 'access_placement'
		);

		$context['posting_fields']['title_class']['label']['text'] = $txt['lp_block_title_class'];
		$context['posting_fields']['title_class']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'title_class'
			),
			'options' => array(),
			'tab' => 'appearance'
		);

		foreach ($context['lp_all_title_classes'] as $key => $data) {
			if (RC2_CLEAN) {
				$context['posting_fields']['title_class']['input']['options'][$key]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['title_class']
				);
			} else {
				$context['posting_fields']['title_class']['input']['options'][$key] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['title_class']
				);
			}
		}

		$context['posting_fields']['title_style']['label']['text'] = $txt['lp_block_title_style'];
		$context['posting_fields']['title_style']['input'] = array(
			'type' => 'textarea',
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['title_style'],
				'style'     => 'width: 100%'
			),
			'tab' => 'appearance'
		);

		if (empty($context['lp_block']['options']['no_content_class'])) {
			$context['posting_fields']['content_class']['label']['text'] = $txt['lp_block_content_class'];
			$context['posting_fields']['content_class']['input'] = array(
				'type' => 'select',
				'attributes' => array(
					'id' => 'content_class'
				),
				'options' => array(),
				'tab' => 'appearance'
			);

			foreach ($context['lp_all_content_classes'] as $key => $data) {
				$value = $key;
				$key   = $key == '_' ? $txt['no'] : $key;

				if (RC2_CLEAN) {
					$context['posting_fields']['content_class']['input']['options'][$key]['attributes'] = array(
						'value'    => $value,
						'selected' => $value == $context['lp_block']['content_class']
					);
				} else {
					$context['posting_fields']['content_class']['input']['options'][$key] = array(
						'value'    => $value,
						'selected' => $value == $context['lp_block']['content_class']
					);
				}
			}

			$context['posting_fields']['content_style']['label']['text'] = $txt['lp_block_content_style'];
			$context['posting_fields']['content_style']['input'] = array(
				'type' => 'textarea',
				'attributes' => array(
					'maxlength' => 255,
					'value'     => $context['lp_block']['content_style'],
					'style'     => 'width: 100%'
				),
				'tab' => 'appearance'
			);
		}

		if (!empty($context['lp_block']['options']['content']) && $context['lp_block']['type'] !== 'bbc') {
			$context['posting_fields']['content']['label']['text'] = $txt['lp_block_content'];
			$context['posting_fields']['content']['input'] = array(
				'type' => 'textarea',
				'attributes' => array(
					'id'        => 'content',
					'maxlength' => Helpers::getMaxMessageLength(),
					'value'     => $context['lp_block']['content']
				),
				'tab' => 'content'
			);
		}

		Subs::runAddons('prepareBlockFields');

		foreach ($context['posting_fields'] as $item => $data) {
			if ($item !== 'icon' && !empty($data['input']['after']))
				$context['posting_fields'][$item]['input']['after'] = '<div class="descbox alternative smalltext">' . $data['input']['after'] . '</div>';

			if (empty($data['input']['tab']))
				$context['posting_fields'][$item]['input']['tab'] = 'tuning';
		}

		$context['lp_block_tab_tuning'] = self::hasParameters($context['posting_fields']);

		loadTemplate('LightPortal/ManageSettings');
	}

	/**
	 * Get a table with possible areas
	 *
	 * Получаем табличку с возможными областями
	 *
	 * @return string
	 */
	private static function getAreasInfo()
	{
		global $context, $txt;

		$areas = array(
			'all',
			'custom_action',
			'pages',
			'page=alias',
			'boards',
			'board=id',
			'board=id1-id3',
			'board=id3|id7',
			'topics',
			'topic=id',
			'topic=id1-id3',
			'topic=id3|id7'
		);

		$context['lp_possible_areas'] = array_combine($areas, $txt['lp_block_areas_values']);

		ob_start();

		template_show_areas_info();

		return ob_get_clean();
	}

	/**
	 * Check whether there are any parameters on the "Tuning" tab
	 *
	 * Проверяем, есть ли какие-нибудь параметры на вкладке «Тюнинг»
	 *
	 * @param array $data
	 * @param string $check_key
	 * @param string $check_value
	 * @return bool
	 */
	private static function hasParameters(array $data = [], string $check_key = 'tab', string $check_value = 'tuning')
	{
		if (empty($data))
			return false;

		foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($data), \RecursiveIteratorIterator::LEAVES_ONLY) as $key => $value) {
			if ($check_key === $key) {
				$result[] = $value;
			}
		}

		return in_array($check_value, $result);
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

		if (!empty($context['lp_block']['options']['content']) && $context['lp_block']['type'] === 'bbc')
			Subs::createBbcEditor($context['lp_block']['content']);

		Subs::runAddons('prepareEditor', array($context['lp_block']));
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

		if (!isset($_POST['preview']))
			return;

		checkSubmitOnce('free');

		$context['preview_title']   = $context['lp_block']['title'][$context['user']['language']];
		$context['preview_content'] = $smcFunc['htmlspecialchars']($context['lp_block']['content'], ENT_QUOTES);

		Helpers::cleanBbcode($context['preview_title']);
		censorText($context['preview_title']);
		censorText($context['preview_content']);

		if (!empty($context['preview_content']))
			Subs::parseContent($context['preview_content'], $context['lp_block']['type']);
		else
			Subs::prepareContent($context['preview_content'], $context['lp_block']['type'], $context['lp_block']['id']);

		$context['page_title']    = $txt['preview'] . ($context['preview_title'] ? ' - ' . $context['preview_title'] : '');
		$context['preview_title'] = Helpers::getPreviewTitle(Helpers::getIcon());
	}

	/**
	 * Get correct priority for a new block
	 *
	 * Получаем правильный приоритет для нового блока
	 *
	 * @return int
	 */
	private static function getPriority()
	{
		global $context, $smcFunc;

		if (empty($context['lp_block']['placement']))
			return 0;

		$request = $smcFunc['db_query']('', '
			SELECT MAX(priority) + 1
			FROM {db_prefix}lp_blocks
			WHERE placement = {string:placement}',
			array(
				'placement' => $context['lp_block']['placement']
			)
		);

		list ($priority) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

		return $priority;
	}

	/**
	 * Creating or updating a block
	 *
	 * Создаем или обновляем блок
	 *
	 * @param int $item
	 * @return int|void
	 */
	public static function setData(int $item = 0)
	{
		global $context, $smcFunc;

		if (!empty($context['post_errors']) || (!isset($_POST['save']) && !isset($_POST['clone'])))
			return;

		checkSubmitOnce('check');

		if (empty($item)) {
			$max_length = Helpers::getMaxMessageLength();
			$priority   = self::getPriority();

			$item = $smcFunc['db_insert']('',
				'{db_prefix}lp_blocks',
				array(
					'icon'          => 'string-60',
					'icon_type'     => 'string-10',
					'type'          => 'string',
					'content'       => 'string-' . $max_length,
					'placement'     => 'string-10',
					'priority'      => 'int',
					'permissions'   => 'int',
					'status'        => 'int',
					'areas'         => 'string',
					'title_class'   => 'string',
					'title_style'   => 'string',
					'content_class' => 'string',
					'content_style' => 'string'
				),
				array(
					$context['lp_block']['icon'],
					$context['lp_block']['icon_type'],
					$context['lp_block']['type'],
					$context['lp_block']['content'],
					$context['lp_block']['placement'],
					$context['lp_block']['priority'] ?? $priority ?? 0,
					$context['lp_block']['permissions'],
					$context['lp_block']['status'] ?? Block::STATUS_ACTIVE,
					$context['lp_block']['areas'],
					$context['lp_block']['title_class'],
					$context['lp_block']['title_style'],
					$context['lp_block']['content_class'],
					$context['lp_block']['content_style']
				),
				array('block_id'),
				1
			);

			$context['lp_num_queries']++;

			if (!empty($context['lp_block']['title'])) {
				$titles = [];
				foreach ($context['lp_block']['title'] as $lang => $title) {
					$titles[] = array(
						'item_id' => $item,
						'type'    => 'block',
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

				$context['lp_num_queries']++;
			}

			if (!empty($context['lp_block']['options']['parameters'])) {
				$parameters = [];
				foreach ($context['lp_block']['options']['parameters'] as $param_name => $value) {
					$value = is_array($value) ? implode(',', $value) : $value;
					$parameters[] = array(
						'item_id' => $item,
						'type'    => 'block',
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

				$context['lp_num_queries']++;
			}
		} else {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_blocks
				SET icon = {string:icon}, icon_type = {string:icon_type}, type = {string:type}, content = {string:content}, placement = {string:placement}, permissions = {int:permissions}, areas = {string:areas}, title_class = {string:title_class}, title_style = {string:title_style}, content_class = {string:content_class}, content_style = {string:content_style}
				WHERE block_id = {int:block_id}',
				array(
					'block_id'      => $item,
					'icon'          => $context['lp_block']['icon'],
					'icon_type'     => $context['lp_block']['icon_type'],
					'type'          => $context['lp_block']['type'],
					'content'       => $context['lp_block']['content'],
					'placement'     => $context['lp_block']['placement'],
					'permissions'   => $context['lp_block']['permissions'],
					'areas'         => $context['lp_block']['areas'],
					'title_class'   => $context['lp_block']['title_class'],
					'title_style'   => $context['lp_block']['title_style'],
					'content_class' => $context['lp_block']['content_class'],
					'content_style' => $context['lp_block']['content_style']
				)
			);

			$context['lp_num_queries']++;

			if (!empty($context['lp_block']['title'])) {
				$titles = [];
				foreach ($context['lp_block']['title'] as $lang => $title) {
					$titles[] = array(
						'item_id' => $item,
						'type'    => 'block',
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

				$context['lp_num_queries']++;
			}

			if (!empty($context['lp_block']['options']['parameters'])) {
				$parameters = [];
				foreach ($context['lp_block']['options']['parameters'] as $param_name => $value) {
					$value = is_array($value) ? implode(',', $value) : $value;
					$parameters[] = array(
						'item_id' => $item,
						'type'    => 'block',
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

				$context['lp_num_queries']++;
			}

			Helpers::getFromCache($context['lp_block']['type'] . '_addon_b' . $item, null);
			Helpers::getFromCache($context['lp_block']['type'] . '_addon_u' . $context['user']['id'], null);
			Helpers::getFromCache($context['lp_block']['type'] . '_addon_b' . $item . '_u' . $context['user']['id'], null);
		}

		if (!empty($_POST['clone']))
			return $item;

		Helpers::getFromCache('active_blocks', null);

		redirectexit('action=admin;area=lp_blocks;sa=main');
	}

	/**
	 * Get the block fields
	 *
	 * Получаем поля блока
	 *
	 * @param int $item
	 * @return array
	 */
	public static function getData(int $item)
	{
		global $smcFunc, $context;

		if (empty($item))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT
				b.block_id, b.icon, b.icon_type, b.type, b.content, b.placement, b.priority, b.permissions, b.status, b.areas, b.title_class, b.title_style, b.content_class, b.content_style, bt.lang, bt.title, bp.name, bp.value
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {string:type})
				LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {string:type})
			WHERE b.block_id = {int:item}',
			array(
				'type' => 'block',
				'item' => $item
			)
		);

		if ($smcFunc['db_num_rows']($request) == 0)
			fatal_lang_error('lp_block_not_found', false, null, 404);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['content']);

			if (!isset($data))
				$data = array(
					'id'            => $row['block_id'],
					'icon'          => $row['icon'],
					'icon_type'     => $row['icon_type'],
					'type'          => $row['type'],
					'content'       => $row['content'],
					'placement'     => $row['placement'],
					'priority'      => $row['priority'],
					'permissions'   => $row['permissions'],
					'status'        => $row['status'],
					'areas'         => $row['areas'],
					'title_class'   => $row['title_class'],
					'title_style'   => $row['title_style'],
					'content_class' => $row['content_class'],
					'content_style' => $row['content_style']
				);

			$data['title'][$row['lang']] = $row['title'];

			if (!empty($row['name']))
				$data['options']['parameters'][$row['name']] = $row['value'];
		}

		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

		return $data ?? [];
	}

	/**
	 * Block export
	 *
	 * Экспорт блоков
	 *
	 * @return void
	 */
	public static function export()
	{
		global $context, $txt, $scripturl;

		loadTemplate('LightPortal/ManageExport');

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_blocks_export'];
		$context['page_area_title'] = $txt['lp_blocks_export'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_blocks;sa=export';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_blocks_export_tab_description']
		);

		Subs::runExport(self::getXmlFile());

		$context['lp_current_blocks'] = self::getAll();
		$context['lp_current_blocks'] = array_merge(array_flip(array_keys($txt['lp_block_placement_set'])), $context['lp_current_blocks']);

		$context['sub_template'] = 'manage_export_blocks';
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
		global $smcFunc, $context;

		if (empty($_POST['items']))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT
				b.block_id, b.icon, b.icon_type, b.type, b.content, b.placement, b.priority, b.permissions, b.status, b.areas, b.title_class, b.title_style, b.content_class, b.content_style,
				pt.lang, pt.title, pp.name, pp.value
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS pt ON (b.block_id = pt.item_id AND pt.type = {string:type})
				LEFT JOIN {db_prefix}lp_params AS pp ON (b.block_id = pp.item_id AND pp.type = {string:type})
			WHERE b.block_id IN ({array_int:blocks})',
			array(
				'type'  => 'block',
				'blocks' => $_POST['items']
			)
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (!isset($items[$row['block_id']]))
				$items[$row['block_id']] = array(
					'block_id'      => $row['block_id'],
					'icon'          => $row['icon'],
					'icon_type'     => $row['icon_type'],
					'type'          => $row['type'],
					'content'       => $row['content'],
					'placement'     => $row['placement'],
					'priority'      => $row['priority'],
					'permissions'   => $row['permissions'],
					'status'        => $row['status'],
					'areas'         => $row['areas'],
					'title_class'   => $row['title_class'],
					'title_style'   => $row['title_style'],
					'content_class' => $row['content_class'],
					'content_style' => $row['content_style']
				);

			if (!empty($row['lang']))
				$items[$row['block_id']]['titles'][$row['lang']] = $row['title'];

			if (!empty($row['name']))
				$items[$row['block_id']]['params'][$row['name']] = $row['value'];
		}

		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

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

		$xmlElements = $root->appendChild($xml->createElement('blocks'));
		foreach ($items as $item) {
			$xmlElement = $xmlElements->appendChild($xml->createElement('item'));
			foreach ($item as $key => $val) {
				$xmlName = $xmlElement->appendChild(in_array($key, ['block_id', 'priority', 'permissions', 'status']) ? $xml->createAttribute($key) : $xml->createElement($key));

				if (in_array($key, ['titles', 'params'])) {
					foreach ($item[$key] as $k => $v) {
						$xmlTitle = $xmlName->appendChild($xml->createElement($k));
						$xmlTitle->appendChild($xml->createTextNode($v));
					}
				} elseif ($key == 'content') {
					$xmlName->appendChild($xml->createCDATASection($val));
				} else {
					$xmlName->appendChild($xml->createTextNode($val));
				}
			}
		}

		$file = sys_get_temp_dir() . '/lp_blocks_backup.xml';
		$xml->save($file);

		return $file;
	}

	/**
	 * Block import
	 *
	 * Импорт блоков
	 *
	 * @return void
	 */
	public static function import()
	{
		global $context, $txt, $scripturl;

		loadTemplate('LightPortal/ManageImport');

		$context['page_title']      = $txt['lp_portal'] . ' - ' . $txt['lp_blocks_import'];
		$context['page_area_title'] = $txt['lp_blocks_import'];
		$context['canonical_url']   = $scripturl . '?action=admin;area=lp_blocks;sa=import';

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_blocks_import_tab_description']
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
		global $smcFunc, $context;

		if (empty($_FILES['import_file']))
			return;

		$file = $_FILES['import_file'];

		if ($file['type'] !== 'text/xml')
			return;

		$xml = simplexml_load_file($file['tmp_name']);

		if ($xml === false)
			return;

		if (!isset($xml->blocks->item[0]['block_id']))
			fatal_lang_error('lp_wrong_import_file', false);

		$items = $titles = $params = [];

		foreach ($xml as $element) {
			foreach ($element->item as $item) {
				$items[] = [
					'block_id'      => $block_id = intval($item['block_id']),
					'icon'          => $item->icon,
					'icon_type'     => $item->icon_type,
					'type'          => $item->type,
					'content'       => $item->content,
					'placement'     => $item->placement,
					'priority'      => intval($item['priority']),
					'permissions'   => intval($item['permissions']),
					'status'        => intval($item['status']),
					'areas'         => $item->areas,
					'title_class'   => $item->title_class,
					'title_style'   => $item->title_style,
					'content_class' => $item->content_class,
					'content_style' => $item->content_style
				];

				if (!empty($item->titles)) {
					foreach ($item->titles as $title) {
						foreach ($title as $k => $v) {
							$titles[] = [
								'item_id' => $block_id,
								'type'    => 'block',
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
								'item_id' => $block_id,
								'type'    => 'block',
								'name'    => $k,
								'value'   => $v
							];
						}
					}
				}
			}
		}

		if (!empty($items)) {
			$sql = "REPLACE INTO {db_prefix}lp_blocks (`block_id`, `icon`, `icon_type`, `type`, `content`, `placement`, `priority`, `permissions`, `status`, `areas`, `title_class`, `title_style`, `content_class`, `content_style`)
				VALUES ";

			$sql .= Subs::getValues($items);

			$result = $smcFunc['db_query']('', $sql);
			$context['lp_num_queries']++;
		}

		if (!empty($titles) && !empty($result)) {
			$sql = "REPLACE INTO {db_prefix}lp_titles (`item_id`, `type`, `lang`, `title`)
				VALUES ";

			$sql .= Subs::getValues($titles);

			$result = $smcFunc['db_query']('', $sql);
			$context['lp_num_queries']++;
		}

		if (!empty($params) && !empty($result)) {
			$sql = "REPLACE INTO {db_prefix}lp_params (`item_id`, `type`, `name`, `value`)
				VALUES ";

			$sql .= Subs::getValues($params);

			$result = $smcFunc['db_query']('', $sql);
			$context['lp_num_queries']++;
		}

		if (empty($result))
			fatal_lang_error('lp_import_failed', false);

		clean_cache();
	}
}
