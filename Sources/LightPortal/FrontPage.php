<?php

namespace Bugo\LightPortal;

use Bugo\LightPortal\Front\AbstractArticle;

/**
 * FrontPage.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.6
 */

class FrontPage
{
	/**
	 * Show articles on the portal frontpage
	 *
	 * Выводим статьи на главной странице портала
	 *
	 * @return void
	 */
	public function show()
	{
		global $context, $modSettings, $scripturl, $txt;

		isAllowedTo('light_portal_view');

		$context['lp_need_lower_case'] = $this->isLowerCaseForDates();

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
		$context['linktree'][] = array(
			'name'        => $txt['lp_portal'],
			'extra_after' => '(' . Helpers::getText($context['total_articles'], $txt['lp_articles_set']) . ')'
		);

		if (!empty($context['lp_frontpage_articles'])) {
			$context['sub_template'] = !empty($modSettings['lp_frontpage_layout']) ? 'show_' . $modSettings['lp_frontpage_layout'] : 'wrong_template';
		} else {
			$context['sub_template'] = 'empty';
		}

		// Mod authors can define their own template
		Subs::runAddons('frontCustomTemplate');

		loadTemplate('LightPortal/ViewFrontPage');

		obExit();
	}

	/**
	 * Form an array of articles
	 *
	 * Формируем массив статей
	 *
	 * @param string $entity
	 * @return void
	 */
	public function prepare(string $entity = '')
	{
		global $modSettings, $context, $scripturl;

		$classname = '\Bugo\LightPortal\Front\\' . $entity;

		if (!class_exists($classname))
			return;

		$entityClass = AbstractArticle::load($classname);

		if (!$entityClass instanceof AbstractArticle)
			return;

		$start = abs(Helpers::request('start'));
		$limit = $modSettings['lp_num_items_per_page'] ?? 12;

		$entityClass->init();

		$total_items = $context['total_articles'] = $entityClass->getTotalCount();

		if ($start >= $total_items) {
			send_http_status(404);
			$start = (floor(($total_items - 1) / $limit) + 1) * $limit - $limit;
		}

		$articles = $entityClass->getData($start, $limit);
		$articles = $this->postProcess($entity, $articles);

		$context['page_index'] = constructPageIndex($scripturl . '?action=portal', Helpers::request()->get('start'), $total_items, $limit);
		$context['start']      = Helpers::request()->get('start');

		$context['portal_next_page'] = Helpers::request('start') + $limit < $total_items ? $scripturl . '?action=portal;start=' . (Helpers::request('start') + $limit) : '';

		$context['lp_frontpage_articles'] = $articles;

		Subs::runAddons('frontAssets');
	}

	/**
	 * Check whether need to display dates in lowercase for the current language
	 *
	 * Проверяем, нужно ли для текущего языка отображать даты в нижнем регистре
	 */
	public function isLowerCaseForDates(): bool
	{
		global $txt;

		return in_array($txt['lang_dictionary'], ['pl', 'es', 'ru', 'uk']);
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
	public function getLayouts(): array
	{
		global $settings, $txt;

		$layouts = $values = [];

		$all_funcs = get_defined_functions()['user'];

		require_once($settings['default_theme_dir'] . '/LightPortal/ViewFrontPage.template.php');

		$fp_funcs = array_values(array_diff(get_defined_functions()['user'], $all_funcs));

		preg_match_all('/template_show_([a-z]+)(.*)/', implode("\n", $fp_funcs), $matches);

		if (!empty($matches[1])) {
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
	 *
	 * @param int $date
	 * @return string
	 */
	public function getCardDate($date): string
	{
		global $modSettings;

		if (!empty($modSettings['lp_frontpage_time_format'])) {
			if ($modSettings['lp_frontpage_time_format'] == 1) {
				$date = timeformat($date, true);
			} else {
				$date = date($modSettings['lp_frontpage_custom_time_format'] ?? 'F j, Y', $date);
			}
		} else {
			$date = Helpers::getFriendlyTime($date);
		}

		return $date;
	}

	/**
	 * Get the sort condition for SQL
	 *
	 * Получаем условие сортировки для SQL
	 *
	 * @return void
	 */
	public function getOrderBy()
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

		$context['current_sorting'] = Helpers::post('sort', 'created;desc');

		return $sorting_types[$context['current_sorting']];
	}

	/**
	 * Post processing for articles
	 *
	 * Заключительная обработка статей
	 *
	 * @param string $entity
	 * @param array $articles
	 * @return array
	 */
	private function postProcess($entity, $articles)
	{
		return array_map(function ($article) use ($entity) {
			global $context, $modSettings;

			if ($context['user']['is_guest'])
				$article['is_new'] = false;

			if (!empty($article['date'])) {
				$article['datetime'] = date('Y-m-d', $article['date']);

				$article['date'] = $this->getCardDate($article['date']);
			}

			$article['msg_link'] = $article['msg_link'] ?? $article['link'];

			if (isset($article['title']) && in_array($entity, ['PageArticle', 'ChosenPageArticle']))
				$article['title'] = Helpers::getTitle($article);

			if (empty($article['image']) && !empty($modSettings['lp_image_placeholder']))
				$article['image'] = $modSettings['lp_image_placeholder'];

			if (isset($article['views']['num']))
				$article['views']['num'] = Helpers::getFriendlyNumber($article['views']['num']);

			return $article;
		}, $articles);
	}
}