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

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\PageIndex;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Articles\ArticleInterface;
use LightPortal\Enums\FrontPageMode;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Renderers\RendererInterface;
use LightPortal\UI\TemplateLoader;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasBreadcrumbs;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasResponse;
use LightPortal\Utils\Traits\HasSorting;
use Ramsey\Collection\Collection;
use Ramsey\Collection\CollectionInterface;

use function LightPortal\app;

use const LP_BASE_URL;

if (! defined('SMF'))
	die('No direct access...');

class FrontPage implements ActionInterface
{
	use HasCache;
	use HasBreadcrumbs;
	use HasRequest;
	use HasResponse;
	use HasSorting;

	private ArticleInterface $article;

	private array $modes = [];

	public function __construct(
		private RendererInterface $renderer,
		private readonly EventDispatcherInterface $dispatcher
	)
	{
		foreach (FrontPageMode::cases() as $case) {
			if ($class = $case->getArticleClass()) {
				$this->modes[$case->value] = $class;
			}
		}

		$currentMode = Setting::get('lp_frontpage_mode', 'string', '');

		$this->dispatcher->dispatch(
			PortalHook::frontModes,
			[
				'modes'       => &$this->modes,
				'currentMode' => &$currentMode,
			]
		);

		$this->article = array_key_exists($currentMode, $this->modes)
			? app($this->modes[$currentMode])
			: app($this->modes[FrontPageMode::ALL_PAGES->value]);
	}

	public function show(): void
	{
		User::$me->isAllowedTo('light_portal_view');

		$this->prepareArticles();

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

	public function prepareArticles(): void
	{
		$start = Str::typed('int', $this->request()->get('start'));
		$limit = Setting::get('lp_num_items_per_page', 'int', 12);

		$this->article->init();

		$this->prepareSortingOptions($this->article);
		$this->prepareSorting('frontpage_sorting');

		$key = "articles_{$start}_{$limit}_" . Utils::$context['lp_current_sorting'];
		$key = ltrim(($this->request()->get('action') ?? '') . '_' . $key, '_');

		$data = $this->langCache($key)
			->setFallback(function () use ($start, $limit) {
				$total = $this->article->getTotalCount();

				$this->updateStart($total, $start, $limit);

				$articles = $this->article->getData($start, $limit, Utils::$context['lp_current_sorting']);
				$articles = iterator_to_array($articles);

				return ['total' => $total, 'articles' => $articles];
			});

		[$articlesData, $itemsCount] = [$data['articles'], $data['total']];

		$articles = new Collection('array', $articlesData);

		Utils::$context['total_articles'] = $itemsCount;

		$this->preLoadImages($articles);

		Utils::$context['page_index'] = new PageIndex(LP_BASE_URL, $start, $itemsCount, $limit);

		Utils::$context['start'] = $this->request()->get('start');

		if (Setting::get('lp_use_simple_pagination', 'bool', false)) {
			Utils::$context['page_index'] = $this->simplePaginate(LP_BASE_URL, $itemsCount, $limit);
		}

		$start = (int) $this->request()->get('start');

		Utils::$context['portal_next_page'] = $start + $limit < $itemsCount
			? LP_BASE_URL . ';start=' . ($start + $limit)
			: '';

		Utils::$context['lp_frontpage_articles'] = $articles->toArray();

		$this->dispatcher->dispatch(PortalHook::frontAssets);
	}

	public function prepareTemplates(): void
	{
		Utils::$context['lp_frontpage_layouts'] = $this->renderer->getLayouts();

		$this->prepareToolbar();

		$currentLayout = Config::$modSettings['lp_frontpage_layout'] ?? $this->renderer::DEFAULT_TEMPLATE;

		$params = [
			'txt'         => Lang::$txt,
			'context'     => Utils::$context,
			'modSettings' => Config::$modSettings,
		];

		$this->dispatcher->dispatch(
			PortalHook::frontLayouts,
			[
				'renderer' => &$this->renderer,
				'layout'   => &$currentLayout,
				'params'   => &$params,
			]
		);

		$content = $this->renderer->render($currentLayout, $params);

		TemplateLoader::fromFile($this->getCurrentTemplate(), compact('content'));
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

	private function prepareToolbar(): void
	{
		/* @uses template_lp_home_toolbar_above, template_lp_home_toolbar_below */
		Utils::$context['template_layers'][] = 'lp_home_toolbar';

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

	private function getCurrentTemplate(): string
	{
		return match (true) {
			empty(Utils::$context['lp_frontpage_articles']) => 'empty',
			empty(Config::$modSettings['lp_frontpage_layout']) => 'wrong',
			default => 'home',
		};
	}

	private function preLoadImages(CollectionInterface $articles): void
	{
		$images = array_filter($articles->column('image'));

		foreach ($images as $image) {
			Utils::$context['html_headers'] .= "\n\t" . Str::html('link', [
				'rel'  => 'preload',
				'as'   => 'image',
				'href' => $image,
			]);
		}
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
