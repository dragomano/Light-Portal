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

namespace LightPortal\Areas;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Enums\BlockAreaType;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\PortalHook;
use LightPortal\Enums\Tab;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Models\BlockFactory;
use LightPortal\Plugins\Block;
use LightPortal\Repositories\BlockRepositoryInterface;
use LightPortal\UI\TemplateLoader;
use LightPortal\UI\Fields\CheckboxField;
use LightPortal\UI\Fields\CustomField;
use LightPortal\UI\Fields\TextareaField;
use LightPortal\UI\Fields\TextField;
use LightPortal\UI\Fields\UrlField;
use LightPortal\UI\Partials\SelectFactory;
use LightPortal\Utils\Content;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Validators\BlockValidator;

use const LP_PAGE_PARAM;

if (! defined('SMF'))
	die('No direct access...');

final class BlockArea extends AbstractArea
{
	public function __construct(BlockRepositoryInterface $repository, EventDispatcherInterface $dispatcher)
	{
		parent::__construct($repository, $dispatcher);
	}

	protected function getEntityName(): string
	{
		return 'block';
	}

	protected function getEntityNamePlural(): string
	{
		return 'blocks';
	}

	protected function getCustomActionHandlers(): array
	{
		return [
			'clone_block'     => fn($data) => $this->handleClone($data['clone_block']),
			'update_priority' => fn($data) => $this->getRepository()
				->updatePriority($data['update_priority'], $data['update_placement']),
		];
	}

	protected function getValidatorClass(): string
	{
		return BlockValidator::class;
	}

	protected function getFactoryClass(): string
	{
		return BlockFactory::class;
	}

	protected function getMainFormActionSuffix(): string
	{
		return ';sa=add';
	}

	protected function getRemoveRedirectSuffix(): string
	{
		return ';sa=main';
	}

	protected function shouldFlushCache(): bool
	{
		return true;
	}

	protected function shouldRequireTitleFields(): bool
	{
		return false;
	}

	protected function showMainContent(): void
	{
		Utils::$context['lp_current_blocks'] = $this->repository->getAll(0, 0, 'placement DESC, priority');

		TemplateLoader::fromFile('admin/block_index');
	}

	protected function setupAdditionalAddContext(): void
	{
		Lang::$txt['lp_blocks_add_instruction'] = sprintf(
			Lang::$txt['lp_blocks_add_instruction'],
			Config::$scripturl . '?action=admin;area=lp_plugins'
		);

		Utils::$context['lp_current_block']['placement'] = $this->request()->get('placement') ?: 'top';

		$this->prepareBlockList();

		TemplateLoader::fromFile('admin/block_add');

		$json = $this->request()->json();
		$type = $json['add_block'] ?? $this->request()->get('add_block') ?? '';

		$this->shouldProcessAddForm = ! (empty($type) && empty($json['search']));

		if (! $this->shouldProcessAddForm)
			return;

		Utils::$context['lp_current_block']['type'] = $type;
		Utils::$context['lp_current_block']['icon'] ??= Utils::$context['lp_loaded_addons'][$type]['icon'] ?? '';
	}

	protected function prepareValidationContext(): void
	{
		$options = $this->getDefaultOptions();

		$this->post()->put('type', Utils::$context['lp_current_block']['type']);

		Utils::$context['lp_current_block']['options'] ??= $options;
	}

	protected function postProcessValidation(): void
	{
		$options = $this->getDefaultOptions();

		$missingKeys = array_diff_key($options, Utils::$context['lp_block']['options']);

		foreach (array_keys($missingKeys) as $key) {
			settype(Utils::$context['lp_block']['options'][$key], get_debug_type($options[$key]));
		}
	}

	protected function prepareCommonFields(): void {}

