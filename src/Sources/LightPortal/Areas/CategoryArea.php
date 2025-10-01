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

use Bugo\Bricks\Tables\Column;
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Bricks\Tables\TablePresenter;
use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Traits\HasArea;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Models\CategoryFactory;
use Bugo\LightPortal\Repositories\CategoryRepositoryInterface;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\TextareaField;
use Bugo\LightPortal\UI\Fields\TextField;
use Bugo\LightPortal\UI\Partials\IconSelect;
use Bugo\LightPortal\UI\Tables\ContextMenuColumn;
use Bugo\LightPortal\UI\Tables\IconColumn;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\StatusColumn;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Language;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Validators\CategoryValidator;

use function Bugo\LightPortal\app;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final readonly class CategoryArea
{
	use HasArea;

	public function __construct(private CategoryRepositoryInterface $repository) {}

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
			$this->repository->remove([$item]);

			$this->cache()->forget('all_categories');

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
		$validatedData = app(CategoryValidator::class)->validate();

		$category = app(CategoryFactory::class)->create(
			array_merge(Utils::$context['lp_current_category'], $validatedData)
		);

		Utils::$context['lp_category'] = $category->toArray();
	}

	private function prepareFormFields(): void
	{
		$this->prepareTitleFields('category');

		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab(Tab::APPEARANCE)
			->setValue(static fn() => new IconSelect(), [
				'icon' => Utils::$context['lp_category']['icon'],
			]);

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

		Utils::$context['preview_title']   = Utils::$context['lp_category']['title'] ?? '';
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
