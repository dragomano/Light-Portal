<?php

namespace Bugo\LightPortal\Addons\TopicRatingBar;

/**
 * TopicRatingBar
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.2
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
	 * @var string
	 */
	public static $addon_type = 'article';

	/**
	 * Select total_votes and total_value columns from topic_ratings table for the frontpage topics
	 *
	 * Выбираем столбцы total_votes и total_value из таблицы topic_ratings при выборке тем-статей
	 *
	 * @param array $custom_columns
	 * @param array $custom_joins
	 * @return void
	 */
	public static function frontTopics(&$custom_columns, &$custom_joins)
	{
		if (!class_exists('TopicRatingBar'))
			return;

		$custom_columns[] = 'tr.total_votes, tr.total_value';
		$custom_joins['topic_ratings AS tr']  = ['t.id_topic = tr.id', 'left'];
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
		$topics[$row['id_topic']]['rating'] = !empty($row['total_votes']) ? number_format($row['total_value'] / $row['total_votes'], 0) : 0;
	}

	/**
	 * Show rating as stars
	 *
	 * @return void
	 */
	public static function frontpageAssets()
	{
		global $context;

		if (empty($context['lp_frontpage_articles']))
			return;

		foreach ($context['lp_frontpage_articles'] as $id => $topic) {
			if (!empty($topic['rating'])) {
				$context['lp_frontpage_articles'][$id]['num_replies'] .= ' <i class="fas fa-star"></i> ' . $topic['rating'];
			}
		}
	}
}
