<?php

/**
 * Optimus.php
 *
 * @package Optimus (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.02.24
 */

namespace Bugo\LightPortal\Addons\Optimus;

use Bugo\Compat\{Config, Utils};
use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class Optimus extends Plugin
{
	public string $type = 'article';

	public function addSettings(array &$settings): void
	{
		$settings['optimus'][] = ['check', 'use_topic_descriptions'];
		$settings['optimus'][] = ['check', 'show_topic_keywords'];
	}

	public function frontTopics(array &$columns): void
	{
		if (
			empty(Utils::$context['lp_optimus_plugin']['use_topic_descriptions'])
			|| ! class_exists('\Bugo\Optimus\Integration')
		) {
			return;
		}

		$columns[] = 't.optimus_description';
	}

	public function frontTopicsOutput(array &$topics, array $row): void
	{
		if (! class_exists('\Bugo\Optimus\Integration'))
			return;

		if (! empty(Utils::$context['lp_optimus_plugin']['show_topic_keywords']))
			$topics[$row['id_topic']]['tags'] = $this->cache('topic_keywords')
				->setFallback(self::class, 'getKeywords', (int) $row['id_topic']);

		if (
			! empty(Utils::$context['lp_optimus_plugin']['use_topic_descriptions'])
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

		$result = Utils::$smcFunc['db_query']('', /** @lang text */ '
			SELECT ok.id, ok.name, olk.topic_id
			FROM {db_prefix}optimus_keywords AS ok
				INNER JOIN {db_prefix}optimus_log_keywords AS olk ON (ok.id = olk.keyword_id)
			ORDER BY olk.topic_id, ok.id',
			[]
		);

		$keywords = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$keywords[$row['topic_id']][] = [
				'name' => $row['name'],
				'href' => Config::$scripturl . '?action=keywords;id=' . $row['id'],
			];
		}

		Utils::$smcFunc['db_free_result']($result);

		return $keywords[$topic] ?? [];
	}
}
