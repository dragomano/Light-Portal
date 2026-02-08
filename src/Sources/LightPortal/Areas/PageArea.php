<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Areas;

use Bugo\Bricks\Tables\DateColumn;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Logging;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Areas\Traits\HasPageBrowseTypes;
use LightPortal\Areas\Traits\HasPageFilters;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\PortalHook;
use LightPortal\Enums\Tab;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Models\PageFactory;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\UI\TemplateLoader;
use LightPortal\UI\Fields\CheckboxField;
use LightPortal\UI\Fields\CustomField;
use LightPortal\UI\Fields\TextareaField;
use LightPortal\UI\Fields\TextField;
use LightPortal\UI\Partials\SelectFactory;
use LightPortal\UI\Tables\CheckboxColumn;
use LightPortal\UI\Tables\NumViewsColumn;
use LightPortal\UI\Tables\PageButtonsRow;
use LightPortal\UI\Tables\PageContextMenuColumn;
use LightPortal\UI\Tables\PageSearchRow;
use LightPortal\UI\Tables\PageSlugColumn;
use LightPortal\UI\Tables\PageStatusColumn;
use LightPortal\UI\Tables\PageTypeSelectRow;
use LightPortal\UI\Tables\PortalTableBuilder;
use LightPortal\UI\Tables\PortalTableBuilderInterface;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Validators\PageValidator;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class PageArea extends AbstractArea
{
	use HasPageBrowseTypes;
	use HasPageFilters;

	public function __construct(PageRepositoryInterface $repository, EventDispatcherInterface $dispatcher)
	{
		parent::__construct($repository, $dispatcher);
	}

	protected function checkPermissions(): void
	{
		User::$me->isAllowedTo(['light_portal_manage_pages_own', 'light_portal_manage_pages_any']);
	}

	protected function getEntityName(): string
	{
		return 'page';
	}

	protected function getEntityNamePlural(): string
	{
		return 'pages';
	}

	protected function getCustomActionHandlers(): array
	{
		return [
			'restore_item'   => fn($data) => $this->getRepository()->restore($data['restore_item']),
			'remove_forever' => fn($data) => $this->getRepository()->removePermanently($data['remove_forever']),
		];
	}

	protected function getValidatorClass(): string
	{
		return PageValidator::class;
	}

	protected function getFactoryClass(): string
	{
		return PageFactory::class;
	}

	protected function shouldFlushCache(): bool
	{
		return true;
	}

	protected function beforeMain(): void
	{
		$this->loadParamsFromRequest();
		$this->checkUser();

		Lang::load('Packages');

		if ($this->isModerate) {
			Theme::addInlineCss('
        #lp_pages .num_views, #lp_pages .num_comments {
            display: none;
        }');
		}
	}

	protected function getMainFormActionSuffix(): string
	{
		return ';sa=main';
	}

	protected function getMainTabData(): array
	{
		$key = User::$me->allowedTo('light_portal_manage_pages_any') && ! $this->userId ? 'all' : 'own';

		$description = implode('', [
			Lang::$txt['lp_pages_manage_' . $key . '_pages'] . ' ',
			Lang::$txt['lp_pages_manage_description'],
		]);

		if ($this->isModerate) {
			$description = Lang::$txt['lp_pages_unapproved_description'];
		}

		if ($this->isDeleted) {
			$description = Lang::$txt['lp_pages_deleted_description'];
		}

		return [
			'title'       => LP_NAME,
			'description' => $description,
		];
	}

	protected function performMassActions(): void
	{
		if ($this->request()->hasNot('mass_actions') || $this->request()->isEmpty('items'))
			return;

		$redirect = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_DEFAULT, [
			'options' => ['default' => 'action=admin;area=lp_pages']
		]);

		$items = $this->request()->get('items') ?? [];

		switch (filter_input(INPUT_POST, 'page_actions')) {
			case 'delete':
				$this->repository->remove($items);
				break;

			case 'delete_forever':
				$this->getRepository()->removePermanently($items);
				break;

			case 'toggle':
				$this->repository->toggleStatus($items);
				break;

			case 'promote_up':
				$this->promote($items);
				break;

			case 'promote_down':
				$this->promote($items, 'down');
				break;
		}

		$this->cache()->flush();

		$this->response()->redirect($redirect);
	}

	protected function buildTable(): PortalTableBuilderInterface
	{
		$this->calculateParams();
		$this->calculateTypes();

		Utils::$context['lp_selected_page_type'] = $this->entryType;

		$params = empty(Utils::$context['search_params']) ? '' : ';params=' . Utils::$context['search_params'];
		$action = Utils::$context['form_action'] . $this->type . $params;

		$builder = PortalTableBuilder::make('lp_pages', Lang::$txt['lp_pages_extra'])
			->setScript('const entity = new Page();')
			->withCreateButton($this->getEntityNamePlural())
			->withParams(
				action: $action,
				defaultSortColumn: 'date'
			)
			->setItems($this->repository->getAll(...), $this->params)
			->setCount($this->repository->getTotalCount(...), $this->params)
			->addColumns([
				IdColumn::make()->setSort('p.page_id'),
				DateColumn::make(title: Lang::$txt['date']),
				NumViewsColumn::make(),
				PageSlugColumn::make(),
				TitleColumn::make()->setData(
					fn($entry) => Str::html('i', [
						'class' => $this->getPageIcon($entry['type']),
						'title' => Utils::$context['lp_content_types'][$entry['type']]
							?? strtoupper((string) $entry['type']),
					]) . ' ' . Str::html('a', [
						'class' => 'bbc_link' . ($entry['is_front'] ? ' _highlight' : ''),
						'href'  => $entry['is_front'] ? Config::$scripturl : (LP_PAGE_URL . $entry['slug']),
					])->setText($entry['title']),
				),
				PageStatusColumn::make(status: $this->status),
				PageContextMenuColumn::make()
			])
			->addRows([
				PageSearchRow::make(),
				PageTypeSelectRow::make(),
			])
			->addFormData([
				'name' => 'manage_pages',
				'href' => Utils::$context['form_action'] . $this->type,
				'include_sort' => true,
				'hidden_fields' => [
					Utils::$context['session_var'] => Utils::$context['session_id'],
					'params' => Utils::$context['search_params'],
				],
			]);

		Utils::$context['user']['is_admin'] && $builder
			->addColumn(CheckboxColumn::make(name: 'mass', entity: 'items'))
			->addRow(PageButtonsRow::make());

		return $builder;
	}

	protected function afterMain(): void
	{
		$this->changeTableTitle();
	}

	protected function setupAdditionalAddContext(): void
	{
		$this->preparePageList();

		TemplateLoader::fromFile('admin/page_add');

		$json = $this->request()->json();
		$type = $json['add_page'] ?? $this->request()->get('add_page') ?? '';

		$this->shouldProcessAddForm = ! (empty($type) && empty($json['search']));

		if (! $this->shouldProcessAddForm)
			return;

		Utils::$context['lp_current_page']['type'] = $type;
	}

	protected function prepareValidationContext(): void
	{
		$options = $this->getDefaultOptions();

		$this->post()->put('type', Utils::$context['lp_current_page']['type']);

		Utils::$context['lp_current_page']['options'] ??= $options;
	}

	protected function postProcessValidation(): void
	{
		$options = $this->getDefaultOptions();

		$missingKeys = array_diff_key($options, Utils::$context['lp_page']['options']);

		foreach (array_keys($missingKeys) as $key) {
			settype(Utils::$context['lp_page']['options'][$key], get_debug_type($options[$key]));
		}
	}

	protected function prepareCommonFields(): void {}

	protected function prepareSpecificFields(): void
	{
		if (Utils::$context['lp_page']['type'] !== ContentType::BBC->name()) {
			TextareaField::make('content', Lang::$txt['lp_content'])
				->setTab(Tab::CONTENT)
				->setAttribute('style', 'height: 300px')
				->setValue($this->prepareContent(Utils::$context['lp_page']));
		} else {
			$this->createBbcEditor(Utils::$context['lp_page']['content']);
		}

		if (Utils::$context['user']['is_admin']) {
			CustomField::make('show_in_menu', Lang::$txt['lp_page_show_in_menu'])
				->setTab(Tab::ACCESS_PLACEMENT)
				->setValue(SelectFactory::pageIcon(...));

			CustomField::make('status', Lang::$txt['status'])
				->setTab(Tab::ACCESS_PLACEMENT)
				->setValue(SelectFactory::status(...));
		}

		CustomField::make('permissions', Lang::$txt['edit_permissions'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(SelectFactory::permission(...));

		CustomField::make('category_id', Lang::$txt['lp_category'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => SelectFactory::category([
				'id'       => 'category_id',
				'multiple' => false,
				'wide'     => false,
				'value'    => Utils::$context['lp_page']['category_id'],
			]));

		CustomField::make('entry_type', Lang::$txt['lp_page_type'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(SelectFactory::entryType(...));

		$this->prepareSlugField();

		TextareaField::make('description', Lang::$txt['lp_page_description'])
			->setTab(Tab::SEO)
			->setAttribute('maxlength', 255)
			->setValue(Utils::$context['lp_page']['description']);

		if (empty(Utils::$context['lp_quantities']['active_tags'])) {
			TextField::make('tags', '')
				->setAttribute('hidden', true)
				->setValue('');
		} else {
			CustomField::make('tags', Lang::$txt['lp_tags'])
				->setTab(Tab::SEO)
				->setValue(SelectFactory::tag(...));
		}

		if (Utils::$context['lp_page']['created_at'] >= time()) {
			CustomField::make('datetime', Lang::$txt['lp_page_publish_datetime'])
				->setValue(Str::html('input', [
						'type'  => 'date',
						'id'    => 'datetime',
						'name'  => 'date',
						'min'   => date('Y-m-d'),
						'value' => Utils::$context['lp_page']['date'],
					]) . ' ' . Str::html('input', [
						'type'  => 'time',
						'name'  => 'time',
						'value' => Utils::$context['lp_page']['time'],
					])
				);
		}

		CheckboxField::make('show_title', Lang::$txt['lp_page_show_title'])
			->setValue(Utils::$context['lp_page']['options']['show_title']);

		CheckboxField::make('show_author_and_date', Lang::$txt['lp_page_show_author_and_date'])
			->setValue(Utils::$context['lp_page']['options']['show_author_and_date']);

		if (Setting::showRelatedPages()) {
			CheckboxField::make('show_related_pages', Lang::$txt['lp_page_show_related_pages'])
				->setValue(Utils::$context['lp_page']['options']['show_related_pages']);
		}

		if (Setting::getCommentBlock() !== '' && Setting::getCommentBlock() !== 'none') {
			CheckboxField::make('allow_comments', Lang::$txt['lp_page_allow_comments'])
				->setValue(Utils::$context['lp_page']['options']['allow_comments']);
		}
	}

	protected function dispatchFieldsEvent(): void
	{
		$this->dispatcher->dispatch(
			PortalHook::preparePageFields,
			[
				'options' => Utils::$context['lp_page']['options'],
				'type'    => Utils::$context['lp_page']['type'],
			]
		);
	}

	protected function prepareEditor(): void
	{
		$this->dispatcher->dispatch(PortalHook::prepareEditor, ['object' => Utils::$context['lp_page']]);
	}

	protected function finalizePreviewTitle(array $entity): void
	{
		Utils::$context['preview_title'] = $this->getPreviewTitle();
	}

	protected function beforeRemove(int $item): void
	{
		if (Utils::$context['lp_current_page']['author_id'] !== User::$me->id) {
			Logging::logAction('remove_lp_page', [
				'page' => Utils::$context['lp_current_page']['title'],
			]);
		}
	}

	protected function postProcessLoadedData(array &$data): void
	{
		$this->getRepository()->prepareData($data);
	}

	protected function validateEntityPermissions(): void
	{
		if (Utils::$context['lp_current_page']['can_edit'] === false) {
			ErrorHandler::fatalLang('lp_page_not_editable', false);
		}
	}

	private function checkUser(): void
	{
		if (! User::$me->allowedTo('light_portal_manage_pages_any') && ! $this->userId) {
			$this->response()->redirect('action=admin;area=lp_pages;u=' . User::$me->id);
		}
	}

	private function promote(array $items, string $type = 'up'): void
	{
		if ($items === [])
			return;

		if ($type === 'down') {
			$items = array_diff(Setting::getFrontpagePages(), $items);
		} else {
			$items = array_merge(
				array_diff($items, Setting::getFrontpagePages()),
				Setting::getFrontpagePages()
			);
		}

		Config::updateModSettings(['lp_frontpage_pages' => implode(',', $items)]);
	}

	private function getDefaultOptions(): array
	{
		$baseParams = [
			'page_icon'            => '',
			'show_in_menu'         => false,
			'show_title'           => true,
			'show_author_and_date' => true,
			'show_related_pages'   => false,
			'allow_comments'       => false,
		];

		$params = [];

		$this->dispatcher->dispatch(
			PortalHook::preparePageParams,
			[
				'params' => &$params,
				'type'   => Utils::$context['lp_current_page']['type'],
			]
		);

		return array_merge($baseParams, $params);
	}

	private function preparePageList(): void
	{
		Utils::$context['lp_all_pages'] = [];
		foreach (Utils::$context['lp_content_types'] as $type => $title) {
			Utils::$context['lp_all_pages'][$type] = [
				'type'  => $type,
				'icon'  => ContentType::icon($type) ?: Utils::$context['lp_loaded_addons'][$type]['icon'],
				'title' => Lang::$txt['lp_' . $type]['title'] ?? $title,
				'desc'  => Lang::$txt['lp_' . $type]['block_desc'] ?? Lang::$txt['lp_' . $type]['description'],
			];
		}

		$titles = array_column(Utils::$context['lp_all_pages'], 'title');
		array_multisort($titles, SORT_ASC, Utils::$context['lp_all_pages']);
	}

	private function getPageIcon(string $type): string
	{
		return ContentType::icon($type)
			?: Utils::$context['lp_loaded_addons'][$type]['icon']
			?? 'fas fa-question';
	}

	private function getRepository(): PageRepositoryInterface
	{
		$repository = $this->repository;

		assert($repository instanceof PageRepositoryInterface);

		return $repository;
	}
}
