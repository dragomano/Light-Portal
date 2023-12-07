<?php declare(strict_types=1);

/**
 * FrontPage.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Entities;

use Bugo\LightPortal\Front\{
	ArticleInterface,
	BoardArticle,
	ChosenPageArticle,
	ChosenTopicArticle,
	PageArticle,
	TopicArticle,
};
use Bugo\LightPortal\Helper;
use Exception;
use IntlException;
use Latte\Engine;
use Latte\Essential\RawPhpExtension;
use Latte\Loaders\FileLoader;
use Latte\RuntimeException;

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

	/**
	 * @throws IntlException
	 */
	public function show()
	{
		$this->middleware('light_portal_view');

		$this->hook('frontModes', [&$this->modes]);

		if (array_key_exists($this->modSettings['lp_frontpage_mode'], $this->modes))
			$this->prepare(new $this->modes[$this->modSettings['lp_frontpage_mode']]);
		elseif ($this->modSettings['lp_frontpage_mode'] === 'chosen_page')
			return call_user_func([new Page, 'show']);

		$this->context['lp_frontpage_num_columns'] = $this->getNumColumns();

		$this->context['canonical_url'] = $this->scripturl;

		$this->context['page_title'] = $this->modSettings['lp_frontpage_title'] ?: ($this->context['forum_name'] . ' - ' . $this->txt['lp_portal']);
		$this->context['linktree'][] = [
			'name'        => $this->txt['lp_portal'],
			'extra_after' => '(' . $this->translate('lp_articles_set', ['articles' => $this->context['total_articles']]) . ')'
		];

		$this->prepareTemplates();

		return false;
	}

	/**
	 * @throws IntlException
	 */
	public function prepare(ArticleInterface $article): void
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

		[$articles, $itemsCount] = [$data['articles'], $data['total']];

		$this->context['total_articles'] = $itemsCount;

		$articles = $this->postProcess($article, $articles);

		$this->preLoadImages($articles);

		$this->context['page_index'] = $this->constructPageIndex(LP_BASE_URL, $this->request()->get('start'), $itemsCount, $limit);
		$this->context['start'] = $this->request()->get('start');

		if (! empty($this->modSettings['lp_use_simple_pagination']))
			$this->context['page_index'] = $this->simplePaginate(LP_BASE_URL, $itemsCount, $limit);

		$this->context['portal_next_page'] = $this->request('start') + $limit < $itemsCount ? LP_BASE_URL . ';start=' . ($this->request('start') + $limit) : '';
		$this->context['lp_frontpage_articles'] = $articles;

		$this->hook('frontAssets');
	}

	public function prepareTemplates(): void
	{
		if (empty($this->context['lp_frontpage_articles'])) {
			$this->context['sub_template'] = 'empty';
		} else {
			$this->context['sub_template'] = empty($this->modSettings['lp_frontpage_layout']) ? 'wrong_template' : 'layout';
		}

		// Mod authors can define their own templates
		$this->hook('frontCustomTemplate', [$this->getLayouts()]);

		$this->view($this->modSettings['lp_frontpage_layout']);
	}

	public function getLayouts(): array
	{
		$values = $titles = [];

		$this->loadTemplate('LightPortal/ViewFrontPage');

		$layouts = glob($this->settings['default_theme_dir'] . '/LightPortal/layouts/*.latte');
		$customs = glob($this->settings['default_theme_dir'] . '/portal_layouts/*.latte');
		$layouts = array_merge($layouts, $customs);

		foreach ($layouts as $layout) {
			$values[] = $title = basename($layout);
			$titles[] = $title === 'default.latte' ? $this->txt['lp_default'] : ucfirst(str_replace('.latte', '', $title));
		}

		$layouts = array_combine($values, $titles);
		$default = $layouts['default.latte'];
		unset($layouts['default.latte']);

		return array_merge(['default.latte' => $default], $layouts);
	}

	public function view(string $layout): void
	{
		if (empty($layout))
			return;

		$latte = new Engine;
		$latte->setTempDirectory(empty($this->modSettings['cache_enable']) ? null : $this->cachedir);
		$latte->setLoader(new FileLoader($this->settings['default_theme_dir'] . '/LightPortal/layouts/'));
		$latte->addExtension(new RawPhpExtension);
		$latte->addFunction('teaser', function (string $text, int $length = 150) use ($latte): string {
			$text = $latte->invokeFilter('stripHtml', [$text]);

			return $latte->invokeFilter('truncate', [$text, $length]);
		});
		$latte->addFunction('icon', function (string $name, string $title = '') use ($latte): string {
			$icon = $this->context['lp_icon_set'][$name];

			if (empty($title)) {
				return $icon;
			}

			return str_replace(' class=', ' title="' . $title . '" class=', $icon);
		});

		$params = [
			'txt'         => $this->txt,
			'context'     => $this->context,
			'modSettings' => $this->modSettings,
		];

		ob_start();

		try {
			$latte->render($layout, $params);
		} catch (RuntimeException $e) {
			if (is_file($this->settings['default_theme_dir'] . '/portal_layouts/' . $layout)) {
				$latte->setLoader(new FileLoader($this->settings['default_theme_dir'] . '/portal_layouts/'));
				$latte->render($layout, $params);
			} else {
				$this->fatalError($e->getMessage());
			}
		} catch (Exception $e) {
			$this->fatalError($e->getMessage());
		}

		$this->context['lp_layout'] = ob_get_clean();
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

		return $num_columns / match ($this->modSettings['lp_frontpage_num_columns']) {
			'1' => 2,
			'2' => 3,
			'3' => 4,
			default => 6,
		};
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

		$this->context['current_sorting'] = $this->request('sort', 'created;desc');

		return $sorting_types[$this->context['current_sorting']];
	}

	public function updateStart(int $total, int &$start, int $limit): void
	{
		if ($start >= $total) {
			$this->sendStatus(404);
			$start = (floor(($total - 1) / $limit) + 1) * $limit - $limit;
		}

		$start = (int) abs($start);
	}

	/**
	 * Post processing for articles
	 *
	 * Заключительная обработка статей
	 * @throws IntlException
	 */
	private function postProcess(ArticleInterface $article, array $articles): array
	{
		return array_map(function ($item) use ($article) {
			if ($this->context['user']['is_guest']) {
				$item['is_new'] = false;
				$item['views']['num'] = 0;
			}

			if (isset($item['date'])) {
				$item['datetime'] = date('Y-m-d', (int) $item['date']);
				$item['raw_date'] = $item['date'];
				$item['date']     = $this->getFriendlyTime((int) $item['date']);
			}

			$item['msg_link'] ??= $item['link'];

			if (is_array($item['title']) && $article instanceof PageArticle)
				$item['title'] = $this->getTranslatedTitle($item['title']);

			if (empty($item['image']) && ! empty($this->modSettings['lp_image_placeholder']))
				$item['image'] = $this->modSettings['lp_image_placeholder'];

			if (! empty($item['views']['num']))
				$item['views']['num'] = $this->getFriendlyNumber((int) $item['views']['num']);

			return $item;
		}, $articles);
	}

	private function preLoadImages(array $articles): void
	{
		$images = array_column($articles, 'image');

		foreach ($images as $image) {
			$this->context['html_headers'] .= "\n\t" . '<link rel="preload" as="image" href="' . $image . '">';
		}
	}

	/**
	 * Get a number in friendly format ("1K" instead "1000", etc)
	 *
	 * Получаем число в приятном глазу формате (для чисел более 10к)
	 */
	private function getFriendlyNumber(int $value = 0): string
	{
		if ($value < 10000)
			return (string) $value;

		$k   = 10 ** 3;
		$mil = 10 ** 6;
		$bil = 10 ** 9;

		if ($value >= $bil)
			return number_format($value / $bil, 1) . 'B';
		else if ($value >= $mil)
			return number_format($value / $mil, 1) . 'M';

		return number_format($value / $k, 1) . 'K';
	}

	private function simplePaginate(string $url, int $total, int $limit): string
	{
		$max_pages = (($total - 1) / $limit) * $limit;

		$prev = $this->context['start'] - $limit;

		$next = $this->context['start'] + $limit > $max_pages ? '' : $this->context['start'] + $limit;

		$paginate = '';

		if ($prev >= 0)
			$paginate .= "<a class=\"button\" href=\"$url;start=$prev\">{$this->context['lp_icon_set']['arrow_left']} {$this->txt['prev']}</a>";

		if ($next)
			$paginate .= "<a class=\"button\" href=\"$url;start=$next\">{$this->txt['next']} {$this->context['lp_icon_set']['arrow_right']}</a>";

		return $paginate;
	}
}
