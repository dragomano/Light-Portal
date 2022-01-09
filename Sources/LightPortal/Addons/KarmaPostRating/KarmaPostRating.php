<?php

/**
 * KarmaPostRating.php
 *
 * @package KarmaPostRating (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 30.12.21
 */

namespace Bugo\LightPortal\Addons\KarmaPostRating;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class KarmaPostRating extends Plugin
{
	public string $type = 'article';

	public function frontTopics(array &$custom_columns, array &$custom_tables, array &$custom_wheres, array &$custom_params)
	{
		if (! class_exists('\Bugo\KarmaPostRating\Subs'))
			return;

		$custom_columns[] = 'IF (kpr.rating_plus || kpr.rating_minus, kpr.rating_plus + kpr.rating_minus' . (empty($this->modSettings['kpr_num_topics_factor']) ? '' : ' + t.num_replies') . ', 0) AS rating';

		$custom_tables[] = 'LEFT JOIN {db_prefix}kpr_ratings AS kpr ON (t.id_first_msg = kpr.item_id AND kpr.item = {string:kpr_item_type})';

		$custom_params['kpr_item_type'] = 'message';
	}

	public function frontTopicsOutput(array &$topics, array $row)
	{
		$topics[$row['id_topic']]['kpr_rating'] = $row['rating'] ?? 0;
	}

	public function frontAssets()
	{
		if (empty($this->context['lp_frontpage_articles']))
			return;

		foreach ($this->context['lp_frontpage_articles'] as $id => $topic) {
			if (! empty($topic['kpr_rating'])) {
				$this->context['lp_frontpage_articles'][$id]['replies']['after'] .= ' <i class="fas fa-star"></i> ' . $topic['kpr_rating'];
			}
		}
	}
}
