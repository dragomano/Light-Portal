<?php

namespace Bugo\LightPortal;

use Bugo\LightPortal\Front\ArticleInterface;
use Bugo\LightPortal\Front\BoardArticle;
use Bugo\LightPortal\Front\TopicArticle;
use Bugo\LightPortal\Front\PageArticle;

/**
 * FrontPage.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
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
	public static function show()
	{
		global $modSettings, $context, $scripturl, $txt;

		isAllowedTo('light_portal_view');

		$context['lp_need_lower_case'] = Helpers::isLowerCaseForDates();

		switch ($modSettings['lp_frontpage_mode']) {
			case 1:
				call_user_func(array(Page::class, 'show'));
				break;

			case 2:
				self::prepare(new TopicArticle);
				$context['sub_template'] = 'show_topics_as_articles';
				break;

			case 3:
				self::prepare(new PageArticle);
				$context['sub_template'] = 'show_pages_as_articles';
				break;

			default:
				self::prepare(new BoardArticle);
				$context['sub_template'] = 'show_boards_as_articles';
		}

		Subs::runAddons('frontCustomTemplate');

		$context['lp_frontpage_layout'] = self::getNumColumns();
		$context['canonical_url']       = $scripturl;

		loadTemplate('LightPortal/ViewFrontPage');

		$context['page_title'] = $modSettings['lp_frontpage_title'] ?: ($context['forum_name'] . ' - ' . $txt['lp_portal']);
		$context['linktree'][] = array(
			'name' => $txt['lp_portal']
		);
	}

	/**
	 * Form an array of articles
	 *
	 * Формируем массив статей
	 *
	 * @param ArticleInterface $article_entity
	 * @return void
	 */
	public static function prepare(ArticleInterface $article_entity)
	{
		global $modSettings, $context, $scripturl;

		$start = Helpers::request('start');
		$limit = $modSettings['lp_num_items_per_page'] ?? 12;

		$total_items = $article_entity::getTotal();

		if ($start >= $total_items) {
			send_http_status(404);

			$start = (floor(($total_items - 1) / $limit) + 1) * $limit - $limit;
		}

		$articles = $article_entity::getData($start, $limit);

		$articles = array_map(function ($article) use ($modSettings) {
			if (!empty($article['date'])) {
				$article['datetime'] = date('Y-m-d', $article['date']);
				$article['date']     = Helpers::getFriendlyTime($article['date']);
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
	 * Get the number columns for the frontpage layout
	 *
	 * Получаем количество колонок для макета главной страницы
	 *
	 * @return int
	 */
	public static function getNumColumns()
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