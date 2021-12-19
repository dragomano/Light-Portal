<?php

/**
 * Optimus.php
 *
 * @package Optimus (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 16.12.21
 */

namespace Bugo\LightPortal\Addons\Optimus;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class Optimus extends Plugin
{
	public string $type = 'article';

	public function addSettings(array &$config_vars)
	{
		$config_vars['optimus'][] = array('check', 'use_topic_descriptions');
		$config_vars['optimus'][] = array('check', 'show_topic_keywords');
	}

	public function frontTopics(array &$custom_columns)
	{
		global $modSettings;

		if (empty($modSettings['lp_optimus_addon_use_topic_descriptions']) || ! class_exists('\Bugo\Optimus\Integration'))
			return;

		$custom_columns[] = 't.optimus_description';
	}

	public function frontTopicsOutput(array &$topics, array $row)
	{
		global $modSettings;

		if (! class_exists('\Bugo\Optimus\Integration'))
			return;

		if (! empty($modSettings['lp_optimus_addon_show_topic_keywords']))
			$topics[$row['id_topic']]['tags'] = Helper::cache('topic_keywords')->setFallback(__CLASS__, 'getKeywords', (int) $row['id_topic']);

		if (! empty($modSettings['lp_optimus_addon_use_topic_descriptions']) && ! empty($row['optimus_description']) && ! empty($topics[$row['id_topic']]['teaser']))
			$topics[$row['id_topic']]['teaser'] = $row['optimus_description'];
	}

	public function getKeywords(int $topic): array
	{
		global $smcFunc, $scripturl;

		if (empty($topic))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT ok.id, ok.name, olk.topic_id
			FROM {db_prefix}optimus_keywords AS ok
				INNER JOIN {db_prefix}optimus_log_keywords AS olk ON (ok.id = olk.keyword_id)
			ORDER BY olk.topic_id, ok.id',
			array()
		);

		$keywords = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$keywords[$row['topic_id']][] = array(
				'name' => $row['name'],
				'href' => $scripturl . '?action=keywords;id=' . $row['id']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $keywords[$topic] ?? [];
	}
}
