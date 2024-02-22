<?php declare(strict_types=1);

/**
 * CategoryArea.php
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

use Bugo\Compat\{Config, ErrorHandler, Lang, Security, Theme, Utils};
use Bugo\LightPortal\Actions\Category;
use Bugo\LightPortal\Areas\Fields\TextareaField;
use Bugo\LightPortal\Areas\Validators\CategoryValidator;
use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Models\CategoryModel;
use Bugo\LightPortal\Repositories\CategoryRepository;
use Bugo\LightPortal\Utils\{Icon, ItemList};

if (! defined('SMF'))
	die('No direct access...');

final class CategoryArea
{
	use Area;
	use Helper;

	private CategoryRepository $repository;

	public function __construct()
	{
		$this->repository = new CategoryRepository();
	}

	public function main(): void
	{
		Theme::loadTemplate('LightPortal/ManageCategories');

		Utils::$context['template_layers'][] = 'manage_categories';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_categories_manage'];

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
				'function' => [$this->repository, 'getAll']
			],
			'get_count' => [
				'function' => [$this->repository, 'getTotalCount']
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
				'title' => [
					'header' => [
						'value' => Lang::$txt['lp_title'],
					],
					'data' => [
						'function' => static fn($entry) => '<a class="bbc_link" href="' . LP_BASE_URL . ';sa=categories;id=' . $entry['id'] . '">' . $entry['title'] . '</a>',
						'class' => 'word_break',
					],
					'sort' => [
						'default' => 't.title DESC',
						'reverse' => 't.title',
					],
				],
				'priority' => [
					'header' => [
						'value' => Lang::$txt['lp_block_priority']
					],
					'data' => [
						'function' => static fn($entry) => '<div data-id="' . $entry['id'] . '">' . $entry['priority'] . ' ' . str_replace(
							' class="',
							' title="' . Lang::$txt['lp_action_move'] . '" class="handle ',
							Icon::get('sort')
						) . '</div>',
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
								x-data="{ status: ' . ($entry['status'] === Category::STATUS_ACTIVE ? 'true' : 'false') . ' }"
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
									<svg aria-hidden="true" width="10" height="10" focusable="false" data-prefix="fas" data-icon="ellipsis-h" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
										<path fill="currentColor" d="M328 256c0 39.8-32.2 72-72 72s-72-32.2-72-72 32.2-72 72-72 72 32.2 72 72zm104-72c-39.8 0-72 32.2-72 72s32.2 72 72 72 72-32.2 72-72-32.2-72-72-72zm-352 0c-39.8 0-72 32.2-72 72s32.2 72 72 72 72-32.2 72-72-32.2-72-72-72z"></path>
									</svg>
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

		$listOptions['title'] = '
			<span class="floatright">
				<a href="' . Config::$scripturl . '?action=admin;area=lp_categories;sa=add;' . Utils::$context['session_var'] . '=' . Utils::$context['session_id'] . '" x-data>
					' . (str_replace(' class=', ' @mouseover="category.toggleSpin($event.target)" @mouseout="category.toggleSpin($event.target)" class=', Icon::get('plus', Lang::$txt['lp_categories_add']))) . '
				</a>
			</span>' . $listOptions['title'];

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

		$this->prepareForumLanguages();
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

		$this->prepareForumLanguages();

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

		if (isset($data['del_item']))
			$this->repository->remove([(int) $data['del_item']]);

		if (isset($data['toggle_item']))
			$this->toggleStatus([(int) $data['toggle_item']], 'category');

		if (isset($data['update_priority']))
			$this->repository->updatePriority($data['update_priority']);

		$this->cache()->flush();

		exit;
	}

	private function getParams(): array
	{
		$params = [];

		$this->hook('prepareCategoryParams', [&$params]);

		return $params;
	}

	private function validateData(): void
	{
		[$postData, $parameters] = (new CategoryValidator())->validate();

		$options = $this->getParams();
		$categoryOptions = Utils::$context['lp_current_category']['options'] ?? $options;

		$category = new CategoryModel($postData, Utils::$context['lp_current_category']);
		$category->titles = Utils::$context['lp_current_category']['titles'] ?? [];
		$category->options = $options;

		foreach (Utils::$context['lp_languages'] as $lang) {
			$category->titles[$lang['filename']] = $postData['title_' . $lang['filename']] ?? $category->titles[$lang['filename']] ?? '';
		}

		$this->cleanBbcode($category->titles);
		$this->cleanBbcode($category->description);

		foreach ($category->options as $option => $value) {
			if (isset($parameters[$option]) && isset($postData) && ! isset($postData[$option])) {
				$postData[$option] = 0;

				if ($parameters[$option] === FILTER_DEFAULT)
					$postData[$option] = '';

				if (is_array($parameters[$option]) && $parameters[$option]['flags'] === FILTER_REQUIRE_ARRAY)
					$postData[$option] = [];
			}

			$category->options[$option] = $postData[$option] ?? $categoryOptions[$option] ?? $value;
		}

		Utils::$context['lp_category'] = $category->toArray();
	}

	private function prepareFormFields(): void
	{
		$this->prepareTitleFields();

		TextareaField::make('description', Lang::$txt['lp_category_description'])
			->setTab('content')
			->setAttribute('maxlength', 255)
			->setValue(Utils::$context['lp_category']['description']);

		$this->hook('prepareCategoryFields');

		$this->preparePostFields();
	}

	private function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		Security::checkSubmitOnce('free');

		Utils::$context['preview_title']   = Utils::$context['lp_category']['titles'][Utils::$context['user']['language']];
		Utils::$context['preview_content'] = Utils::$smcFunc['htmlspecialchars'](Utils::$context['lp_category']['description'], ENT_QUOTES);

		$this->cleanBbcode(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_content']);

		Utils::$context['page_title']    = Lang::$txt['preview'] . (Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : '');
		Utils::$context['preview_title'] = $this->getPreviewTitle();
	}
}
