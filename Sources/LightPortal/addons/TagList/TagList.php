<?php

namespace Bugo\LightPortal\Addons\TagList;

use Bugo\LightPortal\Helpers;

/**
 * TagList
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

class TagList
{
	/**
	 * @var string
	 */
	public $addon_icon = 'fas fa-tags';

	/**
	 * @var string
	 */
	private $source = 'lp_tags';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['tag_list']['parameters']['source'] = $this->source;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'tag_list')
			return;

		$parameters['source'] = FILTER_SANITIZE_STRING;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'tag_list')
			return;

		$sources = array_combine(array('lp_tags', 'keywords'), $txt['lp_tag_list_addon_source_set']);

		if (!class_exists('\Bugo\Optimus\Keywords'))
			$sources = $sources['lp_tags'];

		$context['posting_fields']['source']['label']['text'] = $txt['lp_tag_list_addon_source'];
		$context['posting_fields']['source']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'source'
			),
			'options' => array(),
			'tab' => 'content'
		);

		foreach ($sources as $key => $value) {
			$context['posting_fields']['source']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['source']
			);
		}
	}

	/**
	 * Get all topic keywords
	 *
	 * Получаем ключевые слова всех тем
	 *
	 * @return array
	 */
	public function getAllTopicKeywords()
	{
		global $smcFunc, $scripturl;

		if (!class_exists('\Bugo\Optimus\Keywords'))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT ok.id, ok.name, COUNT(olk.keyword_id) AS frequency
			FROM {db_prefix}optimus_keywords AS ok
				INNER JOIN {db_prefix}optimus_log_keywords AS olk ON (ok.id = olk.keyword_id)
			GROUP BY ok.id, ok.name
			ORDER BY ok.name DESC',
			array()
		);

		$keywords = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$keywords[] = array(
				'value'     => $row['name'],
				'link'      => $scripturl . '?action=keywords;id=' . $row['id'],
				'frequency' => $row['frequency']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $keywords;
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $user_info, $txt;

		if ($type !== 'tag_list')
			return;

		if ($parameters['source'] == 'lp_tags') {
			$tag_list = Helpers::cache(
				'tag_list_addon_b' . $block_id . '_u' . $user_info['id'], 'getAll', \Bugo\LightPortal\Tag::class, $cache_time, ...array(0, 0, 'value')
			);
		} else {
			$tag_list = Helpers::cache('tag_list_addon_b' . $block_id . '_u' . $user_info['id'], 'getAllTopicKeywords', __CLASS__, $cache_time);
		}

		ob_start();

		if (!empty($tag_list)) {
			foreach ($tag_list as $tag) {
				echo '
			<a class="button" href="', $tag['link'], '">', $tag['value'], ' <span class="amt">', $tag['frequency'], '</span></a>';
			}
		} else {
			echo $txt['lp_no_tags'];
		}

		$content = ob_get_clean();
	}
}
