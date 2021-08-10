<?php

/**
 * KarmaPostRating
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\KarmaPostRating;

use Bugo\LightPortal\Addons\Plugin;

class KarmaPostRating extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'article';

	/**
	 * Select rating column from kpr_ratings table for the frontpage topics
	 *
	 * Выбираем столбец rating из таблицы kpr_ratings при выборке тем-статей
	 *
	 * @param array $custom_columns
	 * @param array $custom_tables
	 * @param array $custom_wheres
	 * @param array $custom_params
	 * @return void
	 */
	public function frontTopics(array &$custom_columns, array &$custom_tables, array &$custom_wheres, array &$custom_params)
	{
		global $modSettings;

		if (!class_exists('\Bugo\KarmaPostRating\Subs'))
			return;

		$custom_columns[] = 'IF (kpr.rating_plus || kpr.rating_minus, kpr.rating_plus + kpr.rating_minus' . (!empty($modSettings['kpr_num_topics_factor'])
			 ? ' + t.num_replies' : '') . ', 0) AS rating';

		$custom_tables[] = 'LEFT JOIN {db_prefix}kpr_ratings AS kpr ON (t.id_first_msg = kpr.item_id AND kpr.item = {string:kpr_item_type})';

		$custom_params['kpr_item_type'] = 'message';
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
		$topics[$row['id_topic']]['kpr_rating'] = $row['rating'] ?? 0;
	}

	/**
	 * Show rating as stars
	 *
	 * Отображаем рейтинг в виде звёздочек
	 *
	 * @return void
	 */
	public function frontAssets()
	{
		global $context;

		if (empty($context['lp_frontpage_articles']))
			return;

		foreach ($context['lp_frontpage_articles'] as $id => $topic) {
			if (!empty($topic['kpr_rating'])) {
				$context['lp_frontpage_articles'][$id]['replies']['num'] .= ' <i class="fas fa-star"></i> ' . $topic['kpr_rating'];
			}
		}
	}
}
