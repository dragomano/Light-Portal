<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Areas;

use Bugo\Compat\{Config, ErrorHandler, Lang};
use Bugo\Compat\{Logging, Security, Theme, User, Utils};
use Bugo\LightPortal\Areas\Fields\{CheckboxField, CustomField};
use Bugo\LightPortal\Areas\Fields\{TextareaField, TextField};
use Bugo\LightPortal\Areas\Partials\{CategorySelect, EntryTypeSelect};
use Bugo\LightPortal\Areas\Partials\{PageAuthorSelect, PageIconSelect};
use Bugo\LightPortal\Areas\Partials\{PermissionSelect, StatusSelect, TagSelect};
use Bugo\LightPortal\Areas\Traits\AreaTrait;
use Bugo\LightPortal\Areas\Validators\PageValidator;
use Bugo\LightPortal\Args\ObjectArgs;
use Bugo\LightPortal\Args\ParamsArgs;
use Bugo\LightPortal\Enums\{EntryType, PortalHook, Status, Tab};
use Bugo\LightPortal\EventManager;
use Bugo\LightPortal\Models\PageModel;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Repositories\PageRepository;
use Bugo\LightPortal\Utils\{CacheTrait, Content, DateTime, EntityDataTrait};
use Bugo\LightPortal\Utils\{Icon, ItemList, Language, RequestTrait, Setting, Str};
use IntlException;

