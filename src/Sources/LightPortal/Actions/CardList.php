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

if (! defined('SMF'))
	die('No direct access...');

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

		$articles = $entity->getPages($start, $limit, Utils::$context['lp_current_sorting']);

		Utils::$context['page_index'] = new PageIndex(
			Utils::$context['canonical_url'], $start, $itemsCount, $limit
		);

		Utils::$context['start'] = $this->request()->get('start');

		Utils::$context['lp_frontpage_articles']    = $articles;
		Utils::$context['lp_frontpage_num_columns'] = $front->getNumColumns();

		/* @uses template_lp_list_above, template_lp_list_below */
		Utils::$context['template_layers'][] = 'lp_list';

		$front->prepareTemplates();

		Utils::obExit();
	}
}
