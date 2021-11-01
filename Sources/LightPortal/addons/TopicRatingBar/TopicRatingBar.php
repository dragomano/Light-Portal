<?php

/**
 * TopicRatingBar.php
 *
 * @package TopicRatingBar (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 26.10.21
 */

namespace Bugo\LightPortal\Addons\TopicRatingBar;

use Bugo\LightPortal\Addons\Plugin;

class TopicRatingBar extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'article';

	/**
	 * Select total_votes and total_value columns from topic_ratings table for the frontpage topics
	 *
	 * Выбираем столбцы total_votes и total_value из таблицы topic_ratings при выборке тем-статей
	 *
	 * @param array $custom_columns
	 * @param array $custom_tables
	 * @return void
	 */
	public function frontTopics(array &$custom_columns, array &$custom_tables)
	{
		if (!class_exists('TopicRatingBar'))
			return;

		$custom_columns[] = 'tr.total_votes, tr.total_value';
		$custom_tables[]  = 'LEFT JOIN {db_prefix}topic_ratings AS tr ON (t.id_topic = tr.id)';
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
	public function frontTopicsOutput(array &$topics, array $row)
	{
		$topics[$row['id_topic']]['rating'] = !empty($row['total_votes']) ? number_format($row['total_value'] / $row['total_votes']) : 0;
	}

	/**
	 * Show the rating of each topic
	 *
	 * Отображаем рейтинг каждой темы
	 *
	 * @return void
	 */
	public function frontAssets()
	{
		global $context;

		if (empty($context['lp_frontpage_articles']))
			return;

		foreach ($context['lp_frontpage_articles'] as $id => $topic) {
			if (!empty($topic['rating'])) {
				$context['lp_frontpage_articles'][$id]['replies']['after'] .= ' <i class="fas fa-star"></i> ' . $topic['rating'];
			}
		}
	}
}
