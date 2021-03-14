<?php

namespace Bugo\LightPortal\Addons\Optimus;

use Bugo\LightPortal\Helpers;

/**
 * Optimus
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.7
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Optimus
{
	/**
	 * @var string
	 */
	public $addon_type = 'article';

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(&$config_vars)
	{
		$config_vars[] = array('check', 'lp_optimus_addon_use_topic_descriptions');
		$config_vars[] = array('check', 'lp_optimus_addon_show_topic_keywords');
	}

	/**
	 * Select optimus_description column from topics table for the frontpage topics
	 *
	 * Выбираем столбец optimus_description из таблицы topics при выборке тем-статей
	 *
	 * @param array $custom_columns
	 * @param array $custom_tables
	 * @return void
	 */
	public function frontTopics(&$custom_columns, &$custom_tables)
	{
		global $modSettings;

		if (empty($modSettings['lp_optimus_addon_use_topic_descriptions']) || !class_exists('\Bugo\Optimus\Integration'))
			return;

		$custom_columns[] = 't.optimus_description';
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
	public function frontTopicsOutput(&$topics, $row)
	{
		global $modSettings;

		if (!class_exists('\Bugo\Optimus\Integration'))
			return;

		if (!empty($modSettings['lp_optimus_addon_show_topic_keywords']))
			$topics[$row['id_topic']]['keywords'] = Helpers::cache('topic_keywords', 'getKeywords', __CLASS__, LP_CACHE_TIME, $row['id_topic']);

		if (!empty($modSettings['lp_optimus_addon_use_topic_descriptions']) && !empty($row['optimus_description']) && !empty($topics[$row['id_topic']]['teaser']))
			$topics[$row['id_topic']]['teaser'] = $row['optimus_description'];
	}

	/**
	 * Get all topic keywords
	 *
	 * Получаем все ключевые слова темы
	 *
	 * @param int $topic
	 * @return array
	 */
	public function getKeywords($topic)
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
			$keywords[$row['topic_id']][$row['id']] = $row['name'];
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $keywords[$topic] ?? [];
	}
}
