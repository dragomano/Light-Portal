<?php

namespace Bugo\LightPortal\Addons;

/**
 * KarmaPostRating
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.9.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class KarmaPostRating
{
	/**
	 * Select rating column from kpr_ratings table for the frontpage topics
	 *
	 * Выбираем столбец rating из таблицы kpr_ratings при выборке тем-статей
	 *
	 * @param array $custom_columns
	 * @param array $custom_tables
	 * @param array $custom_wheres
	 * @param array $custom_parameters
	 * @return void
	 */
	public static function topicsAsArticles(&$custom_columns, &$custom_tables, &$custom_wheres, &$custom_parameters)
	{
		global $modSettings, $context;

		if (!class_exists('\Bugo\KarmaPostRating\Subs'))
			return;

		$custom_columns[] = 'IF (kpr.rating_plus || kpr.rating_minus, kpr.rating_plus + kpr.rating_minus' . (!empty($modSettings['kpr_num_topics_factor']) ? ' + t.num_replies' : '') . ', 0) AS rating';

		$custom_tables[] = 'LEFT JOIN {db_prefix}kpr_ratings AS kpr ON (kpr.item_id = t.id_first_msg AND kpr.item = "message")';
		$custom_wheres[] = !empty($context['kpr_ignored_boards']) ? 'AND t.id_board NOT IN ({array_int:kpr_ignored_boards})' : '';

		$custom_parameters['kpr_ignored_boards'] = !empty($context['kpr_ignored_boards']) ? $context['kpr_ignored_boards'] : null;
	}

	/**
	 * Change some result data
	 *
	 * Меняем некоторые результаты выборки
	 *
	 * @param array $topics
	 * @param array $row
	 * @return void
	 */
	public static function topicsAsArticlesResult(&$topics, $row)
	{
		global $modSettings;

		$rating = $row['rating'] ?? 0;

		if (!empty($rating) && !empty($modSettings['lp_subject_size']))
			$topics[$row['id_topic']]['subject'] = shorten_subject($topics[$row['id_topic']]['subject'], $modSettings['lp_subject_size'] - strlen((string) $rating));

		$topics[$row['id_topic']]['kpr_rating'] = $rating;
	}

	/**
	 * Add rating via jQuery
	 *
	 * @return void
	 */
	public static function frontpageAssets()
	{
		global $context;

		if (empty($context['lp_frontpage_articles']))
			return;

		$js = '';
		foreach ($context['lp_frontpage_articles'] as $topic) {
			if (!empty($topic['kpr_rating'])) {
				$js .= '
				let starImg' . $topic['id'] . ' = $(".catbg a[data-id=' . $topic['id'] . ']");
				starImg' . $topic['id'] . '.after(\'<span class="floatright"><span class="new_posts">' . $topic['kpr_rating'] . '<\/span><\/span>\');';
			}
		}

		if (!empty($js))
			addInlineJavaScript('
		jQuery(document).ready(function ($) {
			' . $js . '
		});', true);
	}
}
