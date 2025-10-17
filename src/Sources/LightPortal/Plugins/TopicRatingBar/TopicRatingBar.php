<?php declare(strict_types=1);

/**
 * @package TopicRatingBar (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace Bugo\LightPortal\Plugins\TopicRatingBar;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Utils\Str;
use Laminas\Db\Sql\Select;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::ARTICLE)]
class TopicRatingBar extends Plugin
{
	public function frontTopics(Event $e): void
	{
		if (! class_exists('TopicRatingBar'))
			return;

		$e->args->joins[] = fn(Select $select) => $select->join(
			['tr' => 'topic_ratings'],
			't.id_topic = tr.id',
			['total_votes', 'total_value'],
			Select::JOIN_LEFT
		);
	}

	public function frontTopicsRow(Event $e): void
	{
		$e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
			? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
	}

	public function frontAssets(): void
	{
		foreach (Utils::$context['lp_frontpage_articles'] as $id => $topic) {
			if (! empty($topic['rating'])) {
				Utils::$context['lp_frontpage_articles'][$id]['replies']['after'] .= ' ' .
					Str::html('i', ['class' => 'fas fa-star']) . ' ' . $topic['rating'];
			}
		}
	}
}
