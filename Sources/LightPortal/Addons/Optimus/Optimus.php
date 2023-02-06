<?php

/**
 * Optimus.php
 *
 * @package Optimus (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 18.12.22
 */

namespace Bugo\LightPortal\Addons\Optimus;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class Optimus extends Plugin
{
	public string $type = 'article';

	public function addSettings(array &$config_vars)
	{
		$config_vars['optimus'][] = ['check', 'use_topic_descriptions'];
		$config_vars['optimus'][] = ['check', 'show_topic_keywords'];
	}

	public function frontTopics(array &$custom_columns)
	{
		if (empty($this->context['lp_optimus_plugin']['use_topic_descriptions']) || ! class_exists('\Bugo\Optimus\Integration'))
			return;

		$custom_columns[] = 't.optimus_description';
	}

	public function frontTopicsOutput(array &$topics, array $row)
	{
		if (! class_exists('\Bugo\Optimus\Integration'))
			return;

		if (! empty($this->context['lp_optimus_plugin']['show_topic_keywords']))
			$topics[$row['id_topic']]['tags'] = $this->cache('topic_keywords')->setFallback(__CLASS__, 'getKeywords', (int) $row['id_topic']);

		if (! empty($this->context['lp_optimus_plugin']['use_topic_descriptions']) && ! empty($row['optimus_description']) && ! empty($topics[$row['id_topic']]['teaser']))
			$topics[$row['id_topic']]['teaser'] = $row['optimus_description'];
	}

	public function getKeywords(int $topic): array
	{
		if (empty($topic))
			return [];

		$request = $this->smcFunc['db_query']('', /** @lang text */ '
			SELECT ok.id, ok.name, olk.topic_id
			FROM {db_prefix}optimus_keywords AS ok
				INNER JOIN {db_prefix}optimus_log_keywords AS olk ON (ok.id = olk.keyword_id)
			ORDER BY olk.topic_id, ok.id',
			[]
		);

		$keywords = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$keywords[$row['topic_id']][] = [
				'name' => $row['name'],
				'href' => $this->scripturl . '?action=keywords;id=' . $row['id']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $keywords[$topic] ?? [];
	}
}
