<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
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
use Bugo\LightPortal\EventManagerFactory;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Renderers\RendererInterface;
use Bugo\LightPortal\Utils\CacheTrait;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\SessionTrait;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;

use function abs;
use function array_column;
use function array_key_exists;
use function array_map;
use function call_user_func;
use function date;
use function floor;
use function ltrim;
use function number_format;
use function sprintf;

use const LP_BASE_URL;

final class FrontPage implements ActionInterface
{
	use CacheTrait;
	use RequestTrait;
	use SessionTrait;

	private array $modes = [
		'all_pages'     => PageArticle::class,
		'all_topics'    => TopicArticle::class,
		'chosen_boards' => BoardArticle::class,
		'chosen_pages'  => ChosenPageArticle::class,
		'chosen_topics' => ChosenTopicArticle::class,
	];

	private array $config;

	private RendererInterface $renderer;

	public function __construct()
	{
		$this->config = require dirname(__DIR__) . '/Settings/config.php';

		$this->renderer = new $this->config[RendererInterface::class];
	}

	public function show(): void
	{
		User::mustHavePermission('light_portal_view');

		(new EventManagerFactory())()->dispatch(
			PortalHook::frontModes,
			new Event(new class ($this->modes) {
				public function __construct(public array &$modes) {}
			})
		);

		if (array_key_exists(Config::$modSettings['lp_frontpage_mode'], $this->modes)) {
			$this->prepare(new $this->modes[Config::$modSettings['lp_frontpage_mode']]);
		} elseif (Setting::isFrontpageMode('chosen_page')) {
			call_user_func([new Page(), 'show']);
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

	public function prepare(ArticleInterface $article): void
	{
		$start = (int) $this->request('start');
		$limit = (int) Config::$modSettings['lp_num_items_per_page'] ?? 12;

		$article->init();

		$key = 'articles_u' . User::$info['id'] . '_' . User::$info['language'] . '_' . $start . '_' . $limit;

		$key = ltrim($this->request('action', '') . '_' . $key, '_');

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

		if (! empty(Config::$modSettings['lp_use_simple_pagination'])) {
			Utils::$context['page_index'] = $this->simplePaginate(LP_BASE_URL, $itemsCount, $limit);
		}

		Utils::$context['portal_next_page'] = $this->request('start') + $limit < $itemsCount
			? LP_BASE_URL . ';start=' . ($this->request('start') + $limit)
			: '';

		Utils::$context['lp_frontpage_articles'] = $articles;

		(new EventManagerFactory())()->dispatch(PortalHook::frontAssets);
	}

	public function getLayouts(): array
	{
		return $this->renderer->getLayouts();
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

		$currentLayout = Config::$modSettings['lp_frontpage_layout'] ?? $this->renderer::DEFAULT_TEMPLATE;

		$params = [
			'txt'         => Lang::$txt,
			'context'     => Utils::$context,
			'modSettings' => Config::$modSettings,
		];

		// You can add your own logic here
		(new EventManagerFactory())()->dispatch(
			PortalHook::frontLayouts,
			new Event(new class ($this->renderer, $currentLayout, $params) {
				public function __construct(
					public RendererInterface &$renderer,
					public string &$layout,
					public array &$params
				) {}
			})
		);

		Utils::$context['lp_layout_content'] = $this->renderer->render($currentLayout, $params);
	}

	public function prepareLayoutSwitcher(): void
	{
		if (empty(Config::$modSettings['lp_show_layout_switcher']))
			return;

		Utils::$context['template_layers'][] = 'layout_switcher';

		if ($this->session('lp')->isEmpty('frontpage_layout')) {
			Utils::$context['lp_current_layout'] = $this->request(
				'layout', Config::$modSettings['lp_frontpage_layout'] ?? $this->renderer::DEFAULT_TEMPLATE
			);
		} else {
			Utils::$context['lp_current_layout'] = $this->request(
				'layout', $this->session('lp')->get('frontpage_layout')
			);
		}

		$this->session('lp')->put('frontpage_layout', Utils::$context['lp_current_layout']);

		Config::$modSettings['lp_frontpage_layout'] = $this->session('lp')->get('frontpage_layout');
	}

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

			$item['msg_link'] ??= $item['link'];

			if (empty($item['image']) && ! empty(Config::$modSettings['lp_image_placeholder']))
				$item['image'] = Config::$modSettings['lp_image_placeholder'];

			if (! empty($item['views']['num']))
				$item['views']['num'] = $this->getFriendlyNumber($item['views']['num']);

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
