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

		if (!empty($context['lp_frontpage_articles'])) {
			$context['sub_template'] = !empty($modSettings['lp_frontpage_layout']) ? 'show_' . $modSettings['lp_frontpage_layout'] : 'wrong_template';
		} else {
			$context['sub_template'] = 'empty';
		}

		// Mod authors can define their own template
		Subs::runAddons('frontCustomTemplate');

		$context['lp_frontpage_num_columns'] = $this->getNumColumns();

		$context['canonical_url'] = $scripturl;

		loadTemplate('LightPortal/ViewFrontPage');

		$context['page_title'] = $modSettings['lp_frontpage_title'] ?: ($context['forum_name'] . ' - ' . $txt['lp_portal']);
		$context['linktree'][] = array(
			'name' => $txt['lp_portal']
		);

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

		$entity = AbstractArticle::load($classname);

		if (!$entity instanceof AbstractArticle)
			return;

		$start = Helpers::request('start');
		$limit = $modSettings['lp_num_items_per_page'] ?? 12;

		$entity->init();

		$total_items = $entity->getTotalCount();

		if ($start >= $total_items) {
			send_http_status(404);

			$start = (floor(($total_items - 1) / $limit) + 1) * $limit - $limit;

			if ($start < 0)
				$start = 0;
		}

		$articles = $entity->getData($start, $limit);

		// Post processing for articles
		$articles = array_map(function ($article) use ($context, $modSettings) {
			if ($context['user']['is_guest'])
				$article['is_new'] = false;

			if (!empty($article['date'])) {
				$article['datetime'] = date('Y-m-d', $article['date']);

				if (!empty($modSettings['lp_frontpage_time_format'])) {
					if ($modSettings['lp_frontpage_time_format'] == 1) {
						$article['date'] = timeformat($article['date'], true);
					} else {
						$article['date'] = date($modSettings['lp_frontpage_custom_time_format'] ?? 'F j, Y', $article['date']);
					}
				} else {
					$article['date'] = Helpers::getFriendlyTime($article['date']);
				}
			}

			if (isset($article['title']))
				$article['title'] = Helpers::getTitle($article);

			if (empty($article['image']) && !empty($modSettings['lp_image_placeholder']))
				$article['image'] = $modSettings['lp_image_placeholder'];

			if (isset($article['num_views']))
				$article['num_views'] = Helpers::getFriendlyNumber($article['num_views']);

			return $article;
		}, $articles);

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
				$values[]  = $txt['lp_frontpage_layout_set'][explode('_', $v)[0]] . ' ~ ' .  $name;
			}

			$layouts = array_combine($layouts, $values);
		}

		return $layouts;
	}
}