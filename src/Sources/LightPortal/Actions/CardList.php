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

namespace Bugo\LightPortal\Actions;

use Bugo\Bricks\Tables\Column;
use Bugo\Bricks\Tables\DateColumn;
use Bugo\Bricks\Tables\Interfaces\TableBuilderInterface;
use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\PageIndex;
use Bugo\Compat\Utils;
use Bugo\LightPortal\UI\Tables\PortalTableBuilder;
use Bugo\LightPortal\UI\Tables\TitleColumn;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Bugo\LightPortal\Utils\Weaver;
use WPLake\Typed\Typed;

class CardList implements CardListInterface
{
	use HasRequest;

	public function show(PageListInterface $entity): void
	{
		if (empty(Config::$modSettings['lp_show_items_as_articles']))
			return;

		$start = Typed::int($this->request()->get('start'));
		$limit = Setting::get('lp_num_items_per_page', 'int', 12);

		$itemsCount = $entity->getTotalPages();

		$front = app(FrontPage::class);
		$front->updateStart($itemsCount, $start, $limit);

		$articles = app(Weaver::class)(fn() => $entity->getPages($start, $limit, $this->getOrderBy()));

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

	public function getOrderBy(): string
	{
		$sortingTypes = [
			'title;desc'       => 't.value DESC',
			'title'            => 't.value',
			'created;desc'     => 'p.created_at DESC',
			'created'          => 'p.created_at',
			'updated;desc'     => 'p.updated_at DESC',
			'updated'          => 'p.updated_at',
			'author_name;desc' => 'author_name DESC',
			'author_name'      => 'author_name',
			'num_views;desc'   => 'p.num_views DESC',
			'num_views'        => 'p.num_views',
		];

		Utils::$context['current_sorting'] = $this->request()->get('sort') ?? 'created;desc';

		return $sortingTypes[Utils::$context['current_sorting']];
	}

	public function getBuilder(string $id): TableBuilderInterface
	{
		return PortalTableBuilder::make($id, Utils::$context['page_title'])
			->withParams(
				Setting::get('defaultMaxListItems', 'int', 50),
				defaultSortColumn: 'date'
			)
			->addColumns([
				DateColumn::make(title: Lang::$txt['date'])
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
