<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Areas;

use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Traits\HasArea;
use Bugo\LightPortal\Enums\BlockAreaType;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Events\HasEvents;
use Bugo\LightPortal\Models\BlockFactory;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Repositories\BlockRepositoryInterface;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\TextareaField;
use Bugo\LightPortal\UI\Fields\TextField;
use Bugo\LightPortal\UI\Fields\UrlField;
use Bugo\LightPortal\UI\Partials\AreaSelect;
use Bugo\LightPortal\UI\Partials\ContentClassSelect;
use Bugo\LightPortal\UI\Partials\IconSelect;
use Bugo\LightPortal\UI\Partials\PermissionSelect;
use Bugo\LightPortal\UI\Partials\PlacementSelect;
use Bugo\LightPortal\UI\Partials\TitleClassSelect;
use Bugo\LightPortal\Utils\Content;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Language;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Validators\BlockValidator;
use WPLake\Typed\Typed;

use function Bugo\LightPortal\app;
use function template_show_areas_info;

use const LP_NAME;
use const LP_PAGE_PARAM;

if (! defined('SMF'))
	die('No direct access...');

final readonly class BlockArea
{
	use HasArea;
	use HasEvents;

	public function __construct(private BlockRepositoryInterface $repository) {}

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

	public function add(): void
	{
		Theme::loadTemplate('LightPortal/ManageBlocks');

		Utils::$context['sub_template'] = 'block_add';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_blocks_add_title'];

		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_blocks;sa=add';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_blocks_add_description'],
		];

		Lang::$txt['lp_blocks_add_instruction'] = sprintf(
			Lang::$txt['lp_blocks_add_instruction'], Config::$scripturl . '?action=admin;area=lp_plugins'
		);

		Utils::$context['lp_current_block']['placement'] = $this->request()->get('placement') ?: 'top';

		$this->prepareBlockList();

		$json = $this->request()->json();
		$type = $json['add_block'] ?? $this->request()->get('add_block') ?? '';

		if (empty($type) && empty($json['search']))
			return;

		Utils::$context['lp_current_block']['type'] = $type;
		Utils::$context['lp_current_block']['icon'] ??= Utils::$context['lp_loaded_addons'][$type]['icon'] ?? '';

		Language::prepareList();

		$this->validateData();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData();

		Utils::$context['sub_template'] = 'block_post';
	}

	public function edit(): void
	{
		$item = Typed::int($this->request()->get('block_id') ?: $this->request()->get('id'));

		Theme::loadTemplate('LightPortal/ManageBlocks');

		Utils::$context['sub_template'] = 'block_post';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_blocks_edit_title'];

		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_blocks;sa=edit;id=' . $item;

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_blocks_edit_description'],
		];

		Language::prepareList();

		Utils::$context['lp_current_block'] = $this->repository->getData($item);

		if (empty(Utils::$context['lp_current_block'])) {
			ErrorHandler::fatalLang('lp_block_not_found', false, status: 404);
		}

		if ($this->request()->has('remove')) {
			$this->repository->remove([$item]);

			$this->cache()->forget('active_blocks');

			$this->response()->redirect('action=admin;area=lp_blocks;sa=main');
		}

		$this->validateData();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData(Utils::$context['lp_current_block']['id']);
	}

	private function doActions(): void
	{
		if ($this->request()->hasNot('actions'))
			return;

		$data = $this->request()->json();

		match (true) {
			isset($data['clone_block']) => $this->makeCopy((int) $data['clone_block']),
			isset($data['delete_item']) => $this->repository->remove([(int) $data['delete_item']]),
			isset($data['toggle_item']) => $this->repository->toggleStatus([(int) $data['toggle_item']]),
			isset($data['update_priority']) => $this->repository->updatePriority($data['update_priority'], $data['update_placement']),
			default => null,
		};

		$this->cache()->flush();

		exit;
	}

	private function makeCopy(int $item): void
	{
		if ($item === 0)
			return;

		$this->request()->put('clone', true);

		$result = [
			'success' => false,
		];

		Utils::$context['lp_block'] = $this->repository->getData($item);

		$this->repository->setData();

		if (Utils::$context['lp_block']['id']) {
			$result = [
				'id'      => Utils::$context['lp_block']['id'],
				'success' => true,
			];
		}

		$this->cache()->forget('active_blocks');

		$this->response()->exit($result);
	}

	private function getDefaultOptions(): array
	{
		$baseParams = [
			'hide_header' => false,
		];

		if (in_array(Utils::$context['lp_current_block']['type'], array_keys(Utils::$context['lp_content_types']))) {
			$baseParams['content'] = true;
		}

		$params = [];

		$this->events()->dispatch(
			PortalHook::prepareBlockParams,
			[
				'params' => &$params,
				'type'   => Utils::$context['lp_current_block']['type'],
			]
		);

		return array_merge($baseParams, $params);
	}

	private function validateData(): void
	{
		$options = $this->getDefaultOptions();

		$this->post()->put('type', Utils::$context['lp_current_block']['type']);

		Utils::$context['lp_current_block']['options'] ??= $options;

		$validatedData = app(BlockValidator::class)->validate();

		$block = app(BlockFactory::class)->create(
			array_merge(Utils::$context['lp_current_block'], $validatedData)
		);

		Utils::$context['lp_block'] = $block->toArray();

		$missingKeys = array_diff_key($options, Utils::$context['lp_block']['options']);

		foreach (array_keys($missingKeys) as $key) {
			settype(Utils::$context['lp_block']['options'][$key], get_debug_type($options[$key]));
		}
	}

	private function prepareFormFields(): void
	{
		$this->prepareTitleFields('block', false);

		TextField::make('description', Lang::$txt['lp_block_note'])
			->setTab(Tab::CONTENT)
			->setAttribute('maxlength', 255)
			->setValue(Utils::$context['lp_block']['description']);

		if (isset(Utils::$context['lp_block']['options']['content'])) {
			if (Utils::$context['lp_block']['type'] !== 'bbc') {
				TextareaField::make('content', Lang::$txt['lp_content'])
					->setTab(Tab::CONTENT)
					->setValue($this->prepareContent(Utils::$context['lp_block']));
			} else {
				$this->createBbcEditor(Utils::$context['lp_block']['content']);
			}
		}

		CustomField::make('placement', Lang::$txt['lp_block_placement'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new PlacementSelect());

		CustomField::make('permissions', Lang::$txt['edit_permissions'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new PermissionSelect(), [
				'type' => 'block'
			]);

		CustomField::make('areas', Lang::$txt['lp_block_areas'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setDescription($this->getAreasInfo())
			->setValue(static fn() => new AreaSelect());

		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab(Tab::APPEARANCE)
			->setValue(static fn() => new IconSelect());

		CustomField::make('title_class', Lang::$txt['lp_block_title_class'])
			->setTab(Tab::APPEARANCE)
			->setValue(static fn() => new TitleClassSelect());

		if (Block::showContentClassField(Utils::$context['lp_block']['type'])) {
			CustomField::make('content_class', Lang::$txt['lp_block_content_class'])
				->setTab(Tab::APPEARANCE)
				->setValue(static fn() => new ContentClassSelect());
		}

		CheckboxField::make('hide_header', Lang::$txt['lp_block_hide_header'])
			->setValue(Utils::$context['lp_block']['options']['hide_header']);

		if (isset(Utils::$context['lp_block']['options']['link_in_title'])) {
			UrlField::make('link_in_title', Lang::$txt['lp_block_link_in_title'])
				->setValue(Utils::$context['lp_block']['options']['link_in_title']);
		}

		Utils::$context['lp_block_tab_appearance'] = true;

		$this->events()->dispatch(
			PortalHook::prepareBlockFields,
			[
				'options' => Utils::$context['lp_block']['options'],
				'type'    => Utils::$context['lp_current_block']['type'],
			]
		);

		$this->preparePostFields();
	}

	private function getAreasInfo(): string
	{
		$exampleAreas = [
			'custom_action',
			'!custom_action',
			LP_PAGE_PARAM . '=slug',
			'board=id',
			'board=1-3',
			'board=3|7',
			'topic=id',
			'topic=1-3',
			'topic=3|7',
		];

		Lang::$txt['lp_block_areas_values'][BlockAreaType::CUSTOM_ACTION->name()] = sprintf(
			Lang::$txt['lp_block_areas_values'][BlockAreaType::CUSTOM_ACTION->name()],
			'pm,agreement,search'
		);

		$descriptions = [];
		foreach (BlockAreaType::cases() as $type) {
			$descriptions[] = Lang::$txt['lp_block_areas_values'][$type->name()];
		}

		Utils::$context['lp_possible_areas'] = array_combine($exampleAreas, $descriptions);

		ob_start();

		template_show_areas_info();

		return ob_get_clean();
	}

	private function prepareEditor(): void
	{
		$this->events()->dispatch(PortalHook::prepareEditor, ['object' => Utils::$context['lp_block']]);
	}

	private function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		$this->cache()->flush();

		Security::checkSubmitOnce('free');

		Utils::$context['preview_title']   = Utils::$context['lp_block']['title'] ?? '';
		Utils::$context['preview_content'] = Utils::htmlspecialchars(Utils::$context['lp_block']['content'], ENT_QUOTES);

		Str::cleanBbcode(Utils::$context['preview_title']);

		Lang::censorText(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_content']);

		Utils::$context['preview_content'] = empty(Utils::$context['preview_content'])
			? Content::prepare(
				Utils::$context['lp_block']['type'],
				Utils::$context['lp_block']['id'],
				0,
				Utils::$context['lp_block']['options'] ?? []
			)
			: Content::parse(Utils::$context['preview_content'], Utils::$context['lp_block']['type']);

		Utils::$context['page_title'] = Lang::$txt['preview'] . (
			Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : ''
		);

		Utils::$context['preview_title'] = $this->getPreviewTitle(Icon::parse(Utils::$context['lp_block']['icon']));

		if (! empty(Utils::$context['lp_block']['options']['hide_header'])) {
			Utils::$context['preview_title'] = Utils::$context['lp_block']['title_class'] = '';
		}
	}

	private function prepareBlockList(): void
	{
		$plugins = array_merge(Setting::getEnabledPlugins(), array_keys(ContentType::all()));

		Utils::$context['lp_loaded_addons'] = array_merge(
			Utils::$context['lp_loaded_addons'] ?? [], ContentType::default()
		);

		Utils::$context['lp_all_blocks'] = [];
		foreach ($plugins as $addon) {
			$addon = Str::getSnakeName($addon);

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
