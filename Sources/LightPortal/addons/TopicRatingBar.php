<?php

namespace Bugo\LightPortal\Addons;

/**
 * TopicRatingBar
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class TopicRatingBar
{
	/**
	 * Specifying the addon type (if 'block', you do not need to specify it)
	 *
	 * Указываем тип аддона (если 'block', то можно не указывать)
	 *
	 * @var array
	 */
	public static $addon_type = 'article';

	/**
	 * Select total_votes and total_value columns from topic_ratings table for the frontpage topics
	 *
	 * Выбираем столбцы total_votes и total_value из таблицы topic_ratings при выборке тем-статей
	 *
	 * @param array $custom_columns
	 * @param array $custom_tables
	 * @return void
	 */
	public static function frontTopics(&$custom_columns, &$custom_tables)
	{
		if (!class_exists('TopicRatingBar'))
			return;

		$custom_columns[] = 'tr.total_votes, tr.total_value';
		$custom_tables[]  = 'LEFT JOIN {db_prefix}topic_ratings AS tr ON (tr.id = t.id_topic)';
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
	public static function frontTopicsOutput(&$topics, $row)
	{
		global $modSettings;

		$rating = !empty($row['total_votes']) ? number_format($row['total_value'] / $row['total_votes'], 0) : 0;

		if (!empty($rating) && !empty($modSettings['lp_subject_size']))
			$topics[$row['id_topic']]['subject'] = shorten_subject($topics[$row['id_topic']]['subject'], $modSettings['lp_subject_size'] - $rating);

		$topics[$row['id_topic']]['rating'] = $rating;
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
			if (!empty($topic['rating'])) {
				$img = '';
				for ($i = 0; $i < $topic['rating']; $i++)
					$img .= '<span class="topic_stars">&nbsp;&nbsp;&nbsp;</span>';

				$js .= '
			let starImg' . $topic['id'] . ' = $(".catbg a[data-id=' . $topic['id'] . ']");
			starImg' . $topic['id'] . '.after(\'<span class="topic_stars_main">' . $img . '<\/span>\');';
			}
		}

		if (!empty($js))
			addInlineJavaScript('
		jQuery(document).ready(function ($) {
			' . $js . '
		});', true);
	}
}
