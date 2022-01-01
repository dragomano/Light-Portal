<?php

/**
 * TagList.php
 *
 * @package TagList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 31.12.21
 */

namespace Bugo\LightPortal\Addons\TagList;

use Bugo\LightPortal\Addons\Plugin;

class TagList extends Plugin
{
	public string $icon = 'fas fa-tags';

	public function blockOptions(array &$options)
	{
		$options['tag_list']['parameters']['source']  = 'lp_tags';
		$options['tag_list']['parameters']['sorting'] = 'name';
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'tag_list')
			return;

		$parameters['source']  = FILTER_SANITIZE_STRING;
		$parameters['sorting'] = FILTER_SANITIZE_STRING;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'tag_list')
			return;

		$sources = array_combine(['lp_tags', 'keywords'], $this->txt['lp_tag_list']['source_set']);

		if (! class_exists('\Bugo\Optimus\Keywords'))
			unset($sources['keywords']);

		$this->context['posting_fields']['source']['label']['text'] = $this->txt['lp_tag_list']['source'];
		$this->context['posting_fields']['source']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'source'
			],
			'options' => [],
			'tab' => 'content'
		];

		foreach ($sources as $key => $value) {
			$this->context['posting_fields']['source']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['source']
			];
		}

		$this->context['posting_fields']['sorting']['label']['text'] = $this->txt['lp_tag_list']['sorting'];
		$this->context['posting_fields']['sorting']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'sorting'
			],
			'options' => []
		];

		$sortingSet = array_combine(['name', 'frequency'], $this->txt['lp_tag_list']['sorting_set']);
		foreach ($sortingSet as $key => $value) {
			$this->context['posting_fields']['sorting']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['sorting']
			];
		}
	}

	public function getAllTopicKeywords(string $sort = 'ok.name'): array
	{
		if (!class_exists('\Bugo\Optimus\Keywords'))
			return [];

		$request = $this->smcFunc['db_query']('', '
			SELECT ok.id, ok.name, COUNT(olk.keyword_id) AS frequency
			FROM {db_prefix}optimus_keywords AS ok
				INNER JOIN {db_prefix}optimus_log_keywords AS olk ON (ok.id = olk.keyword_id)
			GROUP BY ok.id, ok.name
			ORDER BY {raw:sort}',
			[
				'sort' => $sort
			]
		);

		$keywords = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$keywords[] = [
				'value'     => $row['name'],
				'link'      => $this->scripturl . '?action=keywords;id=' . $row['id'],
				'frequency' => $row['frequency']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $keywords;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'tag_list')
			return;

		if ($parameters['source'] == 'lp_tags') {
			$tag_list = $this->cache('tag_list_addon_b' . $block_id . '_u' . $this->user_info['id'])
				->setLifeTime($cache_time)
				->setFallback(\Bugo\LightPortal\Lists\Tag::class, 'getAll', 0, 0, $parameters['sorting'] === 'name' ? 'value' : 'num DESC');
		} else {
			$tag_list = $this->cache('tag_list_addon_b' . $block_id . '_u' . $this->user_info['id'])
				->setLifeTime($cache_time)
				->setFallback(__CLASS__, 'getAllTopicKeywords', $parameters['sorting'] === 'name' ? 'ok.name' : 'frequency DESC');
		}

		if (! empty($tag_list)) {
			foreach ($tag_list as $tag) {
				echo '
			<a class="button" href="', $tag['link'], '">', $tag['value'], ' <span class="amt">', $tag['frequency'], '</span></a>';
			}
		} else {
			echo $this->txt['lp_no_tags'];
		}
	}
}
