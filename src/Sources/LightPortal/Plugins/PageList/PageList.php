<?php declare(strict_types=1);

/**
 * @package PageList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 15.03.25
 */

namespace Bugo\LightPortal\Plugins\PageList;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\PortalSubAction;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Repositories\PageRepository;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\VirtualSelectField;
use Bugo\LightPortal\UI\Partials\EntryTypeSelect;
use Bugo\LightPortal\UI\Partials\CategorySelect;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\ParamWrapper;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use WPLake\Typed\Typed;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageList extends Block
{
	public string $icon = 'far fa-file-alt';

	private const SORTING_SET = [
		'page_id', 'author_name', 'title', 'slug', 'type', 'num_views', 'created_at', 'updated_at'
	];

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'categories' => '',
			'types'      => EntryType::DEFAULT->name(),
			'sort'       => 'page_id',
			'num_pages'  => 10,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'categories' => FILTER_DEFAULT,
			'types'      => FILTER_DEFAULT,
			'sort'       => FILTER_DEFAULT,
			'num_pages'  => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomField::make('categories', Lang::$txt['lp_categories'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new CategorySelect(), [
				'id'    => 'categories',
				'hint'  => $this->txt['categories_select'],
				'value' => $options['categories'] ?? '',
			]);

		CustomField::make('types', Lang::$txt['lp_page_type'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new EntryTypeSelect(), [
				'id'    => 'types',
				'value' => $options['types'] ?? '',
			]);

		VirtualSelectField::make('sort', $this->txt['sort'])
			->setOptions(array_combine(self::SORTING_SET, $this->txt['sort_set']))
			->setValue($options['sort']);

		NumberField::make('num_pages', $this->txt['num_pages'])
			->setDescription($this->txt['num_pages_subtext'])
			->setAttribute('min', 0)
			->setAttribute('max', 999)
			->setValue($options['num_pages']);
	}

	public function getData(ParamWrapper $parameters): array
	{
		$allCategories = app(CategoryList::class)();

		$categories = empty($parameters['categories']) ? null : explode(',', (string) $parameters['categories']);
		$sort = Typed::string($parameters['sort'], default: 'page_id');
		$numPages = Typed::int($parameters['num_pages'], default: 10);
		$type = Typed::string($parameters['types'], default: EntryType::DEFAULT->name());

		$queryString = '
	        AND p.status = {int:status}
	        AND p.deleted_at = 0
	        AND p.entry_type = {string:entry_type}
	        AND p.created_at <= {int:current_time}
	        AND p.permissions IN ({array_int:permissions})' . ($categories ? '
	        AND p.category_id IN ({array_int:categories})' : '');

		$queryParams = [
			'status'       => Status::ACTIVE->value,
			'entry_type'   => $type,
			'current_time' => time(),
			'permissions'  => Permission::all(),
			'categories'   => $categories,
		];

		$items = app(PageRepository::class)->getAll(
			0, $numPages, $sort . ' DESC', $queryString, $queryParams
		);

		$pages = [];
		foreach ($items as $row) {
			if (Setting::isFrontpage($row['slug'])) {
				continue;
			}

			$pages[$row['id']] = [
				'id'            => $row['id'],
				'category_id'   => $row['category_id'],
				'category_name' => $allCategories[$row['category_id']]['title'],
				'category_link' => PortalSubAction::CATEGORIES->url() . ';id=' . $row['category_id'],
				'title'         => $row['title'],
				'author_id'     => $row['author_id'],
				'author_name'   => $row['author_name'],
				'slug'          => $row['slug'],
				'num_views'     => $row['num_views'],
				'num_comments'  => $row['num_comments'],
				'created_at'    => $row['created_at'],
				'updated_at'    => $row['created_at'],
			];
		}

		return $pages;
	}

	public function prepareContent(Event $e): void
	{
		$pageList = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$me->id . '_' . User::$me->language)
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getData($e->args->parameters));

		if ($pageList) {
			$ul = Str::html('ul', ['class' => 'normallist page_list']);

			foreach ($pageList as $page) {
				if (empty($title = $page['title'])) {
					continue;
				}

				$li = Str::html('li');
				$link = Str::html('a', [
					'href' => Config::$scripturl . '?' . LP_PAGE_PARAM . '=' . $page['slug'],
				])->setText($title);

				$author = empty($page['author_id']) ? $page['author_name'] : Str::html('a', [
					'href' => Config::$scripturl . '?action=profile;u=' . $page['author_id'],
				])->setText($page['author_name']);

				$li->addHtml($link)
					->addHtml(' ' . Lang::$txt['by'] . ' ' . $author . ', ' . DateTime::relative($page['created_at']) . ' (')
					->addHtml(Lang::getTxt('lp_views_set', ['views' => $page['num_views']]));

				if ($page['num_comments'] && Setting::getCommentBlock() === 'default') {
					$li->addHtml(', ' . Lang::getTxt('lp_comments_set', ['comments' => $page['num_comments']]));
				}

				$li->addHtml(')');
				$ul->addHtml($li);
			}

			echo $ul;
		} else {
			echo Str::html('div', ['class' => 'errorbox'])->setText($this->txt['no_items']);
		}
	}
}
