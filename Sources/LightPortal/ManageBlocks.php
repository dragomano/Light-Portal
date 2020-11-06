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
 * @version 1.2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ManageBlocks
{
	/**
	 * Areas for block output must begin with a Latin letter and may consist of lowercase Latin letters, numbers, and some characters
	 *
	 * Области для вывода блока должны начинаться с латинской буквы и могут состоять из строчных латинских букв, цифр и некоторых символов
	 *
	 * @var string
	 */
	private static $areas_pattern = '^[a-z][a-z0-9=|\-,]+$';

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
		$request = Helpers::db()->table('lp_blocks AS b')
			->select('b.block_id', 'b.icon', 'b.icon_type', 'b.type', 'b.placement', 'b.priority', 'b.permissions', 'b.status', 'b.areas', 'bt.lang', 'bt.title')
			->leftJoin('lp_titles AS bt', 'b.block_id = bt.item_id AND bt.type = "block"')
			->orderBy('b.placement DESC, b.priority')
			->get();

		$current_blocks = [];

		foreach ($request as $row) {
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
		if (Helpers::request()->has('actions') === false)
			return;

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		if (!empty($data['del_item']))
			self::remove([(int) $data['del_item']]);

		if (!empty($data['clone_block']))
			self::makeCopy((int) $data['clone_block']);

		if (!empty($data['toggle_status']) && !empty($data['item'])) {
			$item   = (int) $data['item'];
			$status = $data['toggle_status'];

			self::toggleStatus([$item], $status == 'off' ? Block::STATUS_ACTIVE : Block::STATUS_INACTIVE);
		}

		self::updatePriority();

		Helpers::cache()->flush();

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
		if (empty($items))
			return;

		Helpers::db()->table('lp_blocks')
			->whereIn('block_id', $items)
			->delete();

		Helpers::db()->table('lp_titles')
			->whereIn('item_id', $items)
			->andWhere('type', 'block')
			->delete();

		Helpers::db()->table('lp_params')
			->whereIn('item_id', $items)
			->andWhere('type', 'block')
			->delete();
	}

	/**
	 * Cloning a block
	 *
	 * Клонирование блока
	 *
	 * @param int $item
	 * @return void
	 */
	private static function makeCopy(int $item)
	{
		global $context;

		if (empty($item))
			return;

		Helpers::post()->put('clone', true);
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

		Helpers::cache()->forget('active_blocks');

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
		if (empty($items))
			return;

		Helpers::db()->table('lp_blocks')
			->whereIn('block_id', $items)
			->update(['status' => $status]);
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
		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		if (!isset($data['update_priority']))
			return;

		$blocks = $data['update_priority'];

		$conditions = '';
		foreach ($blocks as $priority => $item)
			$conditions .= ' WHEN block_id = ' . $item . ' THEN ' . $priority;

		if (empty($conditions))
			return;

		if (!empty($blocks) && is_array($blocks)) {
			Helpers::db()->table('lp_blocks')
				->whereIn('block_id', $blocks)
				->update(['priority' => ['CASE ' . $conditions . ' ELSE priority END']]);

			if (!empty($data['update_placement'])) {
				Helpers::db()->table('lp_blocks')
					->whereIn('block_id', $blocks)
					->update(['placement' => $data['update_placement']]);
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

		$context['current_block']['placement'] = Helpers::request('placement', '');

		$context['sub_template'] = 'block_add';

		if (Helpers::post()->has('add_block') === false)
			return;

		$type = Helpers::post('add_block', '');
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

		$item = Helpers::request('block_id') ?: Helpers::request('id');

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

		if (Helpers::post()->has('save') || Helpers::post()->has('preview')) {
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

			foreach ($context['languages'] as $lang)
				$args['title_' . $lang['filename']] = FILTER_SANITIZE_STRING;

			$parameters = [];

			Subs::runAddons('validateBlockData', array(&$parameters, $context['current_block']['type']));

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
			'status'        => $context['current_block']['status'] ?? Block::STATUS_ACTIVE,
			'areas'         => $post_data['areas'] ?? $context['current_block']['areas'] ?? 'all',
			'title_class'   => $post_data['title_class'] ?? $context['current_block']['title_class'] ?? '',
			'title_style'   => $post_data['title_style'] ?? $context['current_block']['title_style'] ?? '',
			'content_class' => $post_data['content_class'] ?? $context['current_block']['content_class'] ?? '',
			'content_style' => $post_data['content_style'] ?? $context['current_block']['content_style'] ?? '',
			'options'       => $options[$context['current_block']['type']]
		);

		$context['lp_block']['priority'] = empty($context['lp_block']['id']) ? self::getPriority() : $context['lp_block']['priority'];

		$context['lp_block']['content'] = Helpers::getShortenText($context['lp_block']['content']);

		if (!empty($context['lp_block']['options']['parameters'])) {
			foreach ($context['lp_block']['options']['parameters'] as $option => $value) {
				if (!empty($post_data['parameters'])) {
					if (!empty($parameters[$option]) && $parameters[$option] == FILTER_VALIDATE_BOOLEAN && $post_data['parameters'][$option] === null)
						$post_data['parameters'][$option] = 0;

					if (!empty($parameters[$option]) && is_array($parameters[$option]) && $parameters[$option]['flags'] == FILTER_REQUIRE_ARRAY && $post_data['parameters'][$option] === null)
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
		if (!empty($data['areas']) && empty(Helpers::validate($data['areas'], $areas_format)))
			$post_errors[] = 'no_valid_areas';

		if (!empty($post_errors)) {
			Helpers::post()->put('preview', true);
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
			$context['posting_fields']['content']['label']['text'] = $txt['lp_content'];
			$context['posting_fields']['content']['input'] = array(
				'type' => 'textarea',
				'attributes' => array(
					'id'        => 'content',
					'maxlength' => MAX_MSG_LENGTH,
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

		$exampe_areas = array(
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

		$context['lp_possible_areas'] = array_combine($exampe_areas, $txt['lp_block_areas_values']);

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

		if (Helpers::post()->has('preview') === false)
			return;

		checkSubmitOnce('free');

		$context['preview_title']   = $context['lp_block']['title'][$context['user']['language']];
		$context['preview_content'] = $smcFunc['htmlspecialchars']($context['lp_block']['content'], ENT_QUOTES);

		Helpers::cleanBbcode($context['preview_title']);
		censorText($context['preview_title']);
		censorText($context['preview_content']);

		!empty($context['preview_content'])
			? Helpers::parseContent($context['preview_content'], $context['lp_block']['type'])
			: Helpers::prepareContent($context['preview_content'], $context['lp_block']['type'], $context['lp_block']['id']);

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
		global $context;

		if (empty($context['lp_block']['placement']))
			return 0;

		$max = Helpers::db()->table('lp_blocks')
			->where('placement', $context['lp_block']['placement'])
			->max('priority');

		return (int) $max + 1;
	}

	/**
	 * Creating or updating a block
	 *
	 * Создаем или обновляем блок
	 *
	 * @param int $item
	 * @return int|void
	 */
	private static function setData(int $item = 0)
	{
		global $context;

		if (!empty($context['post_errors']) || (Helpers::post()->has('save') === false && Helpers::post()->has('clone') === false))
			return;

		checkSubmitOnce('check');

		if (empty($item)) {
			$item = Helpers::db()->table('lp_blocks')
				->insert(
					array(
						'icon'          => $context['lp_block']['icon'],
						'icon_type'     => $context['lp_block']['icon_type'],
						'type'          => $context['lp_block']['type'],
						'content'       => $context['lp_block']['content'],
						'placement'     => $context['lp_block']['placement'],
						'priority'      => $context['lp_block']['priority'],
						'permissions'   => $context['lp_block']['permissions'],
						'status'        => $context['lp_block']['status'],
						'areas'         => $context['lp_block']['areas'],
						'title_class'   => $context['lp_block']['title_class'],
						'title_style'   => $context['lp_block']['title_style'],
						'content_class' => $context['lp_block']['content_class'],
						'content_style' => $context['lp_block']['content_style']
					),
					array('block_id')
				);

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

				Helpers::db()->table('lp_titles')
					->insert($titles, array('item_id', 'type', 'lang'));
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

				Helpers::db()->table('lp_params')
					->insert($parameters, array('item_id', 'type', 'name'));
			}
		} else {
			Helpers::db()->table('lp_blocks')
				->where('block_id', $item)
				->update([
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
				]);

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

				Helpers::db()->table('lp_titles')
					->insert($titles, ['item_id', 'type', 'lang'], 'replace');
			}

			if (!empty($context['lp_block']['options']['parameters'])) {
				$params = [];
				foreach ($context['lp_block']['options']['parameters'] as $param_name => $value) {
					$value = is_array($value) ? implode(',', $value) : $value;

					$params[] = array(
						'item_id' => $item,
						'type'    => 'block',
						'name'    => $param_name,
						'value'   => $value
					);
				}

				Helpers::db()->table('lp_params')
					->insert($params, ['item_id', 'type', 'name'], 'replace');
			}

			Helpers::cache()->forget($context['lp_block']['type'] . '_addon_b' . $item);
			Helpers::cache()->forget($context['lp_block']['type'] . '_addon_u' . $context['user']['id']);
			Helpers::cache()->forget($context['lp_block']['type'] . '_addon_b' . $item . '_u' . $context['user']['id']);
		}

		if (Helpers::post()->filled('clone'))
			return $item;

		Helpers::cache()->forget('active_blocks');

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

		$request = Helpers::db()->table('lp_blocks AS b')
			->select('b.block_id', 'b.icon', 'b.icon_type', 'b.type', 'b.content', 'b.placement', 'b.priority', 'b.permissions', 'b.status', 'b.areas')
			->addSelect('b.title_class', 'b.title_style', 'b.content_class', 'b.content_style', 'bt.lang', 'bt.title', 'bp.name', 'bp.value')
			->leftJoin('lp_titles AS bt', 'b.block_id = bt.item_id AND bt.type = "block"')
			->leftJoin('lp_params AS bp', 'b.block_id = bp.item_id AND bp.type = "block"')
			->where('b.block_id', $item)
			->get();

		if (empty($request)) {
			self::changeBackButton();
			fatal_lang_error('lp_block_not_found', false, null, 404);
		}

		foreach ($request as $row) {
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

		return $data ?? [];
	}

	/**
	 * Change back button position and back button href
	 *
	 * Меняем положение и href кнопки «Назад»
	 *
	 * @return void
	 */
	private static function changeBackButton()
	{
		addInlineJavaScript('
		const backButton = document.querySelector("#fatal_error + .centertext > a.button");
		backButton.setAttribute("href", smf_scripturl + "?action=admin;area=lp_blocks");
		backButton.className = "button floatnone";', true);
	}
}
