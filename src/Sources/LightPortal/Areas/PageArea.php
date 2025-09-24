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

use Bugo\Bricks\Tables\DateColumn;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Bricks\Tables\TablePresenter;
use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Logging;
use Bugo\Compat\Security;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Traits\HasArea;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\Models\PageFactory;
use Bugo\LightPortal\Repositories\PageRepositoryInterface;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\TextareaField;
use Bugo\LightPortal\UI\Fields\TextField;
use Bugo\LightPortal\UI\Partials\CategorySelect;
use Bugo\LightPortal\UI\Partials\EntryTypeSelect;
use Bugo\LightPortal\UI\Partials\PageIconSelect;
use Bugo\LightPortal\UI\Partials\PermissionSelect;
use Bugo\LightPortal\UI\Partials\StatusSelect;
use Bugo\LightPortal\UI\Partials\TagSelect;
use Bugo\LightPortal\UI\Tables\CheckboxColumn;
use Bugo\LightPortal\UI\Tables\NumViewsColumn;
use Bugo\LightPortal\UI\Tables\PageButtonsRow;
use Bugo\LightPortal\UI\Tables\PageContextMenuColumn;
use Bugo\LightPortal\UI\Tables\PageSearchRow;
use Bugo\LightPortal\UI\Tables\PageSlugColumn;
use Bugo\LightPortal\UI\Tables\PageStatusColumn;
use Bugo\LightPortal\UI\Tables\PageTypeSelectRow;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\Content;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\Language;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Validators\PageValidator;
use WPLake\Typed\Typed;

