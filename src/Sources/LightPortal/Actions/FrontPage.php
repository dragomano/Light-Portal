<?php declare(strict_types=1);

/**
 * FrontPage.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Actions;

use Bugo\Compat\{Config, ErrorHandler, Lang, PageIndex, Sapi, Theme, User, Utils};
use Bugo\LightPortal\Articles\{ArticleInterface, BoardArticle, ChosenPageArticle};
use Bugo\LightPortal\Articles\{ChosenTopicArticle, PageArticle, TopicArticle};
use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\{DateTime, Icon};
use eftec\bladeone\BladeOne;
use Exception;
use IntlException;

final class FrontPage implements ActionInterface
{
	use Helper;

	public const DEFAULT_TEMPLATE = 'default.blade.php';

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
	public function show(): void
	{
		User::mustHavePermission('light_portal_view');

		$this->hook('frontModes', [&$this->modes]);

		if (array_key_exists(Config::$modSettings['lp_frontpage_mode'], $this->modes)) {
			$this->prepare(new $this->modes[Config::$modSettings['lp_frontpage_mode']]);
		} elseif ($this->isFrontpageMode('chosen_page')) {
			$this->callHelper([new Page(), 'show']);
			return;
		}

		Utils::$context['lp_frontpage_num_columns'] = $this->getNumColumns();

		Utils::$context['canonical_url'] = Config::$scripturl;

		Utils::$context['page_title'] = Config::$modSettings['lp_frontpage_title'] ?: (
			Utils::$context['forum_name'] . ' - ' . Lang::$txt['lp_portal']
		);

		Utils::$context['linktree'][] = [
			'name'        => Lang::$txt['lp_portal'],
			'extra_after' => '(' . Lang::getTxt('lp_articles_set', [
				'articles' => Utils::$context['total_articles']
			]) . ')'
		];

		$this->prepareTemplates();
	}

	/**
	 * @throws IntlException
	 */
	public function prepare(ArticleInterface $article): void
	{
		$start = (int) $this->request('start');
		$limit = (int) Config::$modSettings['lp_num_items_per_page'] ?? 12;

		$article->init();

		$key = 'articles_u' . Utils::$context['user']['id'] . '_' . $start . '_' . $limit;

		if (($data = $this->cache()->get($key)) === null) {
			$data['total'] = $article->getTotalCount();

			$this->updateStart($data['total'], $start, $limit);

			$data['articles'] = $article->getData($start, $limit);

			$this->cache()->put($key, $data);
		}

		[$articles, $itemsCount] = [$data['articles'], $data['total']];

		Utils::$context['total_articles'] = $itemsCount;

		$articles = $this->postProcess($article, $articles);

		$this->preLoadImages($articles);

		Utils::$context['page_index'] = new PageIndex(
			LP_BASE_URL, $start, $itemsCount, $limit
		);

		Utils::$context['start'] = $this->request()->get('start');

		if (! empty(Config::$modSettings['lp_use_simple_pagination']))
			Utils::$context['page_index'] = $this->simplePaginate(LP_BASE_URL, $itemsCount, $limit);

		Utils::$context['portal_next_page'] = $this->request('start') + $limit < $itemsCount
			? LP_BASE_URL . ';start=' . ($this->request('start') + $limit)
			: '';

		Utils::$context['lp_frontpage_articles'] = $articles;

		$this->hook('frontAssets');
	}

	public function prepareTemplates(): void
	{
		if (empty(Utils::$context['lp_frontpage_articles'])) {
			Utils::$context['sub_template'] = 'empty';
		} else {
			Utils::$context['sub_template'] = empty(Config::$modSettings['lp_frontpage_layout'])
				? 'wrong_template'
				: 'layout';
		}

		Utils::$context['lp_frontpage_layouts'] = $this->getLayouts();

		$this->prepareLayoutSwitcher();

		// Mod authors can use their own logic here
		$this->hook('frontLayouts');

		$this->view(Config::$modSettings['lp_frontpage_layout']);
	}

	public function prepareLayoutSwitcher(): void
	{
		if (empty(Config::$modSettings['lp_show_layout_switcher']))
			return;

		Utils::$context['template_layers'][] = 'layout_switcher';

		if ($this->session()->isEmpty('lp_frontpage_layout')) {
			Utils::$context['lp_current_layout'] = $this->request(
				'layout', Config::$modSettings['lp_frontpage_layout'] ?? self::DEFAULT_TEMPLATE
			);
		} else {
			Utils::$context['lp_current_layout'] = $this->request(
				'layout', $this->session()->get('lp_frontpage_layout')
			);
		}

		$this->session()->put('lp_frontpage_layout', Utils::$context['lp_current_layout']);

		Config::$modSettings['lp_frontpage_layout'] = $this->session()->get('lp_frontpage_layout');
	}

	public function getLayouts(): array
	{
		Theme::loadTemplate('LightPortal/ViewFrontPage');

		$layouts = glob(Theme::$current->settings['default_theme_dir'] . '/LightPortal/layouts/*.blade.php');

		$extensions = ['.blade.php'];

		// Mod authors can add custom extensions for layouts
		$this->hook('customLayoutExtensions', [&$extensions]);

		foreach ($extensions as $extension) {
			$layouts = array_merge(
				$layouts,
				glob(Theme::$current->settings['default_theme_dir'] . '/portal_layouts/*' . $extension)
			);
		}

		$values = $titles = [];

		foreach ($layouts as $layout) {
			$values[] = $title = basename($layout);

			$shortName = ucfirst(strstr($title, '.', true) ?: $title);

			$titles[] = $title === self::DEFAULT_TEMPLATE
				? Lang::$txt['lp_default']
				: str_replace('_', ' ', $shortName);
		}

		$layouts = array_combine($values, $titles);
		$default = $layouts[self::DEFAULT_TEMPLATE];
		unset($layouts[self::DEFAULT_TEMPLATE]);

		return array_merge([self::DEFAULT_TEMPLATE => $default], $layouts);
	}

	public function view(string $layout): void
	{
		if (empty($layout))
			return;

		$params = [
			'txt'         => Lang::$txt,
			'context'     => Utils::$context,
			'modSettings' => Config::$modSettings,
		];

		$templates = [
			Theme::$current->settings['default_theme_dir'] . '/LightPortal/layouts',
			Theme::$current->settings['default_theme_dir'] . '/portal_layouts',
		];

		ob_start();

		try {
			$blade = new BladeOne($templates, Sapi::getTempDir());

			$blade->directiveRT('icon', static function (array|string $expression) {
				if (is_array($expression)) {
					[$name, $title] = count($expression) > 1 ? $expression : [$expression[0], false];
				} else {
					$name = $expression;
				}

				$icon = Icon::get($name);

				if (empty($title)) {
					echo $icon;
					return;
				}

				echo str_replace(' class=', ' title="' . $title . '" class=', $icon);
			});

			$layout = strstr(
				Config::$modSettings['lp_frontpage_layout'], '.', true
			) ?: Config::$modSettings['lp_frontpage_layout'];

			echo $blade->run($layout, $params);
		} catch (Exception $e) {
			ErrorHandler::fatal($e->getMessage());
		}

		Utils::$context['lp_layout'] = ob_get_clean();
	}

	/**
	 * Get the number columns for the frontpage layout
	 *
	 * Получаем количество колонок для макета главной страницы
	 */
	public function getNumColumns(): int
	{
		$columnsCount = 12;

		if (empty(Config::$modSettings['lp_frontpage_num_columns']))
			return $columnsCount;

		return $columnsCount / match (Config::$modSettings['lp_frontpage_num_columns']) {
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
		$sortingTypes = [
			'title;desc'       => 't.title DESC',
			'title'            => 't.title',
			'created;desc'     => 'p.created_at DESC',
			'created'          => 'p.created_at',
			'updated;desc'     => 'p.updated_at DESC',
			'updated'          => 'p.updated_at',
			'author_name;desc' => 'author_name DESC',
			'author_name'      => 'author_name',
			'num_views;desc'   => 'p.num_views DESC',
			'num_views'        => 'p.num_views',
		];

		Utils::$context['current_sorting'] = $this->request('sort', 'created;desc');

		return $sortingTypes[Utils::$context['current_sorting']];
	}

	public function updateStart(int $total, int &$start, int $limit): void
	{
		if ($start >= $total) {
			Utils::sendHttpStatus(404);
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
			if (Utils::$context['user']['is_guest']) {
				$item['is_new'] = false;
				$item['views']['num'] = 0;
			}

			if (isset($item['date'])) {
				$item['datetime'] = date('Y-m-d', (int) $item['date']);
				$item['raw_date'] = $item['date'];
				$item['date']     = DateTime::relative((int) $item['date']);
			}

			$item['msg_link'] ??= $item['link'];

			if (empty($item['image']) && ! empty(Config::$modSettings['lp_image_placeholder']))
				$item['image'] = Config::$modSettings['lp_image_placeholder'];

			if (! empty($item['views']['num']))
				$item['views']['num'] = $this->getFriendlyNumber((int) $item['views']['num']);

			return $item;
		}, $articles);
	}

	private function preLoadImages(array $articles): void
	{
		$images = array_column($articles, 'image');

		foreach ($images as $image) {
			Utils::$context['html_headers'] .= "\n\t" . '<link rel="preload" as="image" href="' . $image . '">';
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

		if ($value >= $bil) {
			return number_format($value / $bil, 1) . 'B';
		} elseif ($value >= $mil) {
			return number_format($value / $mil, 1) . 'M';
		}

		return number_format($value / $k, 1) . 'K';
	}

	private function simplePaginate(string $url, int $total, int $limit): string
	{
		$maxPages = (($total - 1) / $limit) * $limit;

		$prev = Utils::$context['start'] - $limit;

		$next = Utils::$context['start'] + $limit > $maxPages ? '' : Utils::$context['start'] + $limit;

		$paginate = '';

		if ($prev >= 0) {
			$title = Icon::get('arrow_left') . ' ' . Lang::$txt['prev'];
			$paginate .= sprintf('<a class="button" href="%s;start=%s">', $url, $prev) . $title . "</a>";
		}

		if ($next) {
			$title = Lang::$txt['next'] . ' ' . Icon::get('arrow_right');
			$paginate .= sprintf('<a class="button" href="%s;start=%s">', $url, $next) . $title . "</a>";
		}

		return $paginate;
	}
}
