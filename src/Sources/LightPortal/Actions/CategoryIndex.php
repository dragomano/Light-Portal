<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Actions;

use Bugo\Bricks\Tables\Column;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Repositories\CategoryIndexRepository;
use LightPortal\UI\Tables\PortalTableBuilder;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class CategoryIndex extends AbstractIndex
{
	public function __construct(CategoryIndexRepository $repository)
	{
		parent::__construct($repository);
	}

	public function show(): void
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
		return $this->repository->getAll($start, $limit, $sort);
	}

	public function getTotalCount(): int
	{
		return $this->repository->getTotalCount();
	}
}
