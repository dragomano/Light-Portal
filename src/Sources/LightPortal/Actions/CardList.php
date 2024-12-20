<?php

namespace Bugo\LightPortal\Actions;

use Bugo\Bricks\Tables\Column;
use Bugo\Bricks\Tables\Interfaces\TableBuilderInterface;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\PageIndex;
use Bugo\Compat\Utils;
use Bugo\LightPortal\UI\Tables\DateColumn;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Weaver;

class CardList implements CardListInterface
{
	use RequestTrait;

	public function show(PageListInterface $entity): void
	{
		if (empty(Config::$modSettings['lp_show_items_as_articles']))
			return;

		$start = (int) $this->request('start');
		$limit = Setting::get('lp_num_items_per_page', 'int', 12);

		$itemsCount = $entity->getTotalCount();

		$front = new FrontPage();
		$front->updateStart($itemsCount, $start, $limit);

		$sort     = $front->getOrderBy();
		$articles = (new Weaver())(static fn() => $entity->getPages($start, $limit, $sort));

		Utils::$context['page_index'] = new PageIndex(
			Utils::$context['canonical_url'], $start, $itemsCount, $limit
		);

		Utils::$context['start'] = $this->request()->get('start');

		Utils::$context['lp_frontpage_articles']    = $articles;
		Utils::$context['lp_frontpage_num_columns'] = $front->getNumColumns();

		Utils::$context['template_layers'][] = 'sorting';

		$front->prepareTemplates();

		Utils::obExit();
	}

	public function getBuilder(string $id): TableBuilderInterface
	{
		return PortalTableBuilder::make($id, Utils::$context['page_title'])
			->withParams(
				Setting::get('defaultMaxListItems', 'int', 50),
				defaultSortColumn: 'date'
			)
			->addColumns([
				DateColumn::make()
					->setData('date', 'centertext')
					->setSort('p.created_at DESC, p.updated_at DESC', 'p.created_at, p.updated_at'),
				TitleColumn::make()
					->setData(static fn($entry) => Str::html('a', $entry['title'])
						->class('bbc_link' . ($entry['is_front'] ? ' new_posts' : ''))
						->href($entry['is_front'] ? Config::$scripturl : (LP_PAGE_URL . $entry['slug'])), 'centertext'),
				Column::make('author', Lang::$txt['author'])
					->setData(static fn($entry) => empty($entry['author']['name'])
						? Lang::$txt['guest_title']
						: Str::html('a', $entry['author']['name'])
							->href($entry['author']['link']), 'centertext')
					->setSort('author_name DESC', 'author_name'),
				Column::make('num_views', Lang::$txt['views'])
					->setData(static fn($entry) => $entry['views']['num'], 'centertext')
					->setSort('p.num_views DESC', 'p.num_views'),
			]);
	}
}
