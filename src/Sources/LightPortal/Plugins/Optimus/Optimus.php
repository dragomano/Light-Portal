<?php

/**
 * @package Optimus (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 12.11.24
 */

namespace Bugo\LightPortal\Plugins\Optimus;

use Bugo\Compat\{Config, Db};
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class Optimus extends Plugin
{
	public string $type = 'article';

	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name][] = ['check', 'use_topic_descriptions'];
		$e->args->settings[$this->name][] = ['check', 'show_topic_keywords'];
	}

	public function frontTopics(Event $e): void
	{
		if (
			empty($this->context['use_topic_descriptions'])
			|| ! class_exists('\Bugo\Optimus\Integration')
		) {
			return;
		}

		$e->args->columns[] = 't.optimus_description';
	}

	public function frontTopicsRow(Event $e): void
	{
		if (! class_exists('\Bugo\Optimus\Integration'))
			return;

		$topics = &$e->args->articles;
		$row = $e->args->row;

		if (! empty($this->context['show_topic_keywords'])) {
			$topics[$row['id_topic']]['tags'] = $this->cache('topic_keywords')
				->setFallback(self::class, 'getKeywords', (int) $row['id_topic']);
		}

		if (
			! empty($this->context['use_topic_descriptions'])
			&& ! empty($row['optimus_description'])
			&& ! empty($topics[$row['id_topic']]['teaser'])
		) {
			$topics[$row['id_topic']]['teaser'] = $row['optimus_description'];
		}
	}

	public function getKeywords(int $topic): array
	{
		if (empty($topic))
			return [];

		$result = Db::$db->query('', /** @lang text */ '
			SELECT ok.id, ok.name, olk.topic_id
			FROM {db_prefix}optimus_keywords AS ok
				INNER JOIN {db_prefix}optimus_log_keywords AS olk ON (ok.id = olk.keyword_id)
			ORDER BY olk.topic_id, ok.id',
		);

		$keywords = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$keywords[$row['topic_id']][] = [
				'name' => $row['name'],
				'href' => Config::$scripturl . '?action=keywords;id=' . $row['id'],
			];
		}

		Db::$db->free_result($result);

		return $keywords[$topic] ?? [];
	}
}
