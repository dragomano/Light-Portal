<?php declare(strict_types=1);

/**
 * BlockArea.php
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
use Bugo\LightPortal\Repositories\BlockRepository;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

if (! defined('SMF'))
	die('No direct access...');

final class BlockArea
{
	use Area, Helper;

	private BlockRepository $repository;

	private const AREAS_PATTERN = '^[a-z][a-z0-9=|\-,]+$';

	public function __construct()
	{
		$this->repository = new BlockRepository;
	}

	public function main()
	{
		$this->loadTemplate('LightPortal/ManageBlocks');

		$this->context['page_title'] = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_blocks_manage'];

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_blocks_manage_description'],
		];

		$this->doActions();

		$this->context['lp_current_blocks'] = $this->repository->getAll();

		$this->context['sub_template'] = 'manage_blocks';
	}

	public function doActions()
	{
		if ($this->request()->has('actions') === false)
			return;

		$data = $this->request()->json();

		if (isset($data['del_item']))
			$this->remove([(int) $data['del_item']]);

		if (isset($data['clone_block']))
			$this->makeCopy((int) $data['clone_block']);

		if (isset($data['toggle_item']))
			$this->toggleStatus([(int) $data['toggle_item']]);

		$this->updatePriority();

		$this->cache()->flush();

		exit;
	}

	public function add()
	{
		$this->loadTemplate('LightPortal/ManageBlocks');

		$this->context['page_title']    = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_blocks_add_title'];
		$this->context['canonical_url'] = $this->scripturl . '?action=admin;area=lp_blocks;sa=add';

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_blocks_add_description']
		];

		$this->context['current_block']['placement'] = $this->request('placement', '');

		$this->prepareBlockList();

		$this->context['sub_template'] = 'block_add';

		$json = $this->request()->json();
		$type = $json['add_block'] ?? $this->post('add_block', '') ?? '';

		if (empty($type))
			return;

		$this->context['current_block']['type'] = $type;

		$this->prepareForumLanguages();

		$this->context['sub_template'] = 'block_post';

		$this->validateData();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData();
	}

	public function edit()
	{
		$item = (int) ($this->request('block_id') ?: $this->request('id'));

		if (empty($item))
			$this->fatalLangError('lp_block_not_found', false, null, 404);

		$this->loadTemplate('LightPortal/ManageBlocks');

		$this->context['page_title'] = $this->txt['lp_portal'] . ' - ' . $this->txt['lp_blocks_edit_title'];

		$this->context[$this->context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => $this->txt['lp_blocks_edit_description']
		];

		$this->prepareForumLanguages();

		$this->context['sub_template']  = 'block_post';
		$this->context['current_block'] = $this->repository->getData($item);

		if (empty($this->context['user']['is_admin']) && $this->context['user']['id'] != $this->context['current_block']['user_id'])
			$this->fatalLangError('lp_block_not_editable', false);

		if ($this->post()->has('remove')) {
			$this->remove([$item]);

			$this->redirect('action=admin;area=lp_blocks;sa=main');
		}

		$this->validateData();

		$this->context['canonical_url'] = $this->scripturl . '?action=admin;area=lp_blocks;sa=edit;id=' . $this->context['lp_block']['id'];

		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData((int) $this->context['lp_block']['id']);
	}

	private function remove(array $items)
	{
		if (empty($items))
			return;

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_blocks
			WHERE block_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_titles
			WHERE item_id IN ({array_int:items})
				AND type = {literal:block}',
			[
				'items' => $items,
			]
		);

		$this->smcFunc['db_query']('', '
			DELETE FROM {db_prefix}lp_params
			WHERE item_id IN ({array_int:items})
				AND type = {literal:block}',
			[
				'items' => $items,
			]
		);

		$this->context['lp_num_queries'] += 3;

		$this->hook('onBlockRemoving', [$items]);
	}

	private function makeCopy(int $item)
	{
		if (empty($item))
			return;

		$this->post()->put('clone', true);

		$result['success'] = false;

		$this->context['lp_block']       = $this->repository->getData($item);
		$this->context['lp_block']['id'] = $this->repository->setData();

		if ($this->context['lp_block']['id']) {
			$result = [
				'id'      => $this->context['lp_block']['id'],
				'success' => true
			];
		}

		$this->cache()->forget('active_blocks');

		exit(json_encode($result));
	}

	private function updatePriority()
	{
		$data = $this->request()->json();

		if (empty($data['update_priority']))
			return;

		$blocks = $data['update_priority'];

		$conditions = '';
		foreach ($blocks as $priority => $item) {
			$conditions .= ' WHEN block_id = ' . $item . ' THEN ' . $priority;
		}

		if (empty($conditions))
			return;

		if (is_array($blocks)) {
			$this->smcFunc['db_query']('', /** @lang text */ '
				UPDATE {db_prefix}lp_blocks
				SET priority = CASE ' . $conditions . ' ELSE priority END
				WHERE block_id IN ({array_int:blocks})',
				[
					'blocks' => $blocks,
				]
			);

			$this->context['lp_num_queries']++;

			if ($data['update_placement']) {
				$this->smcFunc['db_query']('', '
					UPDATE {db_prefix}lp_blocks
					SET placement = {string:placement}
					WHERE block_id IN ({array_int:blocks})',
					[
						'placement' => $data['update_placement'],
						'blocks'    => $blocks,
					]
				);

				$this->context['lp_num_queries']++;
			}
		}
	}

	private function getOptions(): array
	{
		$options = [];

		foreach (array_keys($this->context['lp_content_types']) as $type) {
			$options[$type] = [
				'content' => true
			];
		}

		$this->hook('blockOptions', [&$options]);

		return $options;
	}

	private function validateData()
	{
		if ($this->post()->only(['save', 'save_exit', 'preview'])) {
			$args = [
				'block_id'      => FILTER_VALIDATE_INT,
				'icon'          => FILTER_DEFAULT,
				'type'          => FILTER_DEFAULT,
				'note'          => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'content'       => FILTER_UNSAFE_RAW,
				'placement'     => FILTER_DEFAULT,
				'priority'      => FILTER_VALIDATE_INT,
				'permissions'   => FILTER_VALIDATE_INT,
				'areas'         => FILTER_DEFAULT,
				'title_class'   => FILTER_DEFAULT,
				'title_style'   => FILTER_DEFAULT,
				'content_class' => FILTER_DEFAULT,
				'content_style' => FILTER_DEFAULT,
			];

			foreach ($this->context['languages'] as $lang) {
				$args['title_' . $lang['filename']] = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}

			$post_data = filter_input_array(INPUT_POST, $args);

			$parameters = [];

			$this->hook('validateBlockData', [&$parameters, $this->context['current_block']['type']]);

			$post_data['parameters'] = filter_var_array($this->post()->only(array_keys($parameters)), $parameters);

			$this->findErrors($post_data);
		}

		$options = $this->getOptions();

		if (empty($options[$this->context['current_block']['type']]))
			$options[$this->context['current_block']['type']] = [];

		$block_options = $this->context['current_block']['options'] ?? $options[$this->context['current_block']['type']];

		if (empty($this->context['current_block']['id']) && empty($this->context['current_block']['icon']))
			$this->context['current_block']['icon'] = $this->context['lp_loaded_addons'][$this->context['current_block']['type']]['icon'];

		$this->context['lp_block'] = [
			'id'            => $post_data['block_id'] ?? $this->context['current_block']['id'] ?? 0,
			'user_id'       => $this->user_info['is_admin'] || ! $this->context['allow_light_portal_manage_own_blocks'] ? 0 : ($this->context['current_block']['user_id'] ?? $this->user_info['id']),
			'title'         => $this->context['current_block']['title'] ?? [],
			'icon'          => empty($post_data['block_id']) ? ($post_data['icon'] ?? $this->context['current_block']['icon'] ?? '') : ($post_data['icon'] ?? ''),
			'type'          => $post_data['type'] ?? $this->context['current_block']['type'] ?? '',
			'note'          => $post_data['note'] ?? $this->context['current_block']['note'] ?? '',
			'content'       => $post_data['content'] ?? $this->context['current_block']['content'] ?? '',
			'placement'     => $post_data['placement'] ?? $this->context['current_block']['placement'] ?? '',
			'priority'      => $post_data['priority'] ?? $this->context['current_block']['priority'] ?? 0,
			'permissions'   => $post_data['permissions'] ?? $this->context['current_block']['permissions'] ?? $this->modSettings['lp_permissions_default'] ?? 2,
			'status'        => $this->context['current_block']['status'] ?? 1,
			'areas'         => $post_data['areas'] ?? $this->context['current_block']['areas'] ?? 'all',
			'title_class'   => $post_data['title_class'] ?? $this->context['current_block']['title_class'] ?? array_key_first($this->context['lp_all_title_classes']),
			'title_style'   => $post_data['title_style'] ?? $this->context['current_block']['title_style'] ?? '',
			'content_class' => $post_data['content_class'] ?? $this->context['current_block']['content_class'] ?? array_key_first($this->context['lp_all_content_classes']),
			'content_style' => $post_data['content_style'] ?? $this->context['current_block']['content_style'] ?? '',
			'options'       => $options[$this->context['current_block']['type']],
		];

		if ($this->context['lp_block']['icon'] === 'undefined')
			$this->context['lp_block']['icon'] = '';

		$this->context['lp_block']['icon_template'] = $this->getIcon($this->context['lp_block']['icon']) . $this->context['lp_block']['icon'];

		$this->context['lp_block']['priority'] = empty($this->context['lp_block']['id']) ? $this->getPriority() : $this->context['lp_block']['priority'];

		$this->context['lp_block']['permissions'] = empty($this->context['user']['is_admin']) ? 4 : $this->context['lp_block']['permissions'];

		if (! empty($this->context['lp_block']['options']['no_content_class']))
			$this->context['lp_block']['content_class'] = '';

		if (isset($this->context['lp_block']['options']['parameters'])) {
			foreach ($this->context['lp_block']['options']['parameters'] as $option => $value) {
				if (isset($parameters[$option]) && isset($post_data['parameters']) && ! isset($post_data['parameters'][$option])) {
					if ($parameters[$option] === FILTER_DEFAULT)
						$post_data[$option] = '';

					if ($parameters[$option] === FILTER_VALIDATE_BOOLEAN)
						$post_data['parameters'][$option] = 0;

					if (is_array($parameters[$option]) && $parameters[$option]['flags'] === FILTER_REQUIRE_ARRAY)
						$post_data['parameters'][$option] = [];
				}

				$this->context['lp_block']['options']['parameters'][$option] = $post_data['parameters'][$option] ?? $block_options['parameters'][$option] ?? $value;
			}
		}

		foreach ($this->context['languages'] as $lang) {
			$this->context['lp_block']['title'][$lang['filename']] = $post_data['title_' . $lang['filename']] ?? $this->context['lp_block']['title'][$lang['filename']] ?? '';
		}

		$this->cleanBbcode($this->context['lp_block']['title']);
	}

	private function findErrors(array $data)
	{
		$post_errors = [];

		if (empty($data['areas']))
			$post_errors[] = 'no_areas';

		$areas_format['options'] = ['regexp' => '/' . self::AREAS_PATTERN . '/'];
		if ($data['areas'] && empty($this->validate($data['areas'], $areas_format)))
			$post_errors[] = 'no_valid_areas';

		$this->hook('findBlockErrors', [$data, &$post_errors]);

		if ($post_errors) {
			$this->post()->put('preview', true);
			$this->context['post_errors'] = [];

			foreach ($post_errors as $error)
				$this->context['post_errors'][] = $this->txt['lp_post_error_' . $error];
		}
	}

	private function prepareFormFields()
	{
		$this->checkSubmitOnce('register');

		$this->prepareIconList();

		foreach ($this->context['languages'] as $lang) {
			$title = $this->txt['lp_title'] . (count($this->context['languages']) > 1 ? ' [' . $lang['name'] . ']' : '');
			$this->context['posting_fields']['title_' . $lang['filename']]['label']['text'] = $title;
			$this->context['posting_fields']['title_' . $lang['filename']]['input'] = [
				'type'       => 'text',
				'tab'        => 'content',
				'attributes' => [
					'maxlength' => 255,
					'value'     => $this->context['lp_block']['title'][$lang['filename']] ?? '',
					'style'     => 'width: 100%',
				],
			];
		}

		$this->context['posting_fields']['note']['label']['text'] = $this->txt['lp_block_note'];
		$this->context['posting_fields']['note']['input'] = [
			'type'       => 'text',
			'tab'        => 'content',
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_block']['note'] ?? '',
				'style'     => 'width: 100%',
			],
		];

		$this->context['posting_fields']['placement']['label']['text'] = $this->txt['lp_block_placement'];
		$this->context['posting_fields']['placement']['input'] = [
			'type' => 'select',
			'tab'  => 'access_placement',
		];

		foreach ($this->context['lp_block_placements'] as $level => $title) {
			$this->context['posting_fields']['placement']['input']['options'][$title] = [
				'value'    => $level,
				'selected' => $level == $this->context['lp_block']['placement'],
			];
		}

		if ($this->context['user']['is_admin']) {
			$this->context['posting_fields']['permissions']['label']['text'] = $this->txt['edit_permissions'];
			$this->context['posting_fields']['permissions']['input'] = [
				'type' => 'select',
				'tab'  => 'access_placement',
			];

			foreach ($this->txt['lp_permissions'] as $level => $title) {
				if (empty($this->context['user']['is_admin']) && empty($level))
					continue;

				$this->context['posting_fields']['permissions']['input']['options'][$title] = [
					'value'    => $level,
					'selected' => $level == $this->context['lp_block']['permissions'],
				];
			}
		}

		$this->context['posting_fields']['areas']['label']['text'] = $this->txt['lp_block_areas'];
		$this->context['posting_fields']['areas']['input'] = [
			'type'       => 'text',
			'after'      => $this->getAreasInfo(),
			'tab'        => 'access_placement',
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_block']['areas'],
				'required'  => true,
				'pattern'   => self::AREAS_PATTERN,
				'style'     => 'width: 100%',
			],
		];

		$this->context['posting_fields']['icon']['label']['text'] = $this->txt['current_icon'];
		$this->context['posting_fields']['icon']['input'] = [
			'type'    => 'select',
			'tab'     => 'appearance',
			'options' => [],
		];

		$this->context['posting_fields']['title_class']['label']['html'] = '<label for="title_class">' . $this->txt['lp_block_title_class'] . '</label>';
		$this->context['posting_fields']['title_class']['input']['html'] = '<div id="title_class" name="title_class"></div>';
		$this->context['posting_fields']['title_class']['input']['tab']  = 'appearance';

		$this->context['posting_fields']['title_style']['label']['text'] = $this->txt['lp_block_title_style'];
		$this->context['posting_fields']['title_style']['input'] = [
			'type'       => 'textarea',
			'tab'        => 'appearance',
			'attributes' => [
				'maxlength' => 255,
				'value'     => $this->context['lp_block']['title_style'],
				'style'     => 'width: 100%',
			],
		];

		if (empty($this->context['lp_block']['options']['no_content_class'])) {
			$this->context['posting_fields']['content_class']['label']['html'] = '<label for="content_class">' . $this->txt['lp_block_content_class'] . '</label>';
			$this->context['posting_fields']['content_class']['input']['html'] = '<div id="content_class" name="content_class"></div>';
			$this->context['posting_fields']['content_class']['input']['tab']  = 'appearance';

			$this->context['posting_fields']['content_style']['label']['text'] = $this->txt['lp_block_content_style'];
			$this->context['posting_fields']['content_style']['input'] = [
				'type'       => 'textarea',
				'tab'        => 'appearance',
				'attributes' => [
					'maxlength' => 255,
					'value'     => $this->context['lp_block']['content_style'],
					'style'     => 'width: 100%',
				],
			];
		}

		if (isset($this->context['lp_block']['options']['content'])) {
			$this->context['posting_fields']['content']['label']['html'] = ' ';

			if ($this->context['lp_block']['type'] !== 'bbc') {
				$this->context['posting_fields']['content']['input'] = [
					'type'       => 'textarea',
					'tab'        => 'content',
					'attributes' => [
						'value' => $this->context['lp_block']['content'],
					],
				];
			} else {
				$this->createBbcEditor($this->context['lp_block']['content']);
			}
		}

		$this->hook('prepareBlockFields');

		$this->preparePostFields();

		$this->context['lp_block_tab_tuning'] = $this->hasParameters($this->context['posting_fields']);
	}

	private function getAreasInfo(): string
	{
		$example_areas = [
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
			'topic=id3|id7',
		];

		$this->txt['lp_block_areas_values'][1] = sprintf($this->txt['lp_block_areas_values'][1], 'home,portal,forum,search');
		$this->context['lp_possible_areas'] = array_combine($example_areas, $this->txt['lp_block_areas_values']);

		ob_start();

		template_show_areas_info();

		return ob_get_clean();
	}

	/**
	 * Check whether there are any parameters on the $check_value tab
	 *
	 * Проверяем, есть ли какие-нибудь параметры на вкладке $check_value
	 */
	private function hasParameters(array $data = [], string $check_key = 'tab', string $check_value = 'tuning'): bool
	{
		if (empty($data))
			return false;

		$result = [];
		foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($data), RecursiveIteratorIterator::LEAVES_ONLY) as $key => $value) {
			if ($check_key === $key) {
				$result[] = $value;
			}
		}

		return in_array($check_value, $result);
	}

	private function prepareEditor()
	{
		$this->hook('prepareEditor', [$this->context['lp_block']]);
	}

	private function preparePreview()
	{
		if ($this->post()->has('preview') === false)
			return;

		$this->checkSubmitOnce('free');

		$this->context['preview_title']   = $this->context['lp_block']['title'][$this->context['user']['language']] ?? '';
		$this->context['preview_content'] = $this->smcFunc['htmlspecialchars']($this->context['lp_block']['content'], ENT_QUOTES);

		$this->cleanBbcode($this->context['preview_title']);
		$this->censorText($this->context['preview_title']);
		$this->censorText($this->context['preview_content']);

		$this->context['preview_content'] = empty($this->context['preview_content'])
			? prepare_content($this->context['lp_block']['type'])
			: parse_content($this->context['preview_content'], $this->context['lp_block']['type']);

		$this->context['page_title']    = $this->txt['preview'] . ($this->context['preview_title'] ? ' - ' . $this->context['preview_title'] : '');
		$this->context['preview_title'] = $this->getPreviewTitle($this->getIcon($this->context['lp_block']['icon']));
	}

	private function getPriority(): int
	{
		if (empty($this->context['lp_block']['placement']))
			return 0;

		$request = $this->smcFunc['db_query']('', '
			SELECT MAX(priority) + 1
			FROM {db_prefix}lp_blocks
			WHERE placement = {string:placement}',
			[
				'placement' => $this->context['lp_block']['placement'],
			]
		);

		[$priority] = $this->smcFunc['db_fetch_row']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return (int) $priority;
	}

	private function prepareBlockList()
	{
		$plugins = array_merge($this->context['lp_enabled_plugins'], array_keys($this->getContentTypes()));

		$this->context['lp_loaded_addons'] = array_merge(
			$this->context['lp_loaded_addons'] ?? [],
			[
				'bbc' => [
					'icon' => 'fab fa-bimobject'
				],
				'html' => [
					'icon' => 'fab fa-html5'
				],
				'php' => [
					'icon' => 'fab fa-php'
				]
			]
		);

		$this->context['lp_all_blocks'] = [];
		foreach ($plugins as $addon) {
			$addon = $this->getSnakeName($addon);

			// We need blocks only
			if (! isset($this->txt['lp_' . $addon]['title']) || isset($this->context['lp_all_blocks'][$addon]))
				continue;

			$this->context['lp_all_blocks'][$addon] = [
				'type'  => $addon,
				'icon'  => $this->context['lp_loaded_addons'][$addon]['icon'],
				'title' => $this->txt['lp_' . $addon]['title'],
				'desc'  => $this->txt['lp_' . $addon]['block_desc'] ?? $this->txt['lp_' . $addon]['description']
			];
		}

		$titles = array_column($this->context['lp_all_blocks'], 'title');
		array_multisort($titles, SORT_ASC, $this->context['lp_all_blocks']);
	}
}
