<?php

declare(strict_types = 1);

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

namespace Bugo\LightPortal;

use Bugo\LightPortal\Front\AbstractArticle;

final class FrontPage
{
	public function show()
	{
		global $modSettings, $context, $scripturl, $txt, $settings;

		isAllowedTo('light_portal_view');

		switch ($modSettings['lp_frontpage_mode']) {
			case 'chosen_page':
				return call_user_func(array(new Page, 'show'));

			case 'all_topics':
				$this->prepare('TopicArticle');
				break;

			case 'all_pages':
				$this->prepare('PageArticle');
				break;

			case 'chosen_boards':
				$this->prepare('BoardArticle');
				break;

			case 'chosen_topics':
				$this->prepare('ChosenTopicArticle');
				break;

			case 'chosen_pages':
			default:
				$this->prepare('ChosenPageArticle');
		}

		$context['lp_frontpage_num_columns'] = $this->getNumColumns();

		$context['canonical_url'] = $scripturl;

		$context['page_title'] = $modSettings['lp_frontpage_title'] ?: ($context['forum_name'] . ' - ' . $txt['lp_portal']);
		$context['linktree'][] = [
			'name'        => $txt['lp_portal'],
			'extra_after' => '(' . Helper::getPluralText($context['total_articles'], $txt['lp_articles_set']) . ')'
		];

		if (empty($context['lp_frontpage_articles'])) {
			$context['sub_template'] = 'empty';
		} else {
			$context['sub_template'] = empty($modSettings['lp_frontpage_layout']) ? 'wrong_template' : 'show_' . $modSettings['lp_frontpage_layout'];
		}

		// Mod authors can define their own template
		Addon::run('frontCustomTemplate');

		loadTemplate('LightPortal/ViewFrontPage');

		// Also, theme makers can load their own layouts from the special template file
		if (is_file($settings['theme_dir'] . '/CustomFrontPage.template.php'))
			loadTemplate('CustomFrontPage');

		obExit();
	}

	public function prepare(string $entity = '')
	{
		global $modSettings, $context, $scripturl;

		$classname = '\Bugo\LightPortal\Front\\' . $entity;

		if (! class_exists($classname))
			return;

		$entityClass = AbstractArticle::load($classname);

		if (! $entityClass instanceof AbstractArticle)
			return;

		$start = (int) Helper::request('start');
		$limit = (int) $modSettings['lp_num_items_per_page'] ?? 12;

		$entityClass->init();

		if (($data = Helper::cache()->get('articles_u' . $context['user']['id'] . '_' . $start . '_' . $limit)) === null) {
			$data['total'] = $entityClass->getTotalCount();

			$this->updateStart($data['total'], $start, $limit);

			$data['articles'] = $entityClass->getData($start, $limit);

			Helper::cache()->put('articles_u' . $context['user']['id'] . '_' . $start . '_' . $limit, $data);
		}

		[$articles, $total_items] = [$data['articles'], $data['total']];

		$context['total_articles'] = $total_items;

		$articles = $this->postProcess($entity, $articles);

		$context['page_index'] = constructPageIndex($scripturl . '?action=' . LP_ACTION, Helper::request()->get('start'), $total_items, $limit);
		$context['start'] = Helper::request()->get('start');

		if (! empty($modSettings['lp_use_simple_pagination']))
			$context['page_index'] = $this->simplePaginate($scripturl . '?action=' . LP_ACTION, $total_items, $limit);

		$context['portal_next_page'] = Helper::request('start') + $limit < $total_items ? $scripturl . '?action=' . LP_ACTION . ';start=' . (Helper::request('start') + $limit) : '';
		$context['lp_frontpage_articles'] = $articles;

		Addon::run('frontAssets');
	}

