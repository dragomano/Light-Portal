<?php

/**
 * TopTopics.php
 *
 * @package TopTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 31.12.21
 */

namespace Bugo\LightPortal\Addons\TopTopics;

use Bugo\LightPortal\Addons\Plugin;

class TopTopics extends Plugin
{
	public string $icon = 'fas fa-balance-scale-left';

	public function blockOptions(array &$options)
	{
		$options['top_topics']['parameters'] = [
			'popularity_type'   => 'replies',
			'num_topics'        => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'top_topics')
			return;

		$parameters['popularity_type']   = FILTER_SANITIZE_STRING;
		$parameters['num_topics']        = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'top_topics')
			return;

		$this->context['posting_fields']['popularity_type']['label']['text'] = $this->txt['lp_top_topics']['type'];
		$this->context['posting_fields']['popularity_type']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'popularity_type'
			],
			'options' => []
		];

		$types = array_combine(['replies', 'views'], $this->txt['lp_top_topics']['type_set']);

		foreach ($types as $key => $value) {
			$this->context['posting_fields']['popularity_type']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['popularity_type']
			];
		}

		$this->context['posting_fields']['num_topics']['label']['text'] = $this->txt['lp_top_topics']['num_topics'];
		$this->context['posting_fields']['num_topics']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'num_topics',
				'min'   => 1,
				'value' => $this->context['lp_block']['options']['parameters']['num_topics']
			]
		];

		$this->context['posting_fields']['show_numbers_only']['label']['text'] = $this->txt['lp_top_topics']['show_numbers_only'];
		$this->context['posting_fields']['show_numbers_only']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_numbers_only',
				'checked' => ! empty($this->context['lp_block']['options']['parameters']['show_numbers_only'])
			]
		];
	}

	public function getData(array $parameters): array
	{
		$this->loadSsi();

		return ssi_topTopics($parameters['popularity_type'], $parameters['num_topics'], 'array');
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'top_topics')
			return;

		$top_topics = $this->cache('top_topics_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData', $parameters);

		if (empty($top_topics))
			return;

		echo '
		<dl class="stats">';

		$max = $top_topics[0]['num_' . $parameters['popularity_type']];

		foreach ($top_topics as $topic) {
			if ($topic['num_' . $parameters['popularity_type']] < 1)
				continue;

			$width = $topic['num_' . $parameters['popularity_type']] * 100 / $max;

			echo '
			<dt>', $topic['link'], '</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($topic['num_' . $parameters['popularity_type']]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $topic['num_' . $parameters['popularity_type']] : __('lp_' . $parameters['popularity_type'] . '_set', [$parameters['popularity_type'] => $topic['num_' . $parameters['popularity_type']]])), '</span>
			</dd>';
		}

		echo '
		</dl>';
	}
}
