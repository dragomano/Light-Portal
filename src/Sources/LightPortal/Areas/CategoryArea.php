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

use Bugo\Compat\{Config, ErrorHandler, Lang, Security, Theme, Utils};
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Areas\Fields\TextareaField;
use Bugo\LightPortal\Areas\Partials\IconSelect;
use Bugo\LightPortal\Areas\Traits\AreaTrait;
use Bugo\LightPortal\Areas\Validators\CategoryValidator;
use Bugo\LightPortal\Enums\{Status, Tab};
use Bugo\LightPortal\Models\CategoryModel;
use Bugo\LightPortal\Repositories\CategoryRepository;
use Bugo\LightPortal\Utils\{CacheTrait, Icon, ItemList};
use Bugo\LightPortal\Utils\{Language, RequestTrait, Str};
use Nette\Utils\Html;

use function str_replace;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryArea
{
	use AreaTrait;
	use CacheTrait;
	use RequestTrait;

	private CategoryRepository $repository;

	public function __construct()
	{
		$this->repository = new CategoryRepository();
	}

	public function main(): void
	{
		Theme::loadTemplate('LightPortal/ManageCategories');

		Utils::$context['template_layers'][] = 'manage_categories';

		Utils::$context['page_title']  = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_categories_manage'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_categories';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_categories_manage_description'],
		];

		$this->doActions();

		$listOptions = [
			'id' => 'lp_categories',
			'items_per_page' => 20,
			'title' => Lang::$txt['lp_categories'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Utils::$context['form_action'],
			'default_sort_col' => 'priority',
			'get_items' => [
				'function' => $this->repository->getAll(...)
			],
			'get_count' => [
				'function' => $this->repository->getTotalCount(...)
			],
			'columns' => [
				'id' => [
					'header' => [
						'value' => '#',
						'style' => 'width: 5%'
					],
					'data' => [
						'db'    => 'id',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'category_id',
						'reverse' => 'category_id DESC'
					]
				],
				'icon' => [
					'header' => [
						'value' => Lang::$txt['custom_profile_icon']
					],
					'data' => [
						'db'    => 'icon',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'icon',
						'reverse' => 'icon DESC'
					]
				],
				'title' => [
					'header' => [
						'value' => Lang::$txt['lp_title'],
					],
					'data' => [
						'function' => static fn($entry) => $entry['status']
							? Html::el('a', ['class' => 'bbc_link'])
								->href(LP_BASE_URL . ';sa=categories;id=' . $entry['id'])
								->setText($entry['title'])
								->toHtml()
							: $entry['title'],
						'class' => 'word_break',
					],
					'sort' => [
						'default' => 'title DESC',
						'reverse' => 'title',
					],
				],
				'priority' => [
					'header' => [
						'value' => Lang::$txt['lp_block_priority']
					],
					'data' => [
						'function' => static fn($entry) => Html::el('div')->data('id', $entry['id'])
							->setHtml($entry['priority'] . ' ' . Icon::get('sort', Lang::$txt['lp_action_move'], 'handle '))
							->toHtml(),
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'priority',
						'reverse' => 'priority DESC'
					]
				],
				'status' => [
					'header' => [
						'value' => Lang::$txt['status'],
					],
					'data' => [
						'function' => static fn($entry) => /** @lang text */ '
							<div
								data-id="' . $entry['id'] . '"
								x-data="{ status: ' . ($entry['status'] === Status::ACTIVE->value ? 'true' : 'false') . ' }"
								x-init="$watch(\'status\', value => category.toggleStatus($el))"
							>
								<span
									:class="{ \'on\': status, \'off\': !status }"
									:title="status ? \'' . Lang::$txt['lp_action_off'] . '\' : \'' . Lang::$txt['lp_action_on'] . '\'"
									@click.prevent="status = !status"
								></span>
							</div>',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'status DESC',
						'reverse' => 'status'
					],
				],
				'actions' => [
					'header' => [
						'value' => Lang::$txt['lp_actions'],
						'style' => 'width: 8%',
					],
					'data' => [
						'function' => static fn($entry) => /** @lang text */ '
						<div data-id="' . $entry['id'] . '" x-data="{ showContextMenu: false }">
							<div class="context_menu" @click.outside="showContextMenu = false">
								<button class="button floatnone" @click.prevent="showContextMenu = true">
									' . Icon::get('ellipsis') . '
								</button>
								<div class="roundframe" x-show="showContextMenu">
									<ul>
										<li>
											<a href="' . Config::$scripturl . '?action=admin;area=lp_categories;sa=edit;id=' . $entry['id'] . '" class="button">' . Lang::$txt['modify'] . '</a>
										</li>
										<li>
											<a @click.prevent="showContextMenu = false; category.remove($root)" class="button error">' . Lang::$txt['remove'] . '</a>
										</li>
									</ul>
								</div>
							</div>
						</div>',
						'class' => 'centertext',
					],
				],
			],
			'form' => [
				'href' => Utils::$context['form_action']
			],
		];

		$listOptions['title'] = Html::el('span', ['class' => 'floatright'])
			->addHtml(
				Html::el('a', [
					'href' => Config::$scripturl . '?action=admin;area=lp_categories;sa=add;' . Utils::$context['session_var'] . '=' . Utils::$context['session_id'],
					'x-data' => '',
				])
				->setHtml(str_replace(
					' class=',
					' @mouseover="category.toggleSpin($event.target)" @mouseout="category.toggleSpin($event.target)" class=',
					Icon::get('plus', Lang::$txt['lp_categories_add'])
				))
				->toHtml()
			)
			->toHtml() . $listOptions['title'];

		new ItemList($listOptions);
	}

	public function add(): void
	{
		Theme::loadTemplate('LightPortal/ManageCategories');

		Utils::$context['sub_template'] = 'category_post';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_categories_add_title'];

		Utils::$context['page_area_title'] = Lang::$txt['lp_categories_add_title'];

		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_categories;sa=add';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_categories_add_description'],
		];

		Utils::$context['lp_current_category'] ??= [];

		Language::prepareList();

		$this->validateData();
		$this->prepareFormFields();
		$this->preparePreview();

		$this->repository->setData();
	}

	public function edit(): void
	{
		$item = (int) ($this->request('category_id') ?: $this->request('id'));

		Theme::loadTemplate('LightPortal/ManageCategories');

		Utils::$context['sub_template'] = 'category_post';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_categories_edit_title'];
		Utils::$context['page_area_title'] = Lang::$txt['lp_categories_edit_title'];

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_categories_edit_description'],
		];

		Utils::$context['lp_current_category'] = $this->repository->getData($item);

		if (empty(Utils::$context['lp_current_category'])) {
			ErrorHandler::fatalLang('lp_category_not_found', status: 404);
		}

		Language::prepareList();

		if ($this->request()->has('remove')) {
			$this->repository->remove([$item]);

			$this->cache()->flush();

			Utils::redirectexit('action=admin;area=lp_categories');
		}

		$this->validateData();

		$categoryTitle = Utils::$context['lp_category']['titles'][Utils::$context['user']['language']] ?? '';
		Utils::$context['page_area_title'] = Lang::$txt['lp_categories_edit_title'] . ($categoryTitle ? ' - ' . $categoryTitle : '');

		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_categories;sa=edit;id=' . Utils::$context['lp_category']['id'];

		$this->prepareFormFields();
		$this->preparePreview();

		$this->repository->setData(Utils::$context['lp_category']['id']);
	}

	private function doActions(): void
	{
		if ($this->request()->hasNot('actions'))
			return;

		$data = $this->request()->json();

		match (true) {
			isset($data['delete_item']) => $this->repository->remove([(int) $data['delete_item']]),
			isset($data['toggle_item']) => $this->repository->toggleStatus([(int) $data['toggle_item']]),
			isset($data['update_priority']) => $this->repository->updatePriority($data['update_priority']),
		};

		$this->cache()->flush();

		exit;
	}

	private function validateData(): void
	{
		$postData = (new CategoryValidator())->validate();

		$category = new CategoryModel($postData, Utils::$context['lp_current_category']);
		$category->icon = $category->icon === 'undefined' ? '' : $category->icon;
		$category->titles = Utils::$context['lp_current_category']['titles'] ?? [];

		foreach (Utils::$context['lp_languages'] as $lang) {
			$category->titles[$lang['filename']] = $postData['title_' . $lang['filename']] ?? $category->titles[$lang['filename']] ?? '';
		}

		Str::cleanBbcode($category->titles);
		Str::cleanBbcode($category->description);

		Utils::$context['lp_category'] = $category->toArray();
	}

	private function prepareFormFields(): void
	{
		$this->prepareTitleFields();

		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new IconSelect(), [
				'icon' => Utils::$context['lp_category']['icon'],
			]);

		TextareaField::make('description', Lang::$txt['lp_category_description'])
			->setTab(Tab::CONTENT)
			->setAttribute('maxlength', 255)
			->setValue(Utils::$context['lp_category']['description']);

		$this->preparePostFields();
	}

	private function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		Security::checkSubmitOnce('free');

		Utils::$context['preview_title']   = Utils::$context['lp_category']['titles'][Utils::$context['user']['language']];
		Utils::$context['preview_content'] = Utils::htmlspecialchars(Utils::$context['lp_category']['description'], ENT_QUOTES);

		Str::cleanBbcode(Utils::$context['preview_title']);

		Lang::censorText(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_content']);

		Utils::$context['page_title']    = Lang::$txt['preview'] . (Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : '');
		Utils::$context['preview_title'] = $this->getPreviewTitle(Icon::parse(Utils::$context['lp_category']['icon']));
	}
}
