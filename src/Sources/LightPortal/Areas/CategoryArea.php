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

use Bugo\Bricks\Tables\Column;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Areas\Traits\HasArea;
use LightPortal\Enums\Tab;
use LightPortal\Models\CategoryFactory;
use LightPortal\Repositories\CategoryRepositoryInterface;
use LightPortal\UI\Fields\CustomField;
use LightPortal\UI\Fields\TextareaField;
use LightPortal\UI\Fields\TextField;
use LightPortal\UI\Partials\SelectFactory;
use LightPortal\UI\Tables\ContextMenuColumn;
use LightPortal\UI\Tables\IconColumn;
use LightPortal\UI\Tables\PortalTableBuilder;
use LightPortal\UI\Tables\StatusColumn;
use LightPortal\UI\Tables\TitleColumn;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Language;
use LightPortal\Utils\Str;
use LightPortal\Validators\CategoryValidator;

use function LightPortal\app;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final readonly class CategoryArea implements AreaInterface
{
	use HasArea;

	public function __construct(private CategoryRepositoryInterface $repository) {}

	public function main(): void
	{
		Utils::$context['page_title']  = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_categories_manage'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_categories';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_categories_manage_description'],
		];

		$this->doActions();

		Theme::loadJavaScriptFile('light_portal/Sortable.min.js');

		$count = Utils::$context['lp_quantities']['active_categories'];
		$count = empty($count) ? '' : " ($count)";

		$builder = PortalTableBuilder::make('lp_categories', Lang::$txt['lp_categories'] . $count)
			->setDefaultSortColumn('priority')
			->setScript('const entity = new Category();
		new Sortable(document.querySelector("#lp_categories tbody"), {
			handle: ".handle",
			animation: 150,
			onSort: e => entity.updatePriority(e)
		})')
			->withCreateButton('categories')
			->setItems($this->repository->getAll(...))
			->setCount($this->repository->getTotalCount(...))
			->addColumns([
				IdColumn::make()->setSort('category_id'),
				IconColumn::make(),
				TitleColumn::make(entity: 'categories'),
				Column::make('priority', Lang::$txt['lp_block_priority'])
					->setStyle('width: 12%')
					->setData(static fn($entry) => Str::html('div')->data('id', $entry['id'])
						->setHtml($entry['priority'] . ' ' .
							Icon::get('sort', Lang::$txt['lp_action_move'], 'handle ')), 'centertext')
					->setSort('priority'),
				StatusColumn::make(),
				ContextMenuColumn::make()
			]);

		$this->getTablePresenter()->show($builder);
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
		$item = Str::typed('int', $this->request()->get('category_id') ?: $this->request()->get('id'));

		Theme::loadTemplate('LightPortal/ManageCategories');

		Utils::$context['sub_template'] = 'category_post';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_categories_edit_title'];

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
			$this->repository->remove($item);

			$this->langCache('active_categories')->forget();

			$this->response()->redirect('action=admin;area=lp_categories');
		}

		$this->validateData();

		$categoryTitle = Utils::$context['lp_category']['title'] ?? '';
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
			isset($data['delete_item']) => $this->repository->remove($data['delete_item']),
			isset($data['toggle_item']) => $this->repository->toggleStatus($data['toggle_item']),
			isset($data['update_priority']) => $this->repository->updatePriority($data['update_priority']),
			default => null,
		};

		$this->langCache('active_categories')->forget();

		exit;
	}

	private function validateData(): void
	{
		$validatedData = app(CategoryValidator::class)->validate();

		$category = app(CategoryFactory::class)->create(
			array_merge(Utils::$context['lp_current_category'], $validatedData)
		);

		Utils::$context['lp_category'] = $category->toArray();
	}

	private function prepareFormFields(): void
	{
		$this->prepareTitleFields();

		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab(Tab::APPEARANCE)
			->setValue(static fn() => SelectFactory::icon([
				'icon' => Utils::$context['lp_category']['icon'],
			]));

		TextField::make('slug', Lang::$txt['lp_slug'])
			->setTab(Tab::SEO)
			->setDescription(Lang::$txt['lp_slug_subtext'])
			->required()
			->setAttribute('maxlength', 255)
			->setAttribute('pattern', LP_ALIAS_PATTERN)
			->setAttribute(
				'x-slug.lazy',
				empty(Utils::$context['lp_category']['id']) ? 'title' : '{}'
			)
			->setValue(Utils::$context['lp_category']['slug']);

		TextareaField::make('description', Lang::$txt['lp_category_description'])
			->setTab(Tab::SEO)
			->setAttribute('maxlength', 255)
			->setValue(Utils::$context['lp_category']['description']);

		$this->preparePostFields();
	}

	private function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		Security::checkSubmitOnce('free');

		Utils::$context['preview_title']   = Str::decodeHtmlEntities(Utils::$context['lp_category']['title'] ?? '');
		Utils::$context['preview_content'] = Utils::htmlspecialchars(Utils::$context['lp_category']['description'], ENT_QUOTES);

		Str::cleanBbcode(Utils::$context['preview_title']);

		Lang::censorText(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_content']);

		Utils::$context['page_title'] = Lang::$txt['preview'] . (
			Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : ''
		);

		Utils::$context['preview_title'] = $this->getPreviewTitle(Icon::parse(Utils::$context['lp_category']['icon']));
	}
}
