<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Areas;

use Bugo\Bricks\Presenters\TablePresenter;
use Bugo\Bricks\Tables\Column;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Traits\AreaTrait;
use Bugo\LightPortal\Areas\Validators\CategoryValidator;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Models\CategoryModel;
use Bugo\LightPortal\Repositories\CategoryRepository;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\TextareaField;
use Bugo\LightPortal\UI\Partials\IconSelect;
use Bugo\LightPortal\UI\Tables\ContextMenuColumn;
use Bugo\LightPortal\UI\Tables\IconColumn;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\StatusColumn;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\CacheTrait;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Language;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\Str;
use WPLake\Typed\Typed;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final class CategoryArea
{
	use AreaTrait;
	use CacheTrait;
	use RequestTrait;

	public function __construct(private readonly CategoryRepository $repository) {}

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

		$builder = PortalTableBuilder::make('lp_categories', Lang::$txt['lp_categories'])
			->setDefaultSortColumn('priority')
			->setScript('const entity = new Category();')
			->withCreateButton('categories')
			->setItems($this->repository->getAll(...))
			->setCount($this->repository->getTotalCount(...))
			->addColumns([
				IdColumn::make()->setSort('category_id'),
				IconColumn::make(),
				TitleColumn::make(entity: 'categories')->setSort('title DESC', 'title'),
				Column::make('priority', Lang::$txt['lp_block_priority'])
					->setStyle('width: 12%')
					->setData(static fn($entry) => Str::html('div')->data('id', $entry['id'])
						->setHtml($entry['priority'] . ' ' .
							Icon::get('sort', Lang::$txt['lp_action_move'], 'handle ')), 'centertext')
					->setSort('priority'),
				StatusColumn::make(),
				ContextMenuColumn::make()
			]);

		app(TablePresenter::class)->show($builder);
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
		$item = Typed::int($this->request()->get('category_id') ?: $this->request()->get('id'));

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
			ErrorHandler::fatalLang('lp_category_not_found', false, status: 404);
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
			default => null,
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
