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

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\PageIndex;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Articles\ArticleInterface;
use Bugo\LightPortal\Articles\BoardArticle;
use Bugo\LightPortal\Articles\ChosenPageArticle;
use Bugo\LightPortal\Articles\ChosenTopicArticle;
use Bugo\LightPortal\Articles\PageArticle;
use Bugo\LightPortal\Articles\TopicArticle;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Events\HasEvents;
use Bugo\LightPortal\Renderers\RendererInterface;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasCache;
use Bugo\LightPortal\Utils\Traits\HasBreadcrumbs;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Bugo\LightPortal\Utils\Traits\HasResponse;
use Bugo\LightPortal\Utils\Traits\HasSession;
use Bugo\LightPortal\Utils\Weaver;
use WPLake\Typed\Typed;

use function abs;
use function array_column;
use function array_key_exists;
use function array_map;
use function date;
use function floor;
use function ltrim;
use function number_format;
use function sprintf;

use const LP_BASE_URL;

final class FrontPage implements ActionInterface
{
	use HasCache;
	use HasBreadcrumbs;
	use HasEvents;
	use HasRequest;
	use HasResponse;
	use HasSession;

	private array $modes = [
		'all_pages'     => PageArticle::class,
		'all_topics'    => TopicArticle::class,
		'chosen_boards' => BoardArticle::class,
		'chosen_pages'  => ChosenPageArticle::class,
		'chosen_topics' => ChosenTopicArticle::class,
	];

	public function __construct(private RendererInterface $renderer) {}

	public function show(): void
	{
		User::$me->isAllowedTo('light_portal_view');

		$this->events()->dispatch(PortalHook::frontModes, ['modes' => &$this->modes]);

		if (array_key_exists(Config::$modSettings['lp_frontpage_mode'], $this->modes)) {
			$this->prepare(new $this->modes[Config::$modSettings['lp_frontpage_mode']]);
		} elseif (Setting::isFrontpageMode('chosen_page')) {
			app(Page::class)->show();
			return;
		}

		Utils::$context['lp_frontpage_num_columns'] = $this->getNumColumns();

		Utils::$context['canonical_url'] = Config::$scripturl;

		Utils::$context['page_title'] = Config::$modSettings['lp_frontpage_title'] ?: (
			Utils::$context['forum_name'] . ' - ' . Lang::$txt['lp_portal']
		);

		$this->breadcrumbs()->add(
			Lang::$txt['lp_portal'],
			after: '(' . Lang::getTxt('lp_articles_set', [
				'articles' => Utils::$context['total_articles']
			]) . ')'
		);

		$this->prepareTemplates();
	}

	public function prepare(ArticleInterface $article): void
	{
		$start = Typed::int($this->request()->get('start'));
		$limit = Setting::get('lp_num_items_per_page', 'int', 12);

		$article->init();

		$key = "articles_{$start}_$limit";
		$key = ltrim(($this->request()->get('action') ?? '') . '_' . $key, '_');

		$data = $this->langCache($key)
			->setFallback(function () use ($article, $start, $limit) {
				$total = $article->getTotalCount();

				$this->updateStart($total, $start, $limit);

				$articles = app(Weaver::class)(static fn() => $article->getData($start, $limit));

				return ['total' => $total, 'articles' => $articles];
			});

		[$articles, $itemsCount] = [$data['articles'], $data['total']];

		Utils::$context['total_articles'] = $itemsCount;

		$articles = $this->postProcess($article, $articles);

		$this->preLoadImages($articles);

		Utils::$context['page_index'] = new PageIndex(
			LP_BASE_URL, $start, $itemsCount, $limit
		);

		Utils::$context['start'] = $this->request()->get('start');

		if (Setting::get('lp_use_simple_pagination', 'bool', false)) {
			Utils::$context['page_index'] = $this->simplePaginate(LP_BASE_URL, $itemsCount, $limit);
		}

		$start = (int) $this->request()->get('start');

		Utils::$context['portal_next_page'] = $start + $limit < $itemsCount
			? LP_BASE_URL . ';start=' . ($start + $limit)
			: '';

		Utils::$context['lp_frontpage_articles'] = $articles;

		$this->events()->dispatch(PortalHook::frontAssets);
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

		Utils::$context['lp_frontpage_layouts'] = $this->renderer->getLayouts();

		$this->prepareLayoutSwitcher();

		$currentLayout = Config::$modSettings['lp_frontpage_layout'] ?? $this->renderer::DEFAULT_TEMPLATE;

		$params = [
			'txt'         => Lang::$txt,
			'context'     => Utils::$context,
			'modSettings' => Config::$modSettings,
		];

		// You can add your own logic here
		$this->events()->dispatch(
			PortalHook::frontLayouts,
			[
				'renderer' => &$this->renderer,
				'layout'   => &$currentLayout,
				'params'   => &$params,
			]
		);

		Utils::$context['lp_layout_content'] = $this->renderer->render($currentLayout, $params);
	}

