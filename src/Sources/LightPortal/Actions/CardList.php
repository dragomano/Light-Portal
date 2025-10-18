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

namespace LightPortal\Actions;

use Bugo\Compat\PageIndex;
use Bugo\Compat\Utils;
use LightPortal\Articles\PageArticle;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasSorting;

use function LightPortal\app;

class CardList implements CardListInterface
{
	use HasRequest;
	use HasSorting;

	public function show(PageListInterface $entity): void
	{
		$this->prepareSortingOptions(app(PageArticle::class));
		$this->prepareSorting('card_list_sorting');

		$start = Str::typed('int', $this->request()->get('start'));
		$limit = Setting::get('lp_num_items_per_page', 'int', 12);

		$itemsCount = $entity->getTotalPages();

		$front = app(FrontPage::class);
		$front->updateStart($itemsCount, $start, $limit);

		$articles = $entity->getPages($start, $limit, $this->getOrderBy());

		Utils::$context['page_index'] = new PageIndex(
			Utils::$context['canonical_url'], $start, $itemsCount, $limit
		);

		Utils::$context['start'] = $this->request()->get('start');

		Utils::$context['lp_frontpage_articles']    = $articles;
		Utils::$context['lp_frontpage_num_columns'] = $front->getNumColumns();

		/* @uses template_category_above, template_category_below */
		Utils::$context['template_layers'][] = 'category';

		$front->prepareTemplates();

		Utils::obExit();
	}

	public function getOrderBy(): string
	{
		$sortingTypes = [
			'title;desc'        => 'title DESC',
			'title'             => 'title',
			'created;desc'      => 'p.created_at DESC',
			'created'           => 'p.created_at',
			'updated;desc'      => 'p.updated_at DESC',
			'updated'           => 'p.updated_at',
			'author_name;desc'  => 'author_name DESC',
			'author_name'       => 'author_name',
			'num_views;desc'    => 'p.num_views DESC',
			'num_views'         => 'p.num_views',
			'last_comment;desc' => 'COALESCE(com.created_at, 0) DESC',
			'last_comment'      => 'COALESCE(com.created_at, 0)',
			'num_replies;desc'  => 'p.num_comments DESC',
			'num_replies'       => 'p.num_comments',
		];

		return $sortingTypes[Utils::$context['lp_current_sorting']] ?? 'p.created_at DESC';
	}
}
