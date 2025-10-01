<?php declare(strict_types=1);

/**
 * @package TopicRatingBar (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 01.10.25
 */

namespace Bugo\LightPortal\Plugins\TopicRatingBar;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::ARTICLE)]
class TopicRatingBar extends Plugin
{
	#[HookAttribute(PortalHook::frontTopics)]
	public function frontTopics(Event $e): void
	{
		if (! class_exists('TopicRatingBar'))
			return;

		$e->args->columns[] = 'tr.total_votes, tr.total_value';

		$e->args->tables[] = 'LEFT JOIN {db_prefix}topic_ratings AS tr ON (t.id_topic = tr.id)';
	}

	#[HookAttribute(PortalHook::frontTopicsRow)]
	public function frontTopicsRow(Event $e): void
	{
		$e->args->articles[$e->args->row['id_topic']]['rating'] = empty($e->args->row['total_votes'])
			? 0 : (number_format($e->args->row['total_value'] / $e->args->row['total_votes']));
	}

	#[HookAttribute(PortalHook::frontAssets)]
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
