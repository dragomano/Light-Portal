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

namespace Bugo\LightPortal\Actions;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalSubAction;
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasRequest;

use function Bugo\LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

final class Category extends AbstractPageList
{
	use HasRequest;

	public function show(): void
	{
		if ($this->request()->hasNot('id')) {
			$this->showAll();
		}

		$category = [
			'id' => Str::typed('int', $this->request()->get('id'))
		];

		$categories = app(CategoryList::class)();
		if (array_key_exists($category['id'], $categories) === false) {
			Utils::$context['error_link'] = PortalSubAction::CATEGORIES->url();
			Lang::$txt['back'] = Lang::$txt['lp_all_categories'];
			ErrorHandler::fatalLang('lp_category_not_found', false, status: 404);
		}

		if ($category['id'] === 0) {
			Utils::$context['page_title'] = Lang::$txt['lp_all_pages_without_category'];
		} else {
			$category = $categories[$category['id']];
			Utils::$context['page_title'] = sprintf(Lang::$txt['lp_all_pages_with_category'], $category['title']);
		}

		Utils::$context['description'] = $category['description'] ?? '';
		Utils::$context['lp_category_edit_link'] = Config::$scripturl . '?action=admin;area=lp_categories;sa=edit;id=' . $category['id'];
		Utils::$context['canonical_url'] = PortalSubAction::CATEGORIES->url() . ';id=' . $category['id'];
		Utils::$context['robot_no_index'] = true;

		$this->breadcrumbs()
			->add(Lang::$txt['lp_all_categories'], PortalSubAction::CATEGORIES->url())
			->add($category['title'] ?? Lang::$txt['lp_no_category']);

		$this->cardList->show($this);

		Utils::obExit();
	}

	public function getPages(int $start, int $limit, string $sort): array
	{
		return $this->getPreparedResults(
			$this->repository->getPagesByCategory((int) $this->request()->get('id'), $start, $limit, $sort)
		);
	}

	public function getTotalPages(): int
	{
		return $this->repository->getTotalPagesByCategory((int) $this->request()->get('id'));
	}

	public function showAll(): void
	{
		Utils::$context['page_title']     = Lang::$txt['lp_all_categories'];
		Utils::$context['canonical_url']  = PortalSubAction::CATEGORIES->url();
		Utils::$context['robot_no_index'] = true;

		$this->breadcrumbs()->add(Utils::$context['page_title']);

		$this->getTablePresenter()->show(
			PortalTableBuilder::make('categories', Utils::$context['page_title'])
				->withParams(
					Setting::get('defaultMaxListItems', 'int', 50),
					Lang::$txt['lp_no_categories'],
					Utils::$context['canonical_url'],
					'title'
				)
				->setItems($this->getAll(...))
				->setCount($this->getTotalCount(...))
				->addColumns([
					Column::make('title', Lang::$txt['lp_category'])
						->setData(static fn($entry) => $entry['icon'] . ' ' . Str::html('a', $entry['title'])
							->href($entry['link']) . (empty($entry['description'])
								? ''
								: Str::html('p', $entry['description'])
							->class('smalltext')))
						->setSort('title DESC', 'title'),
					Column::make('num_pages', Lang::$txt['lp_total_pages_column'])
						->setStyle('width: 16%')
						->setData('num_pages', 'centertext')
						->setSort('frequency DESC', 'frequency'),
				])
		);

		Utils::obExit();
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'title'): array
	{
		return $this->repository->getCategoriesWithPageCount($start, $limit, $sort);
	}

	public function getTotalCount(): int
	{
		return $this->repository->getTotalCategoriesWithPages();
	}
}
