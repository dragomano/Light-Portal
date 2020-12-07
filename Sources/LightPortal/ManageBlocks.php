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
 * @version 1.3
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
	private $areas_pattern = '^[a-z][a-z0-9=|\-,]+$';

	/**
	 * Manage blocks
	 *
	 * Управление блоками
	 *
	 * @return void
	 */
	public function main()
	{
		global $context, $txt;

		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', array('external' => true, 'defer' => true));
		loadJavaScriptFile('light_portal/change_priority.js', array('minimize' => true));

		loadTemplate('LightPortal/ManageBlocks');

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_blocks_manage'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => LP_NAME,
			'description' => $txt['lp_blocks_manage_tab_description']
		);

		$this->doActions();

		$context['lp_current_blocks'] = $this->getAll();
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
	public function getAll()
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT b.block_id, b.icon, b.icon_type, b.type, b.note, b.placement, b.priority, b.permissions, b.status, b.areas, bt.lang, bt.title
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
					'note'        => $row['note'],
					'priority'    => $row['priority'],
					'permissions' => $row['permissions'],
					'status'      => $row['status'],
					'areas'       => str_replace(',', PHP_EOL, $row['areas'])
				);

			$current_blocks[$row['placement']][$row['block_id']]['title'][$row['lang']] = $row['title'];

			Helpers::findMissingBlockTypes($row['type']);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $current_blocks;
	}

	/**
	 * Possible actions with blocks
	 *
	 * Возможные действия с блоками
	 *
	 * @return void
	 */
	public function doActions()
	{
		if (Helpers::request()->has('actions') === false)
			return;

		$data = Helpers::request()->json();

		if (!empty($data['del_item']))
			$this->remove([(int) $data['del_item']]);

		if (!empty($data['clone_block']))
			$this->makeCopy((int) $data['clone_block']);

		if (!empty($data['status']) && !empty($data['item']))
			$this->toggleStatus([(int) $data['item']], $data['status'] == 'off' ? Block::STATUS_ACTIVE : Block::STATUS_INACTIVE);

		$this->updatePriority();

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
	private function remove(array $items)
	{
		global $smcFunc;

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

		$smcFunc['lp_num_queries'] += 3;

		Subs::runAddons('onBlockRemoving', array($items));
	}

	/**
	 * Cloning a block
	 *
	 * Клонирование блока
	 *
	 * @param int $item
	 * @return void
	 */
	private function makeCopy(int $item)
	{
		global $context;

		if (empty($item))
			return;

		Helpers::post()->put('clone', true);
		$result['success'] = false;

		$context['lp_block']         = $this->getData($item);
		$context['lp_block']['id']   = $this->setData();
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
	public function toggleStatus(array $items, int $status = 0)
	{
		global $smcFunc;

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

		$smcFunc['lp_num_queries']++;
	}

	/**
	 * Update priority
	 *
	 * Обновление приоритета
	 *
	 * @return void
	 */
	private function updatePriority()
	{
		global $smcFunc;

		$data = Helpers::request()->json();

		if (!isset($data['update_priority']))
			return;

		$blocks = $data['update_priority'];

		$conditions = '';
		foreach ($blocks as $priority => $item) {
			$conditions .= ' WHEN block_id = ' . $item . ' THEN ' . $priority;
		}

		if (empty($conditions))
			return;

		if (!empty($blocks) && is_array($blocks)) {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_blocks
				SET priority = CASE ' . $conditions . ' ELSE priority END
				WHERE block_id IN ({array_int:blocks})',
				array(
					'blocks' => $blocks
				)
			);

			$smcFunc['lp_num_queries']++;

			if (!empty($data['update_placement'])) {
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}lp_blocks
					SET placement = {string:placement}
					WHERE block_id IN ({array_int:blocks})',
					array(
						'placement' => $data['update_placement'],
						'blocks'    => $blocks
					)
				);

				$smcFunc['lp_num_queries']++;
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
	public function add()
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

		Helpers::prepareForumLanguages();

		$context['sub_template'] = 'block_post';

		$this->validateData();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();
		$this->setData();
	}

	/**
	 * Editing a block
	 *
	 * Редактирование блока
	 *
	 * @return void
	 */
	public function edit()
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

		Helpers::prepareForumLanguages();

		$context['sub_template']  = 'block_post';
		$context['current_block'] = $this->getData($item);

		if (Helpers::post()->has('remove')) {
			$this->remove([$item]);
			redirectexit('action=admin;area=lp_blocks;sa=main');
		}

		$this->validateData();

		$context['canonical_url'] = $scripturl . '?action=admin;area=lp_blocks;sa=edit;id=' . $context['lp_block']['id'];

		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();
		$this->setData($context['lp_block']['id']);
	}

	/**
	 * Get the parameters of all blocks
	 *
	 * Получаем параметры всех блоков
	 *
	 * @return array
	 */
	private function getOptions()
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
	private function validateData()
	{
		global $context, $user_info;

		if (Helpers::post()->has('save') || Helpers::post()->has('preview')) {
			$args = array(
				'block_id'      => FILTER_VALIDATE_INT,
				'icon'          => FILTER_SANITIZE_STRING,
				'icon_type'     => FILTER_SANITIZE_STRING,
				'type'          => FILTER_SANITIZE_STRING,
				'note'          => FILTER_SANITIZE_STRING,
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

			foreach ($context['languages'] as $lang) {
				$args['title_' . $lang['filename']] = FILTER_SANITIZE_STRING;
			}

			$parameters = [];

			Subs::runAddons('validateBlockData', array(&$parameters, $context['current_block']['type']));

			$post_data = filter_input_array(INPUT_POST, $args);
			$post_data['parameters'] = filter_input_array(INPUT_POST, $parameters);

			$this->findErrors($post_data);
		}

		$options = $this->getOptions();

		if (empty($options[$context['current_block']['type']]))
			$options[$context['current_block']['type']] = [];

		$block_options = $context['current_block']['options'] ?? $options[$context['current_block']['type']];

		$context['lp_block'] = array(
			'id'            => $post_data['block_id'] ?? $context['current_block']['id'] ?? 0,
			'title'         => $context['current_block']['title'] ?? [],
			'icon'          => trim($post_data['icon'] ?? $context['current_block']['icon'] ?? ''),
			'icon_type'     => $post_data['icon_type'] ?? $context['current_block']['icon_type'] ?? 'fas',
			'type'          => $post_data['type'] ?? $context['current_block']['type'] ?? '',
			'note'          => $post_data['note'] ?? $context['current_block']['note'] ?? '',
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

		$context['lp_block']['priority'] = empty($context['lp_block']['id']) ? $this->getPriority() : $context['lp_block']['priority'];

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

		foreach ($context['languages'] as $lang) {
			$context['lp_block']['title'][$lang['filename']] = $post_data['title_' . $lang['filename']] ?? $context['lp_block']['title'][$lang['filename']] ?? '';
		}

		Helpers::cleanBbcode($context['lp_block']['title']);
	}

	/**
	 * Check that the fields are filled in correctly
	 *
	 * Проверяем правильность заполнения полей
	 *
	 * @param array $data
	 * @return void
	 */
	private function findErrors(array $data)
	{
		global $context, $txt;

		$post_errors = [];

		if (empty($data['areas']))
			$post_errors[] = 'no_areas';

		$areas_format = array(
			'options' => array("regexp" => '/' . $this->areas_pattern . '/')
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
	private function prepareFormFields()
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

		$context['posting_fields']['note']['label']['text'] = $txt['lp_block_note'];
		$context['posting_fields']['note']['input'] = array(
			'type' => 'text',
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['note'] ?? '',
				'style'     => 'width: 100%'
			),
			'tab' => 'content'
		);

		$context['posting_fields']['icon']['label']['text'] = $txt['current_icon'];
		$context['posting_fields']['icon']['label']['after'] = '<br><span class="smalltext"><a href="https://fontawesome.com/cheatsheet/free" target="_blank" rel="noopener">' . $txt['lp_block_icon_cheatsheet'] . '</a></span>';
		$context['posting_fields']['icon']['input'] = array(
			'type' => 'text',
			'after' => '<span x-ref="preview">' . Helpers::getIcon() . '</span>',
			'attributes' => array(
				'id'        => 'icon',
				'maxlength' => 30,
				'value'     => $context['lp_block']['icon'],
				'x-ref'     => 'icon',
				'@change'   => 'block.changeIcon($refs.preview, $refs.icon, $refs.icon_type)'
			),
			'tab' => 'appearance'
		);

		$context['posting_fields']['icon_type']['label']['text'] = $txt['lp_block_icon_type'];
		$context['posting_fields']['icon_type']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id'      => 'icon_type',
				'x-ref'   => 'icon_type',
				'@change' => 'block.changeIcon($refs.preview, $refs.icon, $refs.icon_type)'
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
			'after' => $this->getAreasInfo(),
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['areas'],
				'required'  => true,
				'pattern'   => $this->areas_pattern,
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

		$context['lp_block_tab_tuning'] = $this->hasParameters($context['posting_fields']);

		loadTemplate('LightPortal/ManageSettings');
	}

	/**
	 * Get a table with possible areas
	 *
	 * Получаем табличку с возможными областями
	 *
	 * @return string
	 */
	private function getAreasInfo()
	{
		global $context, $txt;

		$example_areas = array(
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

		$context['lp_possible_areas'] = array_combine($example_areas, $txt['lp_block_areas_values']);

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
	private function hasParameters(array $data = [], string $check_key = 'tab', string $check_value = 'tuning')
	{
		if (empty($data))
			return false;

		$result = [];
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
	private function prepareEditor()
	{
		global $context;

		if (!empty($context['lp_block']['options']['content']) && $context['lp_block']['type'] === 'bbc')
			Helpers::createBbcEditor($context['lp_block']['content']);

		Subs::runAddons('prepareEditor', array($context['lp_block']));
	}

	/**
	 * Preview
	 *
	 * Предварительный просмотр
	 *
	 * @return void
	 */
	private function preparePreview()
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
	private function getPriority()
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

		[$priority] = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return (int) $priority;
	}

	/**
	 * Creating or updating a block
	 *
	 * Создаем или обновляем блок
	 *
	 * @param int $item
	 * @return int|void
	 */
	private function setData(int $item = 0)
	{
		global $context, $smcFunc;

		if (!empty($context['post_errors']) || (Helpers::post()->has('save') === false && Helpers::post()->has('clone') === false))
			return;

		checkSubmitOnce('check');

		if (empty($item)) {
			$item = $smcFunc['db_insert']('',
				'{db_prefix}lp_blocks',
				array(
					'icon'          => 'string-60',
					'icon_type'     => 'string-10',
					'type'          => 'string',
					'note'          => 'string',
					'content'       => 'string-' . MAX_MSG_LENGTH,
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
					$context['lp_block']['note'],
					$context['lp_block']['content'],
					$context['lp_block']['placement'],
					$context['lp_block']['priority'],
					$context['lp_block']['permissions'],
					$context['lp_block']['status'],
					$context['lp_block']['areas'],
					$context['lp_block']['title_class'],
					$context['lp_block']['title_style'],
					$context['lp_block']['content_class'],
					$context['lp_block']['content_style']
				),
				array('block_id'),
				1
			);

			$smcFunc['lp_num_queries']++;

			Subs::runAddons('onBlockSaving', array($item));

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

				$smcFunc['lp_num_queries']++;
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
		} else {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}lp_blocks
				SET icon = {string:icon}, icon_type = {string:icon_type}, type = {string:type}, note = {string:note}, content = {string:content}, placement = {string:placement}, permissions = {int:permissions}, areas = {string:areas}, title_class = {string:title_class}, title_style = {string:title_style}, content_class = {string:content_class}, content_style = {string:content_style}
				WHERE block_id = {int:block_id}',
				array(
					'block_id'      => $item,
					'icon'          => $context['lp_block']['icon'],
					'icon_type'     => $context['lp_block']['icon_type'],
					'type'          => $context['lp_block']['type'],
					'note'          => $context['lp_block']['note'],
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

			$smcFunc['lp_num_queries']++;

			Subs::runAddons('onBlockSaving', array($item));

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

				$smcFunc['lp_num_queries']++;
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
	public function getData(int $item)
	{
		global $smcFunc;

		if (empty($item))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT
				b.block_id, b.icon, b.icon_type, b.type, b.note, b.content, b.placement, b.priority, b.permissions, b.status, b.areas, b.title_class, b.title_style, b.content_class, b.content_style, bt.lang, bt.title, bp.name, bp.value
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {string:type})
				LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {string:type})
			WHERE b.block_id = {int:item}',
			array(
				'type' => 'block',
				'item' => $item
			)
		);

		if ($smcFunc['db_num_rows']($request) == 0) {
			$this->changeBackButton();
			fatal_lang_error('lp_block_not_found', false, null, 404);
		}

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['content']);

			if (!isset($data))
				$data = array(
					'id'            => $row['block_id'],
					'icon'          => $row['icon'],
					'icon_type'     => $row['icon_type'],
					'type'          => $row['type'],
					'note'          => $row['note'],
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
		$smcFunc['lp_num_queries']++;

		return $data ?? [];
	}

	/**
	 * Change back button position and back button href
	 *
	 * Меняем положение и href кнопки «Назад»
	 *
	 * @return void
	 */
	private function changeBackButton()
	{
		addInlineJavaScript('
		const backButton = document.querySelector("#fatal_error + .centertext > a.button");
		backButton.setAttribute("href", smf_scripturl + "?action=admin;area=lp_blocks");
		backButton.className = "button floatnone";', true);
	}
}
