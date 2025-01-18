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
use Bugo\Bricks\Tables\IdColumn;
use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Areas\Traits\AreaTrait;
use Bugo\LightPortal\Areas\Validators\TagValidator;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Models\TagModel;
use Bugo\LightPortal\Repositories\TagRepository;
use Bugo\LightPortal\UI\Fields\CustomField;
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

final class TagArea
{
	use AreaTrait;
	use CacheTrait;
	use RequestTrait;

	public function __construct(private readonly TagRepository $repository) {}

	public function main(): void
	{
		Utils::$context['page_title']  = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tags_manage'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_tags';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tags_manage_description'],
		];

		$this->doActions();

		$builder = PortalTableBuilder::make('lp_tags', Lang::$txt['lp_tags'])
			->setDefaultSortColumn('title')
			->setScript('const entity = new Tag();')
			->withCreateButton('tags')
			->setItems($this->repository->getAll(...))
			->setCount($this->repository->getTotalCount(...))
			->addColumns([
				IdColumn::make()->setSort('tag_id'),
				IconColumn::make(),
				TitleColumn::make(entity: 'tags')->setSort('t.value'),
				StatusColumn::make(),
				ContextMenuColumn::make()
			]);

		TablePresenter::show($builder);
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
		$item = Typed::int($this->request()->get('tag_id') ?: $this->request()->get('id'));

		Theme::loadTemplate('LightPortal/ManageTags');

		Utils::$context['sub_template'] = 'tag_post';

		Utils::$context['page_title']      = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tags_edit_title'];
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

			Utils::redirectexit('action=admin;area=lp_tags');
		}

		$this->validateData();

		$tagTitle = Utils::$context['lp_tag']['titles'][Utils::$context['user']['language']] ?? '';
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
		$postData = (new TagValidator())->validate();

		$tag = new TagModel($postData, Utils::$context['lp_current_tag']);
		$tag->icon = $tag->icon === 'undefined' ? '' : $tag->icon;
		$tag->titles = Utils::$context['lp_current_tag']['titles'] ?? [];

		foreach (Utils::$context['lp_languages'] as $lang) {
			$tag->titles[$lang['filename']] = $postData['title_' . $lang['filename']] ?? $tag->titles[$lang['filename']] ?? '';
		}

		Str::cleanBbcode($tag->titles);

		Utils::$context['lp_tag'] = $tag->toArray();
	}

	private function prepareFormFields(): void
	{
		$this->prepareTitleFields();

		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new IconSelect(), [
				'icon' => Utils::$context['lp_tag']['icon'],
			]);

		$this->preparePostFields();
	}

	private function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		Security::checkSubmitOnce('free');

		Utils::$context['preview_title'] = Utils::$context['lp_tag']['titles'][Utils::$context['user']['language']];

		Str::cleanBbcode(Utils::$context['preview_title']);

		Lang::censorText(Utils::$context['preview_title']);

		Utils::$context['page_title']    = Lang::$txt['preview'] . (
			Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : ''
		);

		Utils::$context['preview_title'] = Icon::parse(Utils::$context['lp_tag']['icon']) . Utils::$context['preview_title'];
	}
}
