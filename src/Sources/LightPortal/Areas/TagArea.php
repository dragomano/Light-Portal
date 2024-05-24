<?php declare(strict_types=1);

/**
 * TagArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas;

use Bugo\Compat\{Config, ErrorHandler, Lang, Security, Theme, Utils};
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Areas\Partials\IconSelect;
use Bugo\LightPortal\Areas\Validators\TagValidator;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Models\TagModel;
use Bugo\LightPortal\Repositories\TagRepository;
use Bugo\LightPortal\Utils\{Icon, ItemList};

if (! defined('SMF'))
	die('No direct access...');

final class TagArea
{
	use Area;
	use Helper;

	private TagRepository $repository;

	public function __construct()
	{
		$this->repository = new TagRepository();
	}

	public function main(): void
	{
		Utils::$context['page_title']  = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_tags_manage'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_tags';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_tags_manage_description'],
		];

		$this->doActions();

		$listOptions = [
			'id' => 'lp_tags',
			'items_per_page' => 20,
			'title' => Lang::$txt['lp_tags'],
			'no_items_label' => Lang::$txt['lp_no_items'],
			'base_href' => Utils::$context['form_action'],
			'default_sort_col' => 'title',
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
						'default' => 'tag_id',
						'reverse' => 'tag_id DESC'
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
							? '<a class="bbc_link" href="' . LP_BASE_URL . ';sa=tags;id=' . $entry['id'] . '">' . $entry['title'] . '</a>'
							: $entry['title'],
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
						'function' => static fn($entry) => /** @lang text */ '
							<div
								data-id="' . $entry['id'] . '"
								x-data="{ status: ' . ($entry['status'] === Status::ACTIVE->value ? 'true' : 'false') . ' }"
								x-init="$watch(\'status\', value => tag.toggleStatus($el))"
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
											<a href="' . Config::$scripturl . '?action=admin;area=lp_tags;sa=edit;id=' . $entry['id'] . '" class="button">' . Lang::$txt['modify'] . '</a>
										</li>
										<li>
											<a @click.prevent="showContextMenu = false; tag.remove($root)" class="button error">' . Lang::$txt['remove'] . '</a>
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
			'javascript' => 'const tag = new Tag();',
		];

		$listOptions['title'] = '
			<span class="floatright">
				<a href="' . Config::$scripturl . '?action=admin;area=lp_tags;sa=add;' . Utils::$context['session_var'] . '=' . Utils::$context['session_id'] . '" x-data>
					' . (str_replace(' class=', ' @mouseover="tag.toggleSpin($event.target)" @mouseout="tag.toggleSpin($event.target)" class=', Icon::get('plus', Lang::$txt['lp_tags_add']))) . '
				</a>
			</span>' . $listOptions['title'];

		new ItemList($listOptions);
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

		$this->prepareForumLanguages();
		$this->validateData();
		$this->prepareFormFields();
		$this->preparePreview();

		$this->repository->setData();
	}

	public function edit(): void
	{
		$item = (int) ($this->request('tag_id') ?: $this->request('id'));

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
			ErrorHandler::fatalLang('lp_tag_not_found', status: 404);
		}

		$this->prepareForumLanguages();

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

		if (isset($data['del_item']))
			$this->repository->remove([(int) $data['del_item']]);

		if (isset($data['toggle_item']))
			$this->repository->toggleStatus([(int) $data['toggle_item']], 'tag');

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

		$this->cleanBbcode($tag->titles);

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

		$this->cleanBbcode(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_title']);

		Utils::$context['page_title']    = Lang::$txt['preview'] . (
			Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : ''
		);

		Utils::$context['preview_title'] = $this->getIcon(Utils::$context['lp_tag']['icon']) . Utils::$context['preview_title'];
	}
}