use function Bugo\LightPortal\app;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class PageArea
{
	use HasArea;

	private array $params = [];

	private string $browseType;

	private string $type;

	private int $status;

	public function __construct(private readonly PageRepositoryInterface $repository) {}

	public function main(): void
	{
		$this->checkUser();

		Lang::load('Packages');

		if ($this->request()->has('moderate'))
			Theme::addInlineCss('
		#lp_pages .num_views, #lp_pages .num_comments {
			display: none;
		}');

		Utils::$context['page_title']  = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_pages_manage'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_pages;sa=main';

		$menu = Utils::$context['admin_menu_name'];
		$key  = User::$me->allowedTo('light_portal_manage_pages_any') && $this->request()->hasNot('u') ? 'all' : 'own';
		$tabs = [
			'title'       => LP_NAME,
			'description' => implode('', [
				Lang::$txt['lp_pages_manage_' . $key . '_pages'] . ' ',
				Lang::$txt['lp_pages_manage_description'],
			]),
		];

		if ($this->request()->has('moderate')) {
			$tabs['description'] = Lang::$txt['lp_pages_unapproved_description'];
		}

		if ($this->request()->has('deleted')) {
			$tabs['description'] = Lang::$txt['lp_pages_deleted_description'];
		}

		Utils::$context[$menu]['tab_data'] = $tabs;

		$this->doActions();
		$this->massActions();
		$this->calculateParams();
		$this->calculateTypes();

		$builder = PortalTableBuilder::make('lp_pages', Lang::$txt['lp_pages_extra'])
			->setScript('const entity = new Page();')
			->withCreateButton('pages')
			->withParams(
				action: Utils::$context['form_action'] . $this->type . (
					empty(Utils::$context['search_params']) ? '' : ';params=' . Utils::$context['search_params']
				),
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
						'title' => Utils::$context['lp_content_types'][$entry['type']] ?? strtoupper((string) $entry['type']),
					]) . ' ' . Str::html('a', [
						'class' => 'bbc_link' . ($entry['is_front'] ? ' _highlight' : ''),
						'href'  => $entry['is_front'] ? Config::$scripturl : (LP_PAGE_URL . $entry['slug']),
					])->setText($entry['title'])
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

		app(TablePresenter::class)->show($builder);

		$this->changeTableTitle();
	}

	public function add(): void
	{
		Theme::loadTemplate('LightPortal/ManagePages');

		Utils::$context['sub_template'] = 'page_add';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_pages_add_title'];

		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_pages;sa=add';

		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_add_title'];

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_pages_add_description'],
		];

		$this->preparePageList();

		$json = $this->request()->json();
		$type = $json['add_page'] ?? $this->request()->get('add_page') ?? '';

		if (empty($type) && empty($json['search']))
			return;

		Utils::$context['lp_current_page']['type'] = $type;

		Language::prepareList();

		$this->validateData();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData();

		Utils::$context['sub_template'] = 'page_post';
	}

	public function edit(): void
	{
		$item = Typed::int($this->request()->get('page_id') ?: $this->request()->get('id'));

		Theme::loadTemplate('LightPortal/ManagePages');

		Utils::$context['sub_template'] = 'page_post';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_pages_edit_title'];

		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . $item;

		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_edit_title'];

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_pages_edit_description'],
		];

		$data = $this->repository->getData($item);

		$this->repository->prepareData($data);

		Utils::$context['lp_current_page'] = $data;

		if (empty(Utils::$context['lp_current_page'])) {
			ErrorHandler::fatalLang('lp_page_not_found', false, status: 404);
		}

		if (Utils::$context['lp_current_page']['can_edit'] === false) {
			ErrorHandler::fatalLang('lp_page_not_editable', false);
		}

		Language::prepareList();

		if ($this->request()->has('remove')) {
			if (Utils::$context['lp_current_page']['author_id'] !== User::$me->id) {
				Logging::logAction('remove_lp_page', [
					'page' => Utils::$context['lp_current_page']['titles'][User::$me->language]
				]);
			}

			$this->repository->remove([$item]);

			$this->cache()->flush();

			$this->response()->redirect('action=admin;area=lp_pages');
		}

		$this->validateData();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData(Utils::$context['lp_page']['id']);
	}

	private function doActions(): void
	{
		if ($this->request()->hasNot('actions'))
			return;

		$data = $this->request()->json();

		match (true) {
			isset($data['delete_item']) => $this->repository->remove([(int) $data['delete_item']]),
			isset($data['toggle_item']) => $this->repository->toggleStatus([(int) $data['toggle_item']]),
			isset($data['restore_item']) => $this->repository->restore([(int) $data['restore_item']]),
			isset($data['remove_forever']) => $this->repository->removePermanently([(int) $data['remove_forever']]),
			default => null,
		};

		$this->cache()->flush();

		exit;
	}

	private function massActions(): void
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
				$this->repository->removePermanently($items);
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

	private function calculateParams(): void
	{
		$searchParamString = trim((string) $this->request()->get('search'));
		$searchParams = [
			'string' => Utils::htmlspecialchars($searchParamString),
		];

		Utils::$context['search_params'] = empty($searchParamString)
			? '' : base64_encode((string) Utils::$smcFunc['json_encode']($searchParams));

		Utils::$context['search'] = [
			'string' => $searchParams['string'],
		];

		$this->params = [
			(
				empty($searchParams['string'])
				? ''
				: ' AND (INSTR(LOWER(p.slug), {string:search}) > 0 OR INSTR(LOWER(t.title), {string:search}) > 0)'
			) . (
				$this->request()->has('u') ?
				' AND p.author_id = {int:user_id} AND p.deleted_at = 0'
				: ''
			) . (
				$this->request()->has('moderate')
				? ' AND p.status = {int:unapproved} AND p.deleted_at = 0'
				: ''
			) . (
				$this->request()->has('deleted')
				? ' AND p.deleted_at <> 0'
				: ''
			) . (
				$this->request()->hasNot(['u', 'moderate', 'deleted'])
				? ' AND p.status IN ({array_int:statuses}) AND p.deleted_at = 0'
				: ''
			) . ' AND p.entry_type = {string:entry_type}',
			[
				'search'     => Utils::$smcFunc['strtolower']($searchParams['string']),
				'unapproved' => Status::UNAPPROVED->value,
				'statuses'   => [Status::INACTIVE->value, Status::ACTIVE->value],
				'entry_type' => $this->request()->get('type') ?? EntryType::DEFAULT->name(),
			],
		];
	}

	private function calculateTypes(): void
	{
		$this->browseType = 'all';
		$this->type = '';
		$this->status = Status::ACTIVE->value;

		if ($this->request()->has('u')) {
			$this->browseType = 'own';
			$this->type = ';u=' . User::$me->id;
		} elseif ($this->request()->has('moderate')) {
			$this->browseType = 'mod';
			$this->type = ';moderate';
		} elseif ($this->request()->has('deleted')) {
			$this->browseType = 'del';
			$this->type = ';deleted';
		}
	}

	private function changeTableTitle(): void
	{
		$titles = [
			'all' => [
				'',
				Lang::$txt['all'],
				Utils::$context['lp_quantities']['active_pages']
			],
			'own' => [
				';u=' . User::$me->id,
				Lang::$txt['lp_my_pages'],
				Utils::$context['lp_quantities']['my_pages']
			],
			'mod' => [
				';moderate',
				Lang::$txt['awaiting_approval'],
				Utils::$context['lp_quantities']['unapproved_pages']
			],
			'del' => [
				';deleted',
				Lang::$txt['lp_pages_deleted'],
				Utils::$context['lp_quantities']['deleted_pages']
			]
		];

		if (! User::$me->allowedTo('light_portal_manage_pages_any')) {
			unset($titles['all'], $titles['mod'], $titles['del']);
		}

		Utils::$context['lp_pages']['title'] .= ': ';
		foreach ($titles as $browseType => $details) {
			if ($this->browseType === $browseType) {
				Utils::$context['lp_pages']['title'] .= Str::html('img')
					->src(Theme::$current->settings['images_url'] . '/selected.png')
					->alt('&gt;');
			}

			Utils::$context['lp_pages']['title'] .= Str::html('a')
				->href(Utils::$context['form_action'] . $details[0])
				->setText($details[1] . ' (' . $details[2] . ')');

			if ($browseType !== 'del' && count($titles) > 1) {
				Utils::$context['lp_pages']['title'] .= ' | ';
			}
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

		$this->events()->dispatch(
			PortalHook::preparePageParams,
			[
				'params' => &$params,
				'type'   => Utils::$context['lp_current_page']['type'],
			]
		);

		return array_merge($baseParams, $params);
	}

	private function validateData(): void
	{
		$options = $this->getDefaultOptions();

		$this->post()->put('type', Utils::$context['lp_current_page']['type']);

		Utils::$context['lp_current_page']['options'] ??= $options;

		$validatedData = app(PageValidator::class)->validate();

		$page = app(PageFactory::class)->create(
			array_merge(Utils::$context['lp_current_page'], $validatedData)
		);

		$dateTime = DateTime::get();
		$page->date ??= $dateTime->format('Y-m-d');
		$page->time ??= $dateTime->format('H:i');

		Utils::$context['lp_page'] = $page->toArray();

		$missingKeys = array_diff_key($options, Utils::$context['lp_page']['options']);

		foreach (array_keys($missingKeys) as $key) {
			settype(Utils::$context['lp_page']['options'][$key], get_debug_type($options[$key]));
		}
	}

	private function prepareFormFields(): void
	{
		$this->prepareTitleFields();

		if (Utils::$context['lp_page']['type'] !== 'bbc') {
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
				->setValue(static fn() => new PageIconSelect());

			CustomField::make('status', Lang::$txt['status'])
				->setTab(Tab::ACCESS_PLACEMENT)
				->setValue(static fn() => new StatusSelect());
		}

		CustomField::make('permissions', Lang::$txt['edit_permissions'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new PermissionSelect());

		CustomField::make('category_id', Lang::$txt['lp_category'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new CategorySelect(), [
				'id'       => 'category_id',
				'multiple' => false,
				'wide'     => false,
				'data'     => app(CategoryList::class)(),
				'value'    => Utils::$context['lp_page']['category_id'],
			]);

		CustomField::make('entry_type', Lang::$txt['lp_page_type'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new EntryTypeSelect());

		TextField::make('slug', Lang::$txt['lp_slug'])
			->setTab(Tab::SEO)
			->setDescription(Lang::$txt['lp_slug_subtext'])
			->required()
			->setAttribute('maxlength', 255)
			->setAttribute('pattern', LP_ALIAS_PATTERN)
			->setAttribute(
				'x-slug.lazy',
				empty(Utils::$context['lp_page']['id']) ? 'title' : '{}'
			)
			->setValue(Utils::$context['lp_page']['slug']);

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
				->setValue(static fn() => new TagSelect());
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
				]));
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

		$this->events()->dispatch(
			PortalHook::preparePageFields,
			[
				'options' => Utils::$context['lp_page']['options'],
				'type'    => Utils::$context['lp_page']['type'],
			]
		);

		$this->preparePostFields();
	}

	private function prepareEditor(): void
	{
		$this->events()->dispatch(PortalHook::prepareEditor, ['object' => Utils::$context['lp_page']]);
	}

	private function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		Security::checkSubmitOnce('free');

		Utils::$context['preview_title']   = Utils::$context['lp_page']['title'] ?? '';
		Utils::$context['preview_content'] = Utils::htmlspecialchars(Utils::$context['lp_page']['content'], ENT_QUOTES);

		Str::cleanBbcode(Utils::$context['preview_title']);

		Lang::censorText(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_content']);

		if (Utils::$context['preview_content']) {
			Utils::$context['preview_content'] = Content::parse(
				Utils::$context['preview_content'],
				Utils::$context['lp_page']['type']
			);
		}

		Utils::$context['page_title'] = Lang::$txt['preview'] . (
			Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : ''
		);

		Utils::$context['preview_title'] = $this->getPreviewTitle();
	}

	private function checkUser(): void
	{
		if (! User::$me->allowedTo('light_portal_manage_pages_any') && $this->request()->hasNot('u')) {
			$this->response()->redirect('action=admin;area=lp_pages;u=' . User::$me->id);
		}
	}

	private function preparePageList(): void
	{
		Utils::$context['lp_all_pages'] = [];
		foreach (Utils::$context['lp_content_types'] as $type => $title) {
			Utils::$context['lp_all_pages'][$type] = [
				'type'  => $type,
				'icon'  => ContentType::icon($type) ?: Utils::$context['lp_loaded_addons'][$type]['icon'],
				'title' => Lang::$txt['lp_' . $type]['title'] ?? $title,
				'desc'  => Lang::$txt['lp_' . $type]['block_desc'] ?? Lang::$txt['lp_' . $type]['description']
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
}