use function array_column;
use function array_diff;
use function array_merge;
use function array_multisort;
use function base64_encode;
use function count;
use function filter_input;
use function implode;
use function is_array;
use function str_replace;
use function strtoupper;
use function time;
use function trim;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class PageArea
{
	use AreaTrait;
	use CacheTrait;
	use EntityDataTrait;
	use RequestTrait;

	private array $params = [];

	private string $browseType;

	private string $type;

	private int $status;

	private PageRepository $repository;

	public function __construct()
	{
		$this->repository = new PageRepository;
	}

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
		$key  = Utils::$context['allow_light_portal_manage_pages_any'] && $this->request()->hasNot('u') ? 'all' : 'own';
		$tabs = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_pages_manage_' . $key . '_pages'] . ' ' . Lang::$txt['lp_pages_manage_description'],
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

		$listOptions = [
			'id' => 'lp_pages',
			'items_per_page' => 20,
			'title' => Lang::$txt['lp_pages_extra'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Utils::$context['form_action'] . $this->type . (
				empty(Utils::$context['search_params']) ? '' : ';params=' . Utils::$context['search_params']
			),
			'default_sort_col' => 'date',
			'get_items' => [
				'function' => $this->repository->getAll(...),
				'params'   => $this->params,
			],
			'get_count' => [
				'function' => $this->repository->getTotalCount(...),
				'params'   => $this->params,
			],
			'columns' => [
				'id' => [
					'header' => [
						'value' => '#',
						'style' => 'width: 5%',
					],
					'data' => [
						'db' => 'id',
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'p.page_id',
						'reverse' => 'p.page_id DESC',
					],
				],
				'date' => [
					'header' => [
						'value' => Lang::$txt['date'],
					],
					'data' => [
						'db' => 'created_at',
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'date DESC',
						'reverse' => 'date',
					],
				],
				'num_views' => [
					'header' => [
						'value' => Icon::get('views', Lang::$txt['lp_views'])
					],
					'data' => [
						'db' => 'num_views',
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'p.num_views DESC',
						'reverse' => 'p.num_views',
					],
				],
				'slug' => [
					'header' => [
						'value' => Lang::$txt['lp_page_slug'],
					],
					'data' => [
						'db' => 'slug',
						'class' => 'centertext word_break',
					],
					'sort' => [
						'default' => 'p.slug DESC',
						'reverse' => 'p.slug',
					],
				],
				'title' => [
					'header' => [
						'value' => Lang::$txt['lp_title'],
					],
					'data' => [
						'function' => fn($entry) => Str::html('i', [
								'class' => $this->getPageIcon($entry['type']),
								'title' => Utils::$context['lp_content_types'][$entry['type']] ?? strtoupper((string) $entry['type']),
							]) . ' ' . Str::html('a', [
								'class' => 'bbc_link' . ($entry['is_front'] ? ' _highlight' : ''),
								'href'  => $entry['is_front'] ? Config::$scripturl : (LP_PAGE_URL . $entry['slug']),
							])->setText($entry['title']),
						'class' => 'word_break',
					],
					'sort' => [
						'default' => 't.value DESC',
						'reverse' => 't.value',
					],
				],
				'status' => [
					'header' => [
						'value' => Lang::$txt['status'],
					],
					'data' => [
						'function' => fn($entry) => $entry['status'] >= count(Status::cases()) - 1
							? Lang::$txt['lp_page_status_set'][$entry['status']] ?? Lang::$txt['no']
							: (Utils::$context['allow_light_portal_approve_pages']
								? Str::html('div', [
										'data-id' => $entry['id'],
										'x-data'  => '{ status: ' . ($entry['status'] === $this->status ? 'true' : 'false') . ' }',
										'x-init'  => '$watch(\'status\', value => page.toggleStatus($el))',
									])->setHtml(Str::html('span', [
										':class' => '{ \'on\': status, \'off\': !status }',
										':title' => 'status ? \'' . Lang::$txt['lp_action_off'] . '\' : \'' . Lang::$txt['lp_action_on'] . '\'',
										'x-on:click.prevent' => 'status = !status',
									])
								)
								: Str::html('div', [
										'x-data' => '{ status: ' . ($entry['status'] === $this->status ? 'true' : 'false') . ' }',
									])->setHtml(Str::html('span', [
										':class' => '{ \'on\': status, \'off\': !status }',
										'style'  => 'cursor: inherit;',
									])
								)
							),
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'p.status DESC',
						'reverse' => 'p.status',
					],
				],
				'actions' => [
					'header' => [
						'value' => Lang::$txt['lp_actions'],
						'style' => 'width: 8%',
					],
					'data' => [
						'function' => fn($entry) => /** @lang text */ '
						<div data-id="' . $entry['id'] . '" x-data="{ showContextMenu: false }">
							<div class="context_menu" @click.outside="showContextMenu = false">
								<button class="button floatnone" @click.prevent="showContextMenu = true">
									' . Icon::get('ellipsis') . '
								</button>
								<div class="roundframe" x-show="showContextMenu">
									<ul>' . ($this->request()->has('deleted') ? (
											Str::html('li')->addHtml(
												Str::html('a')
													->setAttribute('x-on:click.prevent', 'showContextMenu = false; page.restore($root)')
													->class('button')
													->setText(Lang::$txt['restore_message'])
											) .
											Str::html('li')->addHtml(
												Str::html('a')
													->setAttribute('x-on:click.prevent', 'showContextMenu = false; page.removeForever($root)')
													->class('button error')
													->setText(Lang::$txt['lp_action_remove_permanently'])
											)
										) : (
											Str::html('li')->addHtml(
												Str::html('a')
													->setAttribute('href', Config::$scripturl . "?action=admin;area=lp_pages;sa=edit;id={$entry['id']}")
													->class('button')
													->setText(Lang::$txt['modify'])
											) .
											Str::html('li')->addHtml(
												Str::html('a')
													->setAttribute('x-on:click.prevent', 'showContextMenu = false; page.removeForever($root)')
													->class('button error')
													->setText(Lang::$txt['remove'])
											)
										)) . '
									</ul>
								</div>
							</div>
						</div>',
						'class' => 'centertext',
					],
				],
			],
			'form' => [
				'name' => 'manage_pages',
				'href' => Utils::$context['form_action'] . $this->type,
				'include_sort' => true,
				'hidden_fields' => [
					Utils::$context['session_var'] => Utils::$context['session_id'],
					'params' => Utils::$context['search_params'],
				],
			],
			'javascript' => 'const page = new Page();',
			'additional_rows' => [
				[
					'position' => 'after_title',
					'value' =>
						Str::html('div', ['class' => 'row'])
							->addHtml(
								Str::html('div', ['class' => 'col-lg-10'])->setHtml(
									Str::html('input', [
										'type' => 'search',
										'name' => 'search',
										'value' => Utils::$context['search']['string'],
										'placeholder' => Lang::$txt['lp_pages_search'],
										'style' => 'width: 100%',
									])
								)
							)
							->addHtml(
								Str::html('div', ['class' => 'col-lg-2'])->setHtml(
									Str::html('button', [
										'type' => 'submit',
										'name' => 'is_search',
										'class' => 'button floatnone',
										'style' => 'width: 100%',
									])->setHtml(Icon::get('search') . Lang::$txt['search'])
								)
							),
				],
			],
		];

		$this->addTypeSelect($listOptions);

		if (Utils::$context['user']['is_admin']) {
			$listOptions['columns']['mass'] = [
				'header' => [
					'value' => Str::html('input', [
						'type'    => 'checkbox',
						'onclick' => 'invertAll(this, this.form)',
					])
				],
				'data' => [
					'function' => static fn($entry) => Str::html('input', [
						'type'  => 'checkbox',
						'value' => $entry['id'],
						'name'  => 'items[]',
					]),
					'class' => 'centertext',
				],
			];

			$listOptions['additional_rows'][] = [
				'position' => 'below_table_data',
				'value' =>
					Str::html('select', ['name' => 'page_actions'])
						->addHtml(
							Str::html('option', [
								'value' => $this->request()->has('deleted') ? 'delete_forever' : 'delete'
							])->setText(Lang::$txt[$this->request()->has('deleted') ? 'lp_action_remove_permanently' : 'remove'])
						)
						->addHtml(
							Utils::$context['allow_light_portal_approve_pages']
								? Str::html('option', ['value' => 'toggle'])
									->setText(Lang::$txt['lp_action_toggle'])
								: ''
						)
						->addHtml(
							Setting::isFrontpageMode('chosen_pages')
								? Str::html('option', ['value' => 'promote_up'])
									->setText(Lang::$txt['lp_promote_to_fp'])
								: ''
						)
						->addHtml(
							Setting::isFrontpageMode('chosen_pages')
								? Str::html('option', ['value' => 'promote_down'])
									->setText(Lang::$txt['lp_remove_from_fp'])
								: ''
						) . ' ' .
					Str::html('input', [
						'type'    => 'submit',
						'name'    => 'mass_actions',
						'value'   => Lang::$txt['quick_mod_go'],
						'class'   => 'button',
						'onclick' => 'return document.forms[\'manage_pages\'][\'page_actions\'].value && confirm(\'' . Lang::$txt['quickmod_confirm'] . '\');',
					]),
				'class' => 'floatright',
			];
		}

		$listOptions['title'] = Str::html('span', ['class' => 'floatright'])
			->addHtml(
				Str::html('a', [
					'href' => Config::$scripturl . '?action=admin;area=lp_pages;sa=add;' . Utils::$context['session_var'] . '=' . Utils::$context['session_id'],
					'x-data' => '',
				])
				->setHtml(str_replace(
					' class=',
					' @mouseover="page.toggleSpin($event.target)" @mouseout="page.toggleSpin($event.target)" class=',
					Icon::get('plus', Lang::$txt['lp_pages_add'])
				))
			) . $listOptions['title'];

		if (Setting::getCommentBlock() === 'default') {
			unset($listOptions['columns']['num_comments']);
		}

		new ItemList($listOptions);

		$this->changeTableTitle();
	}

	public function add(): void
	{
		Theme::loadTemplate('LightPortal/ManagePages');

		Utils::$context['sub_template'] = 'page_add';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_pages_add_title'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_add_title'];
		Utils::$context['form_action']     = Config::$scripturl . '?action=admin;area=lp_pages;sa=add';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_pages_add_description'],
		];

		$this->preparePageList();

		$json = $this->request()->json();
		$type = $json['add_page'] ?? $this->request('add_page', '') ?? '';

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

	/**
	 * @throws IntlException
	 */
	public function edit(): void
	{
		$item = (int) ($this->request('page_id') ?: $this->request('id'));

		Theme::loadTemplate('LightPortal/ManagePages');

		Utils::$context['sub_template'] = 'page_post';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_pages_edit_title'];

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_pages_edit_description'],
		];

		$data = $this->repository->getData($item);

		$this->repository->prepareData($data);

		Utils::$context['lp_current_page'] = $data;

		if (empty(Utils::$context['lp_current_page'])) {
			ErrorHandler::fatalLang('lp_page_not_found', status: 404);
		}

		if (Utils::$context['lp_current_page']['can_edit'] === false) {
			ErrorHandler::fatalLang('lp_page_not_editable');
		}

		Language::prepareList();

		if ($this->request()->has('remove')) {
			if (Utils::$context['lp_current_page']['author_id'] !== User::$info['id']) {
				Logging::logAction('remove_lp_page', [
					'page' => Utils::$context['lp_current_page']['titles'][User::$info['language']]
				]);
			}

			$this->repository->remove([$item]);

			$this->cache()->flush();

			Utils::redirectexit('action=admin;area=lp_pages');
		}

		$this->validateData();

		$pageTitle = Utils::$context['lp_page']['titles'][Utils::$context['user']['language']] ?? '';
		Utils::$context['page_area_title'] = Lang::$txt['lp_pages_edit_title'] . ($pageTitle ? ' - ' . $pageTitle : '');
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . Utils::$context['lp_page']['id'];

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
			isset($data['delete_item'])    => $this->repository->remove([(int) $data['delete_item']]),
			isset($data['toggle_item'])    => $this->repository->toggleStatus([(int) $data['toggle_item']]),
			isset($data['restore_item'])   => $this->repository->restore([(int) $data['restore_item']]),
			isset($data['remove_forever']) => $this->repository->removePermanently([(int) $data['remove_forever']]),
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

		$items = $this->request('items');
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

		Utils::redirectexit($redirect);
	}

	private function calculateParams(): void
	{
		$searchParamString = trim((string) $this->request('search', ''));
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
					: ' AND (INSTR(LOWER(p.slug), {string:search}) > 0 OR INSTR(LOWER(t.value), {string:search}) > 0)'
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
				'entry_type' => $this->request('type', EntryType::DEFAULT->name()),
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
			$this->type = ';u=' . User::$info['id'];
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
				';u=' . User::$info['id'],
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

		if (! Utils::$context['allow_light_portal_manage_pages_any']) {
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

	private function addTypeSelect(array &$listOptions): void
	{
		$types = '';
		foreach (Utils::$context['lp_page_types'] as $type => $text) {
			if (Utils::$context['user']['is_admin'] === false && $type === 'internal')
				continue;

			$types .= Str::html('option', [
				'value'    => $type,
				'selected' => $this->request()->has('type') && $this->request()->get('type') === $type,
			])->setText($text);
		}

		$listOptions['additional_rows'][] = [
			'position' => 'above_column_headers',
			'value' =>
				Str::html('label', ['for' => 'type'])
					->setText(Lang::$txt['lp_page_type']) . ' ' .
				Str::html('select', [
					'id'       => 'type',
					'name'     => 'type',
					'onchange' => 'this.form.submit()',
				])->addHtml($types),
			'class' => 'floatright',
		];
	}

	private function promote(array $items, string $type = 'up'): void
	{
		if ($items === [])
			return;

		if ($type === 'down') {
			$items = array_diff(Setting::getFrontpagePages(), $items);
		} else {
			$items = array_merge(
				array_diff($items, Setting::getFrontpagePages()), Setting::getFrontpagePages()
			);
		}

		Config::updateModSettings(['lp_frontpage_pages' => implode(',', $items)]);
	}

	private function getParams(): array
	{
		$baseParams = [
			'show_title'           => true,
			'show_in_menu'         => false,
			'page_icon'            => '',
			'show_author_and_date' => true,
			'show_related_pages'   => false,
			'allow_comments'       => false,
		];

		$params = [];

		EventManager::getInstance()->dispatch(PortalHook::preparePageParams, new Event(new ParamsArgs($params)));

		return array_merge($baseParams, $params);
	}

	private function validateData(): void
	{
		[$postData, $parameters] = (new PageValidator())->validate();

		$options = $this->getParams();
		$pageOptions = Utils::$context['lp_current_page']['options'] ?? $options;

		$page = new PageModel($postData, Utils::$context['lp_current_page']);
		$page->authorId = empty($postData['author_id']) ? $page->authorId : $postData['author_id'];
		$page->titles = Utils::$context['lp_current_page']['titles'] ?? [];
		$page->options = $options;

		$dateTime = DateTime::get();
		$page->date = $postData['date'] ?? $dateTime->format('Y-m-d');
		$page->time = $postData['time'] ?? $dateTime->format('H:i');

		foreach (Utils::$context['lp_languages'] as $lang) {
			$page->titles[$lang['filename']] = $postData['title_' . $lang['filename']] ?? $page->titles[$lang['filename']] ?? '';
		}

		Str::cleanBbcode($page->titles);
		Str::cleanBbcode($page->description);

		foreach ($page->options as $option => $value) {
			if (isset($parameters[$option]) && isset($postData) && ! isset($postData[$option])) {
				$postData[$option] = 0;

				if ($parameters[$option] === FILTER_DEFAULT)
					$postData[$option] = '';

				if (is_array($parameters[$option]) && $parameters[$option]['flags'] === FILTER_REQUIRE_ARRAY)
					$postData[$option] = [];
			}

			$page->options[$option] = $postData[$option] ?? $pageOptions[$option] ?? $value;
		}

		Utils::$context['lp_page'] = $page->toArray();
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
		}

		CustomField::make('permissions', Lang::$txt['edit_permissions'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new PermissionSelect());

		CustomField::make('category_id', Lang::$txt['lp_category'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new CategorySelect(), [
				'id'         => 'category_id',
				'multiple'   => false,
				'full_width' => false,
				'data'       => $this->getEntityData('category'),
				'value'      => Utils::$context['lp_page']['category_id'],
			]);

		CustomField::make('entry_type', Lang::$txt['lp_page_type'])
			->setTab(Tab::ACCESS_PLACEMENT)
			->setValue(static fn() => new EntryTypeSelect());

		if (Utils::$context['user']['is_admin']) {
			CustomField::make('status', Lang::$txt['status'])
				->setTab(Tab::ACCESS_PLACEMENT)
				->setValue(static fn() => new StatusSelect());

			CustomField::make('author_id', Lang::$txt['lp_page_author'])
				->setTab(Tab::ACCESS_PLACEMENT)
				->setDescription(Lang::$txt['lp_page_author_placeholder'])
				->setValue(static fn() => new PageAuthorSelect());
		}

		TextField::make('slug', Lang::$txt['lp_page_slug'])
			->setTab(Tab::SEO)
			->setDescription(Lang::$txt['lp_page_slug_subtext'])
			->required()
			->setAttribute('maxlength', 255)
			->setAttribute('pattern', LP_ALIAS_PATTERN)
			->setAttribute(
				'x-slug.lazy.replacement._',
				empty(Utils::$context['lp_page']['id']) ? 'title_' . User::$info['language'] : '{}'
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

		if (! Setting::showRelatedPages()) {
			CheckboxField::make('show_related_pages', Lang::$txt['lp_page_show_related_pages'])
				->setValue(Utils::$context['lp_page']['options']['show_related_pages']);
		}

		if (Setting::getCommentBlock() !== '' && Setting::getCommentBlock() !== 'none') {
			CheckboxField::make('allow_comments', Lang::$txt['lp_page_allow_comments'])
				->setValue(Utils::$context['lp_page']['options']['allow_comments']);
		}

		EventManager::getInstance()->dispatch(PortalHook::preparePageFields);

		$this->preparePostFields();
	}

	private function prepareEditor(): void
	{
		EventManager::getInstance()->dispatch(
			PortalHook::prepareEditor,
			new Event(new ObjectArgs(Utils::$context['lp_page']))
		);
	}

	private function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		Security::checkSubmitOnce('free');

		Utils::$context['preview_title']   = Utils::$context['lp_page']['titles'][Utils::$context['user']['language']];
		Utils::$context['preview_content'] = Utils::htmlspecialchars(
			Utils::$context['lp_page']['content'], ENT_QUOTES
		);

		Str::cleanBbcode(Utils::$context['preview_title']);

		Lang::censorText(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_content']);

		if (Utils::$context['preview_content']) {
			Utils::$context['preview_content'] = Content::parse(
				Utils::$context['preview_content'], Utils::$context['lp_page']['type']
			);
		}

		Utils::$context['page_title'] = Lang::$txt['preview'] . (
			Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : ''
		);

		Utils::$context['preview_title'] = $this->getPreviewTitle();
	}

	private function checkUser(): void
	{
		if (Utils::$context['allow_light_portal_manage_pages_any'] === false && $this->request()->hasNot('u')) {
			Utils::redirectexit('action=admin;area=lp_pages;u=' . User::$info['id']);
		}
	}

	private function preparePageList(): void
	{
		$defaultTypes = $this->getDefaultTypes();

		Utils::$context['lp_all_pages'] = [];
		foreach (Utils::$context['lp_content_types'] as $type => $title) {
			Utils::$context['lp_all_pages'][$type] = [
				'type'  => $type,
				'icon'  => $defaultTypes[$type]['icon'] ?? Utils::$context['lp_loaded_addons'][$type]['icon'],
				'title' => Lang::$txt['lp_' . $type]['title'] ?? $title,
				'desc'  => Lang::$txt['lp_' . $type]['block_desc'] ?? Lang::$txt['lp_' . $type]['description']
			];
		}

		$titles = array_column(Utils::$context['lp_all_pages'], 'title');
		array_multisort($titles, SORT_ASC, Utils::$context['lp_all_pages']);
	}

	private function getPageIcon(string $type): string
	{
		return $this->getDefaultTypes()[$type]['icon'] ?? Utils::$context['lp_loaded_addons'][$type]['icon'] ?? 'fas fa-question';
	}
}