	/**
	 * Get the number columns for the frontpage layout
	 *
	 * Получаем количество колонок для макета главной страницы
	 */
	public function getNumColumns(): int
	{
		global $modSettings;

		$num_columns = 12;

		if (empty($modSettings['lp_frontpage_num_columns']))
			return $num_columns;

		switch ($modSettings['lp_frontpage_num_columns']) {
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
	 * Get available layouts of the frontpage
	 *
	 * Получаем доступные макеты главной страницы
	 */
	public static function getLayouts(): array
	{
		global $settings, $txt;

		$layouts = $values = [];

		$allFunctions = get_defined_functions()['user'];

		loadTemplate('LightPortal/ViewFrontPage');

		// Support of custom templates
		if (is_file($customTemplates = $settings['theme_dir'] . '/CustomFrontPage.template.php'))
			require_once $customTemplates;

		$frontPageFunctions = array_values(array_diff(get_defined_functions()['user'], $allFunctions));

		preg_match_all('/template_show_([a-z]+)(.*)/', implode("\n", $frontPageFunctions), $matches);

		if (! empty($matches[1])) {
			foreach ($matches[1] as $k => $v) {
				$layouts[] = $name = $v . ($matches[2][$k] ?? '');
				$values[]  = strpos($name, '_') === false ? $txt['lp_default'] : ucfirst(explode('_', $name)[1]);
			}

			$layouts = array_combine($layouts, $values);
		}

		return $layouts;
	}

	/**
	 * Get the formatted date for the portal cards
	 *
	 * Получаем отформатированную дату для карточек портала
	 */
	public function getCardDate(int $date): string
	{
		global $modSettings;

		if (empty($modSettings['lp_frontpage_time_format'])) {
			return Helper::getFriendlyTime($date);
		}

		if ($modSettings['lp_frontpage_time_format'] == 1) {
			return timeformat($date, true);
		}

		return date($modSettings['lp_frontpage_custom_time_format'] ?? 'F j, Y', $date);
	}

	/**
	 * Get the sort condition for SQL
	 *
	 * Получаем условие сортировки для SQL
	 */
	public function getOrderBy(): string
	{
		global $context;

		$sorting_types = array(
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
		);

		$context['current_sorting'] = Helper::post('sort', 'created;desc');

		return $sorting_types[$context['current_sorting']];
	}

	/**
	 * Post processing for articles
	 *
	 * Заключительная обработка статей
	 */
	private function postProcess(string $entity, array $articles): array
	{
		return array_map(function ($article) use ($entity) {
			global $context, $modSettings;

			if ($context['user']['is_guest'])
				$article['is_new'] = false;

			if (! empty($article['date'])) {
				$article['datetime'] = date('Y-m-d', (int) $article['date']);

				$article['date'] = $this->getCardDate((int) $article['date']);
			}

			$article['msg_link'] ??= $article['link'];

			if (isset($article['title']) && in_array($entity, ['PageArticle', 'ChosenPageArticle']))
				$article['title'] = Helper::getTranslatedTitle($article['title']);

			if (empty($article['image']) && ! empty($modSettings['lp_image_placeholder']))
				$article['image'] = $modSettings['lp_image_placeholder'];

			if (isset($article['views']['num']))
				$article['views']['num'] = Helper::getFriendlyNumber((int) $article['views']['num']);

			return $article;
		}, $articles);
	}

	private function simplePaginate(string $url, int $total, int $limit): string
    {
		global $context, $txt;

		$max_pages = (($total - 1) / $limit) * $limit;

		$prev = $context['start'] - $limit;

		$next = $context['start'] + $limit > $max_pages ? '' : $context['start'] + $limit;

		$paginate = '';

		if ($prev >= 0)
			$paginate .= '<i class="fas fa-arrow-left"></i> <a href="' . $url . ';start=' . $prev . '">' . $txt['prev'] . '</a>';

		if ($prev >= 0 && $next)
			$paginate .= ' <i class="fas fa-map-signs"></i> ';

		if ($next)
			$paginate .= '<a href="' . $url . ';start=' . $next . '">' . $txt['next'] . '</a> <i class="fas fa-arrow-right"></i>';

		return $paginate;
	}

	private function updateStart(int $total, int &$start, int $limit)
	{
		if ($start >= $total) {
			send_http_status(404);
			$start = (floor(($total - 1) / $limit) + 1) * $limit - $limit;
		}

		$start = abs($start);
	}
}