	protected function prepareSpecificFields(): void
	{
		TextField::make('description', Lang::$txt['lp_block_note'])
			->setTab(Tab::CONTENT)
			->setAttribute('maxlength', 255)
			->setValue(Utils::$context['lp_block']['description'] ?? '');

		if (isset(Utils::$context['lp_block']['options']['content'])) {
			if (Utils::$context['lp_block']['type'] !== ContentType::BBC->name()) {
				TextareaField::make('content', Lang::$txt['lp_content'])
					->setTab(Tab::CONTENT)
					->setValue($this->prepareContent(Utils::$context['lp_block']));
			} else {
				$this->createBbcEditor(Utils::$context['lp_block']['content']);
			}
		}

		CustomField::make('placement', Lang::$txt['lp_block_placement'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(SelectFactory::placement(...));

		CustomField::make('permissions', Lang::$txt['edit_permissions'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => SelectFactory::permission(['type' => 'block']));

		CustomField::make('areas', Lang::$txt['lp_block_areas'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setDescription($this->getAreasInfo())
			->setValue(SelectFactory::area(...));

		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab(Tab::APPEARANCE)
			->setValue(SelectFactory::icon(...));

		CustomField::make('title_class', Lang::$txt['lp_block_title_class'])
			->setTab(Tab::APPEARANCE)
			->setValue(SelectFactory::titleClass(...));

		if (Block::showContentClassField(Utils::$context['lp_block']['type'])) {
			CustomField::make('content_class', Lang::$txt['lp_block_content_class'])
				->setTab(Tab::APPEARANCE)
				->setValue(SelectFactory::contentClass(...));
		}

		CheckboxField::make('hide_header', Lang::$txt['lp_block_hide_header'])
			->setValue(Utils::$context['lp_block']['options']['hide_header']);

		if (isset(Utils::$context['lp_block']['options']['link_in_title'])) {
			UrlField::make('link_in_title', Lang::$txt['lp_block_link_in_title'])
				->setValue(Utils::$context['lp_block']['options']['link_in_title']);
		}
	}

	protected function dispatchFieldsEvent(): void
	{
		$this->dispatcher->dispatch(
			PortalHook::prepareBlockFields,
			[
				'options' => Utils::$context['lp_block']['options'],
				'type'    => Utils::$context['lp_current_block']['type'],
			]
		);
	}

	protected function prepareEditor(): void
	{
		$this->dispatcher->dispatch(PortalHook::prepareEditor, ['object' => Utils::$context['lp_block']]);
	}

	protected function preparePreviewContent(array $entity): void
	{
		Utils::$context['preview_content'] = Utils::htmlspecialchars($entity['content'] ?? '', ENT_QUOTES);

		Lang::censorText(Utils::$context['preview_content']);

		Utils::$context['preview_content'] = empty(Utils::$context['preview_content'])
			? Content::prepare(
				$entity['type'],
				$entity['id'],
				0,
				$entity['options'] ?? []
			)
			: Content::parse(Utils::$context['preview_content'], $entity['type']);
	}

	protected function finalizePreviewTitle(array $entity): void
	{
		Utils::$context['preview_title'] = $this->getPreviewTitle(
			Icon::parse($entity['icon'] ?? '')
		);

		if (! empty($entity['options']['hide_header'])) {
			Utils::$context['preview_title'] = Utils::$context['lp_block']['title_class'] = '';
		}
	}

	private function handleClone(mixed $item): void
	{
		if (empty($item))
			return;

		$this->request()->put('clone', true);

		$result = ['success' => false];

		Utils::$context['lp_block'] = $this->repository->getData(intval($item));

		$this->repository->setData();

		if (Utils::$context['lp_block']['id']) {
			$result = [
				'id'      => Utils::$context['lp_block']['id'],
				'success' => true,
			];
		}

		$this->clearCache();

		$this->response()->exit($result);
	}

	private function getDefaultOptions(): array
	{
		$baseParams = ['hide_header' => false];

		if (in_array(Utils::$context['lp_current_block']['type'], array_keys(Utils::$context['lp_content_types']))) {
			$baseParams['content'] = true;
		}

		$params = [];

		$this->dispatcher->dispatch(
			PortalHook::prepareBlockParams,
			[
				'baseParams' => &$baseParams,
				'params'     => &$params,
				'type'       => Utils::$context['lp_current_block']['type'],
			]
		);

		return array_merge($baseParams, $params);
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

		return TemplateLoader::fromFile('admin/show_areas_info');
	}

	private function prepareBlockList(): void
	{
		$plugins = array_merge(Setting::getEnabledPlugins(), array_keys(ContentType::all()));

		Utils::$context['lp_loaded_addons'] = array_merge(
			Utils::$context['lp_loaded_addons'] ?? [],
			ContentType::default()
		);

		Utils::$context['lp_all_blocks'] = [];

		foreach ($plugins as $addon) {
			$addon = Str::getSnakeName($addon);

			if (! isset(Lang::$txt['lp_' . $addon]['title']) || isset(Utils::$context['lp_all_blocks'][$addon]))
				continue;

			Utils::$context['lp_all_blocks'][$addon] = [
				'type'  => $addon,
				'icon'  => Utils::$context['lp_loaded_addons'][$addon]['icon'],
				'title' => Lang::$txt['lp_' . $addon]['title'],
				'desc'  => Lang::$txt['lp_' . $addon]['block_desc'] ?? Lang::$txt['lp_' . $addon]['description'],
			];
		}

		$titles = array_column(Utils::$context['lp_all_blocks'], 'title');
		array_multisort($titles, SORT_ASC, Utils::$context['lp_all_blocks']);
	}

	private function getRepository(): BlockRepositoryInterface
	{
		$repository = $this->repository;

		assert($repository instanceof BlockRepositoryInterface);

		return $repository;
	}
}
