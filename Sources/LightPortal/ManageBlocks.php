<?php

namespace Bugo\LightPortal;

/**
 * ManageBlocks.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
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
	private const AREAS_PATTERN = '^[a-z][a-z0-9=|\-,]+$';

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

		loadTemplate('LightPortal/ManageBlocks');

		$context['page_title'] = $txt['lp_portal'] . ' - ' . $txt['lp_blocks_manage'];

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title'       => '<a href="https://dragomano.github.io/Light-Portal/" target="_blank" rel="noopener"><span class="main_icons help"></span></a> ' . LP_NAME,
			'description' => $txt['lp_blocks_manage_description']
		);

		$this->doActions();

		$context['lp_current_blocks'] = $this->getAll();
		$context['lp_current_blocks'] = array_merge(array_flip(array_keys($context['lp_block_placements'])), $context['lp_current_blocks']);

		$context['sub_template'] = 'manage_blocks';
	}

	/**
	 * Get a list of all blocks sorted by placement
	 *
	 * Получаем список всех блоков с разбивкой по размещению
	 *
	 * @return array
	 */
	public function getAll(): array
	{
		global $smcFunc, $user_info;

		$request = $smcFunc['db_query']('', '
			SELECT b.block_id, b.user_id, b.icon, b.type, b.note, b.placement, b.priority, b.permissions, b.status, b.areas, bt.lang, bt.title
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})' . ($user_info['is_admin'] ? '' : '
			WHERE b.user_id = {int:user_id}') . '
			ORDER BY b.placement DESC, b.priority',
			array(
				'user_id' => $user_info['id']
			)
		);

		$currentBlocks = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (!isset($currentBlocks[$row['placement']][$row['block_id']]))
				$currentBlocks[$row['placement']][$row['block_id']] = array(
					'user_id'     => $row['user_id'],
					'icon'        => Helpers::getIcon($row['icon']),
					'type'        => $row['type'],
					'note'        => $row['note'],
					'priority'    => $row['priority'],
					'permissions' => $row['permissions'],
					'status'      => $row['status'],
					'areas'       => str_replace(',', PHP_EOL, $row['areas'])
				);

			$currentBlocks[$row['placement']][$row['block_id']]['title'][$row['lang']] = $row['title'];

			$this->prepareMissingBlockTypes($row['type']);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $currentBlocks;
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

		if (!empty($data['toggle_item']))
			self::toggleStatus([(int) $data['toggle_item']]);

		$this->updatePriority();

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
			DELETE FROM {db_prefix}lp_blocks
			WHERE block_id IN ({array_int:items})',
			array(
				'items' => $items
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_titles
			WHERE item_id IN ({array_int:items})
				AND type = {literal:block}',
			array(
				'items' => $items
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_params
			WHERE item_id IN ({array_int:items})
				AND type = {literal:block}',
			array(
				'items' => $items
			)
		);

		$smcFunc['lp_num_queries'] += 3;

		Addons::run('onBlockRemoving', array($items));
	}

	/**
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
		$context['lp_block']['icon'] = Helpers::getIcon($context['lp_block']['icon']);

		if (!empty($context['lp_block']['id'])) {
			loadTemplate('LightPortal/ManageBlocks');

			ob_start();
			show_block_entry($context['lp_block']['id'], $context['lp_block']);

			$result = [
				'success' => true,
				'block'   => ob_get_clean()
			];
		}

		Helpers::cache()->forget('active_blocks');

		exit(json_encode($result));
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

		$new_status = $smcFunc['db_title'] === POSTGRE_TITLE ? 'CASE WHEN status = 1 THEN 0 ELSE 1 END' : '!status';

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_blocks
			SET status = ' . $new_status . '
			WHERE block_id IN ({array_int:items})',
			array(
				'items' => $items
			)
		);

		$smcFunc['lp_num_queries']++;
	}

	/**
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
			'description' => $txt['lp_blocks_add_description']
		);

		$context['current_block']['placement'] = Helpers::request('placement', '');

		$this->prepareBlockList();

		$context['sub_template'] = 'block_add';

		$json = Helpers::request()->json();
		$type = $json['add_block'] ?? Helpers::post('add_block', '') ?? '';

		if (empty($type))
			return;

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
			'description' => $txt['lp_blocks_edit_description']
		);

		Helpers::prepareForumLanguages();

		$context['sub_template']  = 'block_post';
		$context['current_block'] = $this->getData($item);

		if (empty($context['user']['is_admin']) && $context['user']['id'] != $context['current_block']['user_id'])
			fatal_lang_error('lp_block_not_editable', false);

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
	 * @return array
	 */
	private function getOptions(): array
	{
		global $context;

		$options = [];

		foreach (array_keys($context['lp_content_types']) as $type) {
			$options[$type] = [
				'content' => true
			];
		}

		Addons::run('blockOptions', array(&$options));

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
				'block_id'      => FILTER_VALIDATE_INT,
				'icon'          => FILTER_SANITIZE_STRING,
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

			$post_data = filter_input_array(INPUT_POST, $args);

			$parameters = [];

			Addons::run('validateBlockData', array(&$parameters, $context['current_block']['type']));

			$post_data['parameters'] = filter_var_array(Helpers::post()->only(array_keys($parameters)), $parameters);

			$this->findErrors($post_data);
		}

		$options = $this->getOptions();

		if (empty($options[$context['current_block']['type']]))
			$options[$context['current_block']['type']] = [];

		$block_options = $context['current_block']['options'] ?? $options[$context['current_block']['type']];

		$context['lp_block'] = array(
			'id'            => $post_data['block_id'] ?? $context['current_block']['id'] ?? 0,
			'user_id'       => $user_info['is_admin'] || ! allowedTo('light_portal_manage_own_blocks') ? 0 : ($context['current_block']['user_id'] ?? $user_info['id']),
			'title'         => $context['current_block']['title'] ?? [],
			'icon'          => !empty($post_data['block_id']) ? ($post_data['icon'] ?? '') : ($post_data['icon'] ?? $context['current_block']['icon'] ?? ''),
			'type'          => $post_data['type'] ?? $context['current_block']['type'] ?? '',
			'note'          => $post_data['note'] ?? $context['current_block']['note'] ?? '',
			'content'       => $post_data['content'] ?? $context['current_block']['content'] ?? '',
			'placement'     => $post_data['placement'] ?? $context['current_block']['placement'] ?? '',
			'priority'      => $post_data['priority'] ?? $context['current_block']['priority'] ?? 0,
			'permissions'   => $post_data['permissions'] ?? $context['current_block']['permissions'] ?? $modSettings['lp_permissions_default'] ?? 2,
			'status'        => $context['current_block']['status'] ?? Block::STATUS_ACTIVE,
			'areas'         => $post_data['areas'] ?? $context['current_block']['areas'] ?? 'all',
			'title_class'   => $post_data['title_class'] ?? $context['current_block']['title_class'] ?? array_key_first($context['lp_all_title_classes']),
			'title_style'   => $post_data['title_style'] ?? $context['current_block']['title_style'] ?? '',
			'content_class' => $post_data['content_class'] ?? $context['current_block']['content_class'] ?? array_key_first($context['lp_all_content_classes']),
			'content_style' => $post_data['content_style'] ?? $context['current_block']['content_style'] ?? '',
			'options'       => $options[$context['current_block']['type']]
		);

		if (!empty($context['lp_block']['options']['no_content_class'])) {
			$context['lp_block']['content_class'] = '';
		}

		$context['lp_block']['icon_template'] = Helpers::getIcon($context['lp_block']['icon']) . $context['lp_block']['icon'];

		$context['lp_block']['priority'] = empty($context['lp_block']['id']) ? $this->getPriority() : $context['lp_block']['priority'];

		if (!empty($context['lp_block']['options']['parameters'])) {
			foreach ($context['lp_block']['options']['parameters'] as $option => $value) {
				if (!empty($parameters[$option]) && !empty($post_data['parameters']) && !isset($post_data['parameters'][$option])) {
					if ($parameters[$option] == FILTER_SANITIZE_STRING)
						$post_data[$option] = '';

					if ($parameters[$option] == FILTER_VALIDATE_BOOLEAN)
						$post_data['parameters'][$option] = 0;

					if (is_array($parameters[$option]) && $parameters[$option]['flags'] == FILTER_REQUIRE_ARRAY)
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
			'options' => array("regexp" => '/' . self::AREAS_PATTERN . '/')
		);
		if (!empty($data['areas']) && empty(Helpers::validate($data['areas'], $areas_format)))
			$post_errors[] = 'no_valid_areas';

		Addons::run('findBlockErrors', array($data, &$post_errors));

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
		global $context, $txt;

		checkSubmitOnce('register');

		Helpers::prepareIconList();

		foreach ($context['languages'] as $lang) {
			$context['posting_fields']['title_' . $lang['filename']]['label']['text'] = $txt['lp_title'] . (count($context['languages']) > 1 ? ' [' . $lang['name'] . ']' : '');
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
		$context['posting_fields']['icon']['input'] = array(
			'type'    => 'select',
			'options' => [],
			'tab'     => 'appearance'
		);

		$context['posting_fields']['placement']['label']['text'] = $txt['lp_block_placement'];
		$context['posting_fields']['placement']['input'] = array(
			'type' => 'select',
			'tab'  => 'access_placement'
		);

		foreach ($context['lp_block_placements'] as $level => $title) {
			$context['posting_fields']['placement']['input']['options'][$title] = array(
				'value'    => $level,
				'selected' => $level == $context['lp_block']['placement']
			);
		}

		$context['posting_fields']['permissions']['label']['text'] = $txt['edit_permissions'];
		$context['posting_fields']['permissions']['input'] = array(
			'type' => 'select',
			'tab'  => 'access_placement'
		);

		foreach ($txt['lp_permissions'] as $level => $title) {
			if (empty($context['user']['is_admin']) && empty($level))
				continue;

			$context['posting_fields']['permissions']['input']['options'][$title] = array(
				'value'    => $level,
				'selected' => $level == $context['lp_block']['permissions']
			);
		}

		$context['posting_fields']['areas']['label']['text'] = $txt['lp_block_areas'];
		$context['posting_fields']['areas']['input'] = array(
			'type' => 'text',
			'after' => $this->getAreasInfo(),
			'attributes' => array(
				'maxlength' => 255,
				'value'     => $context['lp_block']['areas'],
				'required'  => true,
				'pattern'   => self::AREAS_PATTERN,
				'style'     => 'width: 100%'
			),
			'tab' => 'access_placement'
		);

		$context['posting_fields']['title_class']['label']['text'] = $txt['lp_block_title_class'];
		$context['posting_fields']['title_class']['input'] = array(
			'type'    => 'select',
			'options' => [],
			'tab'     => 'appearance'
		);

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
				'type'    => 'select',
				'options' => [],
				'tab'     => 'appearance'
			);

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

		if (!empty($context['lp_block']['options']['content'])) {
			$context['posting_fields']['content']['label']['html'] = ' ';

			if ($context['lp_block']['type'] !== 'bbc') {

				$context['posting_fields']['content']['input'] = array(
					'type' => 'textarea',
					'attributes' => array(
						'value' => $context['lp_block']['content']
					),
					'tab' => 'content'
				);
			} else {
				Helpers::createBbcEditor($context['lp_block']['content']);

				ob_start();
				template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
				$context['posting_fields']['content']['input']['html'] = '<div>' . ob_get_clean()  . '</div>';

				$context['posting_fields']['content']['input']['tab'] = 'content';
			}
		}

		Addons::run('prepareBlockFields');

		Helpers::preparePostFields();

		$context['lp_block_tab_tuning'] = $this->hasParameters($context['posting_fields']);
	}

	/**
	 * Get a table with possible areas
	 *
	 * Получаем табличку с возможными областями
	 *
	 * @return string
	 */
	private function getAreasInfo(): string
	{
		global $context, $txt;

		$example_areas = array(
			'all',
			'custom_action',
			'pages',
			LP_PAGE_PARAM . '=alias',
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
	private function hasParameters(array $data = [], string $check_key = 'tab', string $check_value = 'tuning'): bool
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
	 * @return void
	 */
	private function prepareEditor()
	{
		global $context;

		Addons::run('prepareEditor', array($context['lp_block']));
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

		$context['preview_title']   = $context['lp_block']['title'][$context['user']['language']] ?? '';
		$context['preview_content'] = $smcFunc['htmlspecialchars']($context['lp_block']['content'], ENT_QUOTES);

		Helpers::cleanBbcode($context['preview_title']);
		censorText($context['preview_title']);
		censorText($context['preview_content']);

		!empty($context['preview_content'])
			? Helpers::parseContent($context['preview_content'], $context['lp_block']['type'])
			: Helpers::prepareContent($context['preview_content'], $context['lp_block']['type']);

		$context['page_title']    = $txt['preview'] . ($context['preview_title'] ? ' - ' . $context['preview_title'] : '');
		$context['preview_title'] = Helpers::getPreviewTitle(Helpers::getIcon($context['lp_block']['icon']));
	}

	/**
	 * Get correct priority for a new block
	 *
	 * Получаем правильный приоритет для нового блока
	 *
	 * @return int
	 */
	private function getPriority(): int
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
	 * @param int $item
	 * @return int|void
	 */
	private function setData(int $item = 0)
	{
		global $context;

		if (!empty($context['post_errors']) || (Helpers::post()->has('save') === false && Helpers::post()->has('save_exit') === false && Helpers::post()->has('clone') === false))
			return 0;

		checkSubmitOnce('check');

		Helpers::prepareBbcContent($context['lp_block']);

		if (empty($item)) {
			$item = $this->addData();
		} else {
			$this->updateData($item);
		}

		if (Helpers::post()->notEmpty('clone'))
			return $item;

		Helpers::cache()->flush();

		if (Helpers::post()->has('save_exit'))
			redirectexit('action=admin;area=lp_blocks;sa=main');

		if (Helpers::post()->has('save'))
			redirectexit('action=admin;area=lp_blocks;sa=edit;id=' . $item);
	}

	/**
	 * @return int
	 */
	private function addData(): int
	{
		global $smcFunc, $context;

		$smcFunc['db_transaction']('begin');

		$item = $smcFunc['db_insert']('',
			'{db_prefix}lp_blocks',
			array(
				'user_id'       => 'int',
				'icon'          => 'string',
				'type'          => 'string',
				'note'          => 'string',
				'content'       => 'string-65534',
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
				$context['lp_block']['user_id'],
				$context['lp_block']['icon'],
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

		if (empty($item)) {
			$smcFunc['db_transaction']('rollback');
			return 0;
		}

		Addons::run('onBlockSaving', array($item));

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

		$smcFunc['db_transaction']('commit');

		return $item;
	}

	/**
	 * @param int $item
	 * @return void
	 */
	private function updateData(int $item)
	{
		global $smcFunc, $context;

		$smcFunc['db_transaction']('begin');

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_blocks
			SET icon = {string:icon}, type = {string:type}, note = {string:note}, content = {string:content}, placement = {string:placement}, permissions = {int:permissions}, areas = {string:areas}, title_class = {string:title_class}, title_style = {string:title_style}, content_class = {string:content_class}, content_style = {string:content_style}
			WHERE block_id = {int:block_id}',
			array(
				'block_id'      => $item,
				'icon'          => $context['lp_block']['icon'],
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

		Addons::run('onBlockSaving', array($item));

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

		$smcFunc['db_transaction']('commit');

		Helpers::cache()->forget($context['lp_block']['type'] . '_addon_b' . $item);
		Helpers::cache()->forget($context['lp_block']['type'] . '_addon_u' . $context['user']['id']);
		Helpers::cache()->forget($context['lp_block']['type'] . '_addon_b' . $item . '_u' . $context['user']['id']);
	}

	/**
	 * @param int $item
	 * @return array
	 */
	public function getData(int $item): array
	{
		global $smcFunc;

		if (empty($item))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT
				b.block_id, b.user_id, b.icon, b.type, b.note, b.content, b.placement, b.priority, b.permissions, b.status, b.areas, b.title_class, b.title_style, b.content_class, b.content_style, bt.lang, bt.title, bp.name, bp.value
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})
				LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {literal:block})
			WHERE b.block_id = {int:item}',
			array(
				'item' => $item
			)
		);

		if ($smcFunc['db_num_rows']($request) == 0) {
			$this->changeBackButton();
			fatal_lang_error('lp_block_not_found', false, null, 404);
		}

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if ($row['type'] === 'bbc') {
				Helpers::require('Subs-Post');
				$row['content'] = un_preparsecode($row['content']);
			}

			censorText($row['content']);

			if (!isset($data))
				$data = array(
					'id'            => $row['block_id'],
					'user_id'       => $row['user_id'],
					'icon'          => $row['icon'],
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

			if (!empty($row['lang']))
				$data['title'][$row['lang']] = $row['title'];

			if (!empty($row['name']))
				$data['options']['parameters'][$row['name']] = $row['value'];
		}

		if (!empty($data['type']))
			$this->prepareMissingBlockTypes($data['type']);

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

	/**
	 * Form a list of addons that not installed
	 *
	 * Формируем список неустановленных плагинов
	 *
	 * @param string $type
	 * @return void
	 */
	private function prepareMissingBlockTypes(string $type)
	{
		global $txt, $context;

		if (!isset($txt['lp_' . $type]['title']))
			$context['lp_missing_block_types'][$type] = '<span class="error">' . sprintf($txt['lp_addon_not_installed'], Helpers::getCamelName($type)) . '</span>';
	}

	/**
	 * @return void
	 */
	private function prepareBlockList()
	{
		global $context, $txt;

		$plugins = array_merge($context['lp_enabled_plugins'], array_keys(Subs::getContentTypes()));

		$context['lp_all_blocks'] = [];
		foreach ($plugins as $addon) {
			$addon = Helpers::getSnakeName($addon);

			// We need blocks only
			if (!isset($txt['lp_' . $addon]['title']) || isset($context['lp_all_blocks'][$addon]))
				continue;

			$context['lp_all_blocks'][$addon] = [
				'type'  => $addon,
				'icon'  => $context['lp_' . $addon]['icon'],
				'title' => $txt['lp_' . $addon]['title'],
				'desc'  => $txt['lp_' . $addon]['block_desc'] ?? $txt['lp_' . $addon]['description']
			];
		}

		$titles = array_column($context['lp_all_blocks'], 'title');
		array_multisort($titles, SORT_ASC, $context['lp_all_blocks']);
	}
}
