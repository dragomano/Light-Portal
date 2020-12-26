<?php

namespace Bugo\LightPortal;

use Bugo\LightPortal\Front\AbstractArticle;
use Bugo\LightPortal\Front\BoardArticle;
use Bugo\LightPortal\Front\ChosenPageArticle;
use Bugo\LightPortal\Front\ChosenTopicArticle;
use Bugo\LightPortal\Front\PageArticle;
use Bugo\LightPortal\Front\TopicArticle;

/**
 * FrontPage.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
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
			case 1:
				return call_user_func(array(new Page, 'show'));

			case 2:
				$this->prepare(new TopicArticle);
				$context['sub_template'] = 'show_topics_as_articles';
				break;

			case 3:
				$this->prepare(new PageArticle);
				$context['sub_template'] = 'show_pages_as_articles';
				break;

			case 4:
				$this->prepare(new BoardArticle);
				$context['sub_template'] = 'show_boards_as_articles';
				break;

			case 5:
				$this->prepare(new ChosenTopicArticle);
				$context['sub_template'] = 'show_topics_as_articles';
				break;

			default:
				$this->prepare(new ChosenPageArticle);
				$context['sub_template'] = 'show_pages_as_articles';
		}

		Subs::runAddons('frontCustomTemplate');

		$context['lp_frontpage_layout'] = $this->getNumColumns();
		$context['canonical_url']       = $scripturl;

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
	 * @param AbstractArticle $entity
	 * @return void
	 */
	public function prepare(AbstractArticle $entity)
	{
		global $modSettings, $context, $scripturl;

		$start = Helpers::request('start');
		$limit = $modSettings['lp_num_items_per_page'] ?? 12;

		$entity->init();

		$total_items = $entity->getTotalCount();

		if ($start >= $total_items) {
			send_http_status(404);

			$start = (floor(($total_items - 1) / $limit) + 1) * $limit - $limit;
		}

		$articles = $entity->getData($start, $limit);

		$articles = array_map(function ($article) use ($modSettings) {
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

		$context['lp_frontpage_articles'] = $articles;

		Subs::runAddons('frontAssets');
	}

	/**
	 * Check whether need to display dates in lowercase for the current language
	 *
	 * Проверяем, нужно ли для текущего языка отображать даты в нижнем регистре
	 *
	 * @return bool
	 */
	public function isLowerCaseForDates()
	{
		global $txt;

		return in_array($txt['lang_dictionary'], ['pl', 'es', 'ru', 'uk']);
	}

	/**
	 * Get the number columns for the frontpage layout
	 *
	 * Получаем количество колонок для макета главной страницы
	 *
	 * @return int
	 */
	public function getNumColumns()
	{
		global $modSettings;

		$num_columns = 12;

		if (!empty($modSettings['lp_frontpage_layout'])) {
			switch ($modSettings['lp_frontpage_layout']) {
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
		}

		return $num_columns;
	}
}