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

use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Areas\Traits\HasArea;
use LightPortal\Enums\Tab;
use LightPortal\Models\TagFactory;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\UI\Fields\CustomField;
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
use LightPortal\Validators\TagValidator;

use function LightPortal\app;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

final readonly class TagArea implements AreaInterface
{
	use HasArea;

	public function __construct(private TagRepositoryInterface $repository) {}

	public function main(): void
	{
		Utils::$context['page_title']  = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tags_manage'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_tags';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tags_manage_description'],
		];

		$this->doActions();

		$count = Utils::$context['lp_quantities']['active_tags'];
		$count = empty($count) ? '' : " ($count)";

		$builder = PortalTableBuilder::make('lp_tags', Lang::$txt['lp_tags'] . $count)
			->setDefaultSortColumn('title')
			->setScript('const entity = new Tag();')
			->withCreateButton('tags')
			->setItems($this->repository->getAll(...))
			->setCount($this->repository->getTotalCount(...))
			->addColumns([
				IdColumn::make()->setSort('tag_id'),
				IconColumn::make(),
				TitleColumn::make(entity: 'tags'),
				StatusColumn::make(),
				ContextMenuColumn::make()
			]);

		$this->getTablePresenter()->show($builder);
	}

	public function add(): void
	{
		Theme::loadTemplate('LightPortal/ManageTags');

		Utils::$context['sub_template'] = 'tag_post';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tags_add_title'];

		Utils::$context['page_area_title'] = Lang::$txt['lp_tags_add_title'];

		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_tags;sa=add';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tags_add_description'],
		];

		Utils::$context['lp_current_tag'] ??= [];

		Language::prepareList();

		$this->validateData();
		$this->prepareFormFields();
		$this->preparePreview();

		$this->repository->setData();
	}

	public function edit(): void
	{
		$item = Str::typed('int', $this->request()->get('tag_id') ?: $this->request()->get('id'));

		Theme::loadTemplate('LightPortal/ManageTags');

		Utils::$context['sub_template'] = 'tag_post';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tags_edit_title'];

		Utils::$context['page_area_title'] = Lang::$txt['lp_tags_edit_title'];

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tags_edit_description'],
		];

		Utils::$context['lp_current_tag'] = $this->repository->getData($item);

		if (empty(Utils::$context['lp_current_tag'])) {
			ErrorHandler::fatalLang('lp_tag_not_found', false, status: 404);
		}

		Language::prepareList();

		if ($this->request()->has('remove')) {
			$this->repository->remove([$item]);

			$this->cache()->flush();

			$this->response()->redirect('action=admin;area=lp_tags');
		}

		$this->validateData();

		$tagTitle = Utils::$context['lp_tag']['title'] ?? '';
		Utils::$context['page_area_title'] = Lang::$txt['lp_tags_edit_title'] . ($tagTitle ? ' - ' . $tagTitle : '');

		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_tags;sa=edit;id=' . Utils::$context['lp_tag']['id'];

		$this->prepareFormFields();
		$this->preparePreview();

		$this->repository->setData(Utils::$context['lp_tag']['id']);
	}

	private function doActions(): void
	{
		if ($this->request()->hasNot('actions'))
			return;

		$data = $this->request()->json();

		match (true) {
			isset($data['delete_item']) => $this->repository->remove([(int) $data['delete_item']]),
			isset($data['toggle_item']) => $this->repository->toggleStatus([(int) $data['toggle_item']]),
			default => null,
		};

		$this->cache()->flush();

		exit;
	}

	private function validateData(): void
	{
		$validatedData = app(TagValidator::class)->validate();

		$tag = app(TagFactory::class)->create(
			array_merge(Utils::$context['lp_current_tag'], $validatedData)
		);

		Utils::$context['lp_tag'] = $tag->toArray();
	}

	private function prepareFormFields(): void
	{
		$this->prepareTitleFields();

		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab(Tab::APPEARANCE)
			->setValue(static fn() => SelectFactory::icon([
				'icon' => Utils::$context['lp_tag']['icon'],
			]));

		TextField::make('slug', Lang::$txt['lp_slug'])
			->setTab(Tab::SEO)
			->setDescription(Lang::$txt['lp_slug_subtext'])
			->required()
			->setAttribute('maxlength', 255)
			->setAttribute('pattern', LP_ALIAS_PATTERN)
			->setAttribute(
				'x-slug.lazy',
				empty(Utils::$context['lp_tag']['id']) ? 'title' : '{}'
			)
			->setValue(Utils::$context['lp_tag']['slug']);

		$this->preparePostFields();
	}

	private function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		Security::checkSubmitOnce('free');

		Utils::$context['preview_title'] = Str::decodeHtmlEntities(Utils::$context['lp_tag']['title'] ?? '');

		Str::cleanBbcode(Utils::$context['preview_title']);

		Lang::censorText(Utils::$context['preview_title']);

		Utils::$context['page_title'] = Lang::$txt['preview'] . (
			Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : ''
		);

		Utils::$context['preview_title'] = Icon::parse(Utils::$context['lp_tag']['icon']) . Utils::$context['preview_title'];
	}
}
