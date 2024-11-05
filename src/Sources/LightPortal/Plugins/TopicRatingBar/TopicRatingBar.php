<?php

/**
 * @package TopicRatingBar (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.11.24
 */

namespace Bugo\LightPortal\Plugins\TopicRatingBar;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class TopicRatingBar extends Plugin
{
	public string $type = 'article';

	/**
	 * Select total_votes and total_value columns from topic_ratings table for the frontpage topics
	 *
	 * Выбираем столбцы total_votes и total_value из таблицы topic_ratings при выборке тем-статей
	 */
	public function frontTopics(Event $e): void
	{
		if (! class_exists('TopicRatingBar'))
			return;

		$e->args->columns[] = 'tr.total_votes, tr.total_value';
		$e->args->tables[]  = 'LEFT JOIN {db_prefix}topic_ratings AS tr ON (t.id_topic = tr.id)';
	}

	/**
	 * Change some result data
	 *
	 * Меняем некоторые результаты выборки
	 */
	public function frontTopicsRow(Event $e): void
	{
		$e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes']) ? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
	}

	/**
	 * Show the rating of each topic
	 *
	 * Отображаем рейтинг каждой темы
	 */
	public function frontAssets(): void
	{
		foreach (Utils::$context['lp_frontpage_articles'] as $id => $topic) {
			if (! empty($topic['rating'])) {
				Utils::$context['lp_frontpage_articles'][$id]['replies']['after'] .= ' <i class="fas fa-star"></i> ' . $topic['rating'];
			}
		}
	}
}
