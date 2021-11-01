<?php

/**
 * TagList.php
 *
 * @package TagList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 26.10.21
 */

namespace Bugo\LightPortal\Addons\TagList;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class TagList extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'fas fa-tags';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		$options['tag_list']['parameters']['source']  = 'lp_tags';
		$options['tag_list']['parameters']['sorting'] = 'name';
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'tag_list')
			return;

		$parameters['source']  = FILTER_SANITIZE_STRING;
		$parameters['sorting'] = FILTER_SANITIZE_STRING;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'tag_list')
			return;

		$sources = array_combine(array('lp_tags', 'keywords'), $txt['lp_tag_list']['source_set']);

		if (!class_exists('\Bugo\Optimus\Keywords'))
			unset($sources['keywords']);

		$context['posting_fields']['source']['label']['text'] = $txt['lp_tag_list']['source'];
		$context['posting_fields']['source']['input'] = array(
			'type' => 'radio_select',
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

		$context['posting_fields']['sorting']['label']['text'] = $txt['lp_tag_list']['sorting'];
		$context['posting_fields']['sorting']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id' => 'sorting'
			),
			'options' => array()
		);

		$sortingSet = array_combine(array('name', 'frequency'), $txt['lp_tag_list']['sorting_set']);
		foreach ($sortingSet as $key => $value) {
			$context['posting_fields']['sorting']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['sorting']
			);
		}
	}

	/**
	 * Get all topic keywords
	 *
	 * Получаем ключевые слова всех тем
	 *
	 * @param string $sort
	 * @return array
	 */
	public function getAllTopicKeywords(string $sort = 'ok.name'): array
	{
		global $smcFunc, $scripturl;

		if (!class_exists('\Bugo\Optimus\Keywords'))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT ok.id, ok.name, COUNT(olk.keyword_id) AS frequency
			FROM {db_prefix}optimus_keywords AS ok
				INNER JOIN {db_prefix}optimus_log_keywords AS olk ON (ok.id = olk.keyword_id)
			GROUP BY ok.id, ok.name
			ORDER BY {raw:sort}',
			array(
				'sort' => $sort
			)
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
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $user_info, $txt;

		if ($type !== 'tag_list')
			return;

		if ($parameters['source'] == 'lp_tags') {
			$tag_list = Helpers::cache('tag_list_addon_b' . $block_id . '_u' . $user_info['id'])
				->setLifeTime($cache_time)
				->setFallback(\Bugo\LightPortal\Lists\Tag::class, 'getAll', ...array(0, 0, $parameters['sorting'] == 'name' ? 'value' : 'num DESC'));
		} else {
			$tag_list = Helpers::cache('tag_list_addon_b' . $block_id . '_u' . $user_info['id'])
				->setLifeTime($cache_time)
				->setFallback(__CLASS__, 'getAllTopicKeywords', $parameters['sorting'] == 'name' ? 'ok.name' : 'frequency DESC');
		}

		if (!empty($tag_list)) {
			foreach ($tag_list as $tag) {
				echo '
			<a class="button" href="', $tag['link'], '">', $tag['value'], ' <span class="amt">', $tag['frequency'], '</span></a>';
			}
		} else {
			echo $txt['lp_no_tags'];
		}
	}
}