	public function prepareLayoutSwitcher(): void
	{
		if (empty(Config::$modSettings['lp_show_layout_switcher']))
			return;

		Utils::$context['template_layers'][] = 'layout_switcher';

		if ($this->session('lp')->isEmpty('frontpage_layout')) {
			Utils::$context['lp_current_layout'] = $this->request()->get('layout')
				?? Config::$modSettings['lp_frontpage_layout'] ?? $this->renderer::DEFAULT_TEMPLATE;
		} else {
			Utils::$context['lp_current_layout'] = $this->request()->get('layout')
				?? $this->session('lp')->get('frontpage_layout');
		}

		$this->session('lp')->put('frontpage_layout', Utils::$context['lp_current_layout']);

		Config::$modSettings['lp_frontpage_layout'] = $this->session('lp')->get('frontpage_layout');
	}

	public function getNumColumns(): int
	{
		$baseColumnsCount = 12;
		$userColumnsCount = Setting::get('lp_frontpage_num_columns', 'string', '');

		if (empty($userColumnsCount)) {
			return $baseColumnsCount;
		}

		return $baseColumnsCount / match ($userColumnsCount) {
			'1'     => 2,
			'2'     => 3,
			'3'     => 4,
			default => 6,
		};
	}

	public function updateStart(int $total, int &$start, int $limit): void
	{
		if ($start >= $total) {
			Utils::sendHttpStatus(404);

			$start = (floor(($total - 1) / $limit) + 1) * $limit - $limit;
		}

		$start = (int) abs($start);
	}

	private function postProcess(ArticleInterface $article, array $articles): array
	{
		return array_map(function ($item) use ($article) {
			if (Utils::$context['user']['is_guest']) {
				$item['is_new'] = false;
				$item['views']['num'] = 0;
			}

			if (isset($item['date'])) {
				$item['datetime'] = date('Y-m-d', $item['date']);
				$item['raw_date'] = $item['date'];
				$item['date']     = DateTime::relative($item['date']);
			}

			if (empty($item['image'])) {
				$item['image'] = Setting::get('lp_image_placeholder', 'string', '');
			}

			if (! empty($item['views']['num'])) {
				$item['views']['num'] = $this->getFriendlyNumber($item['views']['num']);
			}

			return $item;
		}, $articles);
	}

	private function preLoadImages(array $articles): void
	{
		$images = array_column($articles, 'image');

		foreach ($images as $image) {
			Utils::$context['html_headers'] .= "\n\t" . Str::html('link', [
				'rel'  => 'preload',
				'as'   => 'image',
				'href' => $image,
			]);
		}
	}

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

		$button = Str::html('a')->class('button')->href('%s;start=%s');

		if ($prev >= 0) {
			$title = Icon::get('arrow_left') . ' ' . Lang::$txt['prev'];
			$paginate .= sprintf($button->startTag(), $url, $prev) . $title . $button->endTag();
		}

		if ($next) {
			$title = Lang::$txt['next'] . ' ' . Icon::get('arrow_right');
			$paginate .= sprintf($button->startTag(), $url, $next) . $title . $button->endTag();
		}

		return $paginate;
	}
}
