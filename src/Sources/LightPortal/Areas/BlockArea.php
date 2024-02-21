<?php declare(strict_types=1);

/**
 * BlockArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas;

use Bugo\Compat\{Config, Database as Db, ErrorHandler, Lang, Security, Theme, Utils};
use Bugo\LightPortal\Areas\Fields\{CheckboxField, CustomField, TextareaField, TextField};
use Bugo\LightPortal\Areas\Partials\{AreaSelect, ContentClassSelect, IconSelect};
use Bugo\LightPortal\Areas\Partials\{PermissionSelect, PlacementSelect, TitleClassSelect};
use Bugo\LightPortal\Areas\Validators\BlockValidator;
use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Models\BlockModel;
use Bugo\LightPortal\Repositories\BlockRepository;
use Bugo\LightPortal\Utils\Content;

if (! defined('SMF'))
	die('No direct access...');

final class BlockArea
{
	use Area;
	use Helper;

	private BlockRepository $repository;

	public function __construct()
	{
		$this->repository = new BlockRepository;
	}

	public function main(): void
	{
		Theme::loadTemplate('LightPortal/ManageBlocks');

		Utils::$context['sub_template'] = 'manage_blocks';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_blocks_manage'];

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_blocks_manage_description'],
		];

		$this->doActions();

		Utils::$context['lp_current_blocks'] = $this->repository->getAll();
	}

	public function doActions(): void
	{
		if ($this->request()->hasNot('actions'))
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

	public function add(): void
	{
		Theme::loadTemplate('LightPortal/ManageBlocks');

		Utils::$context['sub_template'] = 'block_add';

		Utils::$context['page_title']    = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_blocks_add_title'];
		Utils::$context['canonical_url'] = Config::$scripturl . '?action=admin;area=lp_blocks;sa=add';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_blocks_add_description']
		];

		Lang::$txt['lp_blocks_add_instruction'] = sprintf(Lang::$txt['lp_blocks_add_instruction'], Config::$scripturl . '?action=admin;area=lp_plugins');

		Utils::$context['current_block']['placement'] = $this->request('placement', 'top');

		$this->prepareBlockList();

		$json = $this->request()->json();
		$type = $json['add_block'] ?? $this->request('add_block', '') ?? '';

		if (empty($type) && empty($json['search']))
			return;

		Utils::$context['current_block']['type'] = $type;

		$this->prepareForumLanguages();
		$this->validateData();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData();

		Utils::$context['sub_template'] = 'block_post';
	}

	public function edit(): void
	{
		$item = (int) ($this->request('block_id') ?: $this->request('id'));

		if ($item === 0) {
			ErrorHandler::fatalLang('lp_block_not_found', status: 404);
		}

		Theme::loadTemplate('LightPortal/ManageBlocks');

		Utils::$context['sub_template'] = 'block_post';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_blocks_edit_title'];

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_blocks_edit_description']
		];

		$this->prepareForumLanguages();

		Utils::$context['current_block'] = $this->repository->getData($item);

		if ($this->request()->has('remove')) {
			$this->remove([$item]);

			Utils::redirectexit('action=admin;area=lp_blocks;sa=main');
		}

		$this->validateData();

		Utils::$context['canonical_url'] = Config::$scripturl . '?action=admin;area=lp_blocks;sa=edit;id=' . Utils::$context['lp_block']['id'];

		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData(Utils::$context['lp_block']['id']);
	}

	private function remove(array $items): void
	{
		if ($items === [])
			return;

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_blocks
			WHERE block_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_titles
			WHERE item_id IN ({array_int:items})
				AND type = {literal:block}',
			[
				'items' => $items,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_params
			WHERE item_id IN ({array_int:items})
				AND type = {literal:block}',
			[
				'items' => $items,
			]
		);

		Utils::$context['lp_num_queries'] += 3;

		$this->hook('onBlockRemoving', [$items]);
	}

	private function makeCopy(int $item): void
	{
		if ($item === 0)
			return;

		$this->request()->put('clone', true);

		$result = [
			'success' => false,
		];

		Utils::$context['lp_block']       = $this->repository->getData($item);
		Utils::$context['lp_block']['id'] = $this->repository->setData();

		if (Utils::$context['lp_block']['id']) {
			$result = [
				'id'      => Utils::$context['lp_block']['id'],
				'success' => true,
			];
		}

		$this->cache()->forget('active_blocks');

		exit(json_encode($result));
	}

	private function updatePriority(): void
	{
		$data = $this->request()->json();

		if (empty($data['update_priority']))
			return;

		$blocks = $data['update_priority'];

		$conditions = '';
		foreach ($blocks as $priority => $item) {
			$conditions .= ' WHEN block_id = ' . $item . ' THEN ' . $priority;
		}

		if ($conditions === '')
			return;

		if (is_array($blocks)) {
			Db::$db->query('', /** @lang text */ '
				UPDATE {db_prefix}lp_blocks
				SET priority = CASE ' . $conditions . ' ELSE priority END
				WHERE block_id IN ({array_int:blocks})',
				[
					'blocks' => $blocks,
				]
			);

			Utils::$context['lp_num_queries']++;

			if ($data['update_placement']) {
				Db::$db->query('', '
					UPDATE {db_prefix}lp_blocks
					SET placement = {string:placement}
					WHERE block_id IN ({array_int:blocks})',
					[
						'placement' => $data['update_placement'],
						'blocks'    => $blocks,
					]
				);

				Utils::$context['lp_num_queries']++;
			}
		}
	}

	private function getParams(): array
	{
		$baseParams = [
			'hide_header'      => false,
			'no_content_class' => false,
		];

		if (in_array(Utils::$context['current_block']['type'], array_keys(Utils::$context['lp_content_types']))) {
			$baseParams['content'] = true;
		}

		$params = [];

		$this->hook('prepareBlockParams', [&$params]);

		return array_merge($baseParams, $params);
	}

	private function validateData(): void
	{
		[$postData, $parameters] = (new BlockValidator())->validate();

		$options = $this->getParams();

		$blockOptions = Utils::$context['current_block']['options'] ?? $options;

		$type = Utils::$context['current_block']['type'];
		Utils::$context['current_block']['icon'] ??= Utils::$context['lp_loaded_addons'][$type]['icon'] ?? '';

		$block = new BlockModel($postData, Utils::$context['current_block']);
		$block->titles = Utils::$context['current_block']['titles'] ?? [];
		$block->options = $options;
		$block->icon = $block->icon === 'undefined' ? '' : $block->icon;
		$block->priority = $block->id === 0 ? $this->getPriority() : $block->priority;
		$block->permissions = empty(Utils::$context['user']['is_admin']) ? 4 : $block->permissions;
		$block->contentClass = empty($block->options['no_content_class']) ? $block->contentClass : '';

		foreach ($block->options as $option => $value) {
			if (
				isset($parameters[$option])
				&& isset($postData['parameters'])
				&& ! isset($postData['parameters'][$option])
			) {
				$postData['parameters'][$option] = 0;

				if ($option === 'no_content_class')
					$postData['parameters'][$option] = $value;

				if ($parameters[$option] === FILTER_DEFAULT)
					$postData['parameters'][$option] = '';

				if (is_array($parameters[$option]) && $parameters[$option]['flags'] === FILTER_REQUIRE_ARRAY)
					$postData['parameters'][$option] = [];
			}

			$block->options[$option] = $postData['parameters'][$option] ?? $blockOptions[$option] ?? $value;
		}

		foreach (Utils::$context['lp_languages'] as $lang) {
			$block->titles[$lang['filename']] = $postData['title_' . $lang['filename']]
				?? $block->titles[$lang['filename']]
				?? '';
		}

		$this->cleanBbcode($block->titles);

		Utils::$context['lp_block'] = $block->toArray();
	}

	private function prepareFormFields(): void
	{
		$this->prepareTitleFields('block', false);

		TextField::make('note', Lang::$txt['lp_block_note'])
			->setTab('content')
			->setAttribute('maxlength', 255)
			->setValue(Utils::$context['lp_block']['note']);

		if (isset(Utils::$context['lp_block']['options']['content'])) {
			if (Utils::$context['lp_block']['type'] !== 'bbc') {
				TextareaField::make('content', Lang::$txt['lp_content'])
					->setTab('content')
					->setValue($this->prepareContent(Utils::$context['lp_block']));
			} else {
				$this->createBbcEditor(Utils::$context['lp_block']['content']);
			}
		}

		CustomField::make('placement', Lang::$txt['lp_block_placement'])
			->setTab('access_placement')
			->setValue(static fn() => new PlacementSelect());

		CustomField::make('permissions', Lang::$txt['edit_permissions'])
			->setTab('access_placement')
			->setValue(static fn() => new PermissionSelect(), [
				'type' => 'block'
			]);

		CustomField::make('areas', Lang::$txt['lp_block_areas'])
			->setTab('access_placement')
			->setAfter($this->getAreasInfo())
			->setValue(static fn() => new AreaSelect());

		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab('appearance')
			->setValue(static fn() => new IconSelect());

		CustomField::make('title_class', Lang::$txt['lp_block_title_class'])
			->setTab('appearance')
			->setValue(static fn() => new TitleClassSelect());

		if (empty(Utils::$context['lp_block']['options']['no_content_class'])) {
			CustomField::make('content_class', Lang::$txt['lp_block_content_class'])
				->setTab('appearance')
				->setValue(static fn() => new ContentClassSelect());
		}

		CheckboxField::make('hide_header', Lang::$txt['lp_block_hide_header'])
			->setValue(Utils::$context['lp_block']['options']['hide_header']);

		Utils::$context['lp_block_tab_appearance'] = true;

		$this->hook('prepareBlockFields');

		$this->preparePostFields();
	}

	private function getAreasInfo(): string
	{
		$example_areas = [
			'custom_action',
			'!custom_action',
			LP_PAGE_PARAM . '=alias',
			'board=id',
			'board=1-3',
			'board=3|7',
			'topic=id',
			'topic=1-3',
			'topic=3|7',
		];

		Lang::$txt['lp_block_areas_values'][0] = sprintf(Lang::$txt['lp_block_areas_values'][0], 'pm,agreement,search');

		Utils::$context['lp_possible_areas'] = array_combine($example_areas, Lang::$txt['lp_block_areas_values']);

		ob_start();

		template_show_areas_info();

		return ob_get_clean();
	}

	private function prepareEditor(): void
	{
		$this->hook('prepareEditor', [Utils::$context['lp_block']]);
	}

	private function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		$this->cache()->flush();

		Security::checkSubmitOnce('free');

		Utils::$context['preview_title']   = Utils::$context['lp_block']['titles'][Utils::$context['user']['language']] ?? '';
		Utils::$context['preview_content'] = Utils::$smcFunc['htmlspecialchars'](Utils::$context['lp_block']['content'], ENT_QUOTES);

		$this->cleanBbcode(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_content']);

		Utils::$context['preview_content'] = empty(Utils::$context['preview_content'])
			? Content::prepare(Utils::$context['lp_block']['type'], Utils::$context['lp_block']['id'], 0, Utils::$context['lp_block']['options'] ?? [])
			: Content::parse(Utils::$context['preview_content'], Utils::$context['lp_block']['type']);

		Utils::$context['page_title']    = Lang::$txt['preview'] . (Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : '');
		Utils::$context['preview_title'] = $this->getPreviewTitle($this->getIcon(Utils::$context['lp_block']['icon']));

		if (! empty(Utils::$context['lp_block']['options']['hide_header'])) {
			Utils::$context['preview_title'] = Utils::$context['lp_block']['title_class'] = '';
		}
	}

	private function getPriority(): int
	{
		if (empty(Utils::$context['lp_block']['placement']))
			return 0;

		$result = Db::$db->query('', '
			SELECT MAX(priority) + 1
			FROM {db_prefix}lp_blocks
			WHERE placement = {string:placement}',
			[
				'placement' => Utils::$context['lp_block']['placement'],
			]
		);

		[$priority] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return (int) $priority;
	}

	private function prepareBlockList(): void
	{
		$plugins = array_merge(Utils::$context['lp_enabled_plugins'], array_keys($this->getContentTypes()));

		Utils::$context['lp_loaded_addons'] = array_merge(Utils::$context['lp_loaded_addons'] ?? [], $this->getDefaultTypes());

		Utils::$context['lp_all_blocks'] = [];
		foreach ($plugins as $addon) {
			$addon = $this->getSnakeName($addon);

			// We need blocks only
			if (! isset(Lang::$txt['lp_' . $addon]['title']) || isset(Utils::$context['lp_all_blocks'][$addon]))
				continue;

			Utils::$context['lp_all_blocks'][$addon] = [
				'type'  => $addon,
				'icon'  => Utils::$context['lp_loaded_addons'][$addon]['icon'],
				'title' => Lang::$txt['lp_' . $addon]['title'],
				'desc'  => Lang::$txt['lp_' . $addon]['block_desc'] ?? Lang::$txt['lp_' . $addon]['description']
			];
		}

		$titles = array_column(Utils::$context['lp_all_blocks'], 'title');
		array_multisort($titles, SORT_ASC, Utils::$context['lp_all_blocks']);
	}
}
