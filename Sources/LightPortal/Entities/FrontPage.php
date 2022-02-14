<?php declare(strict_types=1);

/**
 * FrontPage.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Entities;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Front\{ArticleInterface, BoardArticle, PageArticle, TopicArticle, ChosenPageArticle, ChosenTopicArticle};

use function isAllowedTo;
use function loadTemplate;
use function send_http_status;

final class FrontPage
{
	use Helper;

	private array $modes = [
		'all_pages'     => PageArticle::class,
		'all_topics'    => TopicArticle::class,
		'chosen_boards' => BoardArticle::class,
		'chosen_pages'  => ChosenPageArticle::class,
		'chosen_topics' => ChosenTopicArticle::class,
	];

	public function show()
	{
		isAllowedTo('light_portal_view');

		if (array_key_exists($this->modSettings['lp_frontpage_mode'], $this->modes))
			$this->prepare(new $this->modes[$this->modSettings['lp_frontpage_mode']]);
		elseif ($this->modSettings['lp_frontpage_mode'] === 'chosen_page')
			return call_user_func([new Page, 'show']);

		$this->context['lp_frontpage_num_columns'] = $this->getNumColumns();

		$this->context['canonical_url'] = $this->scripturl;

		$this->context['page_title'] = $this->modSettings['lp_frontpage_title'] ?: ($this->context['forum_name'] . ' - ' . $this->txt['lp_portal']);
		$this->context['linktree'][] = [
			'name'        => $this->txt['lp_portal'],
			'extra_after' => '(' . __('lp_articles_set', ['articles' => $this->context['total_articles']]) . ')'
		];

		if (empty($this->context['lp_frontpage_articles'])) {
			$this->context['sub_template'] = 'empty';
		} else {
			$this->context['sub_template'] = empty($this->modSettings['lp_frontpage_layout']) ? 'wrong_template' : 'show_' . $this->modSettings['lp_frontpage_layout'];
		}

		// Mod authors can define their own template
		$this->hook('frontCustomTemplate');

		loadTemplate('LightPortal/ViewFrontPage');

		$this->addLazyLoadingForImages();

		// Also, theme makers can load their own layouts from the special template file
		if (is_file($this->settings['theme_dir'] . '/CustomFrontPage.template.php'))
			loadTemplate('CustomFrontPage');

		return false;
	}

	public function prepare(ArticleInterface $article)
	{
		$start = (int) $this->request('start');
		$limit = (int) $this->modSettings['lp_num_items_per_page'] ?? 12;

		$article->init();

		if (($data = $this->cache()->get('articles_u' . $this->context['user']['id'] . '_' . $start . '_' . $limit)) === null) {
			$data['total'] = $article->getTotalCount();

			$this->updateStart($data['total'], $start, $limit);

			$data['articles'] = $article->getData($start, $limit);

			$this->cache()->put('articles_u' . $this->context['user']['id'] . '_' . $start . '_' . $limit, $data);
		}

		[$articles, $total_items] = [$data['articles'], $data['total']];

		$this->context['total_articles'] = $total_items;

		$articles = $this->postProcess($article, $articles);

		$this->context['page_index'] = constructPageIndex(LP_BASE_URL, $this->request()->get('start'), $total_items, $limit);
		$this->context['start'] = $this->request()->get('start');

		if (! empty($this->modSettings['lp_use_simple_pagination']))
			$this->context['page_index'] = $this->simplePaginate(LP_BASE_URL, $total_items, $limit);

		$this->context['portal_next_page'] = $this->request('start') + $limit < $total_items ? LP_BASE_URL . ';start=' . ($this->request('start') + $limit) : '';
		$this->context['lp_frontpage_articles'] = $articles;

		$this->hook('frontAssets');
	}

	/**
	 * Get the number columns for the frontpage layout
	 *
	 * Получаем количество колонок для макета главной страницы
	 */
	public function getNumColumns(): int
	{
		$num_columns = 12;

		if (empty($this->modSettings['lp_frontpage_num_columns']))
			return $num_columns;

		switch ($this->modSettings['lp_frontpage_num_columns']) {
			case '1':
				$num_columns /= 2;
				break;
			case '2':
				$num_columns /= 3;
				break;
			case '3':
				$num_columns /= 4;
				break;
			default:
				$num_columns /= 6;
		}

		return $num_columns;
	}

	/**
	 * Get the sort condition for SQL
	 *
	 * Получаем условие сортировки для SQL
	 */
	public function getOrderBy(): string
	{
		$sorting_types = [
			'title;desc'       => 't.title DESC',
			'title'            => 't.title',
			'created;desc'     => 'p.created_at DESC',
			'created'          => 'p.created_at',
			'updated;desc'     => 'p.updated_at DESC',
			'updated'          => 'p.updated_at',
			'author_name;desc' => 'author_name DESC',
			'author_name'      => 'author_name',
			'num_views;desc'   => 'p.num_views DESC',
			'num_views'        => 'p.num_views'
		];

		$this->context['current_sorting'] = $this->post('sort', 'created;desc');

		return $sorting_types[$this->context['current_sorting']];
	}

	/**
	 * Post processing for articles
	 *
	 * Заключительная обработка статей
	 */
	private function postProcess(ArticleInterface $article, array $articles): array
	{
		return array_map(function ($item) use ($article) {
			if ($this->context['user']['is_guest'])
				$item['is_new'] = false;

			if ($item['date']) {
				$item['datetime'] = date('Y-m-d', (int) $item['date']);
				$item['date'] = $this->getFriendlyTime((int) $item['date']);
			}

			$item['msg_link'] ??= $item['link'];

			if (is_array($item['title']) && $article instanceof PageArticle)
				$item['title'] = $this->getTranslatedTitle($item['title']);

			if (empty($item['image']) && ! empty($this->modSettings['lp_image_placeholder']))
				$item['image'] = $this->modSettings['lp_image_placeholder'];

			if (isset($item['views']['num']))
				$item['views']['num'] = $this->getFriendlyNumber((int) $item['views']['num']);

			return $item;
		}, $articles);
	}

	private function simplePaginate(string $url, int $total, int $limit): string
	{
		$max_pages = (($total - 1) / $limit) * $limit;

		$prev = $this->context['start'] - $limit;

		$next = $this->context['start'] + $limit > $max_pages ? '' : $this->context['start'] + $limit;

		$paginate = '';

		if ($prev >= 0)
			$paginate .= '<a class="button" href="' . $url . ';start=' . $prev . '">' . $this->context['lp_icon_set']['arrow_left'] . ' ' . $this->txt['prev'] . '</a>';

		if ($next)
			$paginate .= '<a class="button" href="' . $url . ';start=' . $next . '">' . $this->txt['next'] . ' ' . $this->context['lp_icon_set']['arrow_right'] . '</a>';

		return $paginate;
	}

	private function updateStart(int $total, int &$start, int $limit)
	{
		if ($start >= $total) {
			send_http_status(404);
			$start = (floor(($total - 1) / $limit) + 1) * $limit - $limit;
		}

		$start = (int) abs($start);
	}
}
