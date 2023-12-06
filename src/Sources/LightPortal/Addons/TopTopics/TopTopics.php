<?php

/**
 * TopTopics.php
 *
 * @package TopTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */

namespace Bugo\LightPortal\Addons\TopTopics;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, NumberField, RadioField};

if (! defined('LP_NAME'))
	die('No direct access...');

class TopTopics extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-balance-scale-left';

	public function blockOptions(array &$options): void
	{
		$options['top_topics']['parameters'] = [
			'popularity_type'   => 'replies',
			'num_topics'        => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'top_topics')
			return;

		$parameters['popularity_type']   = FILTER_DEFAULT;
		$parameters['num_topics']        = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'top_topics')
			return;

		RadioField::make('popularity_type', $this->txt['lp_top_topics']['type'])
			->setOptions(array_combine(['replies', 'views'], $this->txt['lp_top_topics']['type_set']))
			->setValue($this->context['lp_block']['options']['parameters']['popularity_type']);

		NumberField::make('num_topics', $this->txt['lp_top_topics']['num_topics'])
			->setAttribute('min', 1)
			->setValue($this->context['lp_block']['options']['parameters']['num_topics']);

		CheckboxField::make('show_numbers_only', $this->txt['lp_top_topics']['show_numbers_only'])
			->setValue($this->context['lp_block']['options']['parameters']['show_numbers_only']);
	}

	public function prepareContent($data, array $parameters): void
	{
		if ($data->type !== 'top_topics')
			return;

		$parameters['show_numbers_only'] ??= false;

		$top_topics = $this->cache('top_topics_addon_b' . $data->block_id . '_u' . $this->user_info['id'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getFromSsi', 'topTopics', $parameters['popularity_type'], $parameters['num_topics'], 'array');

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
				<span>', ($parameters['show_numbers_only'] ? $topic['num_' . $parameters['popularity_type']] : $this->translate('lp_' . $parameters['popularity_type'] . '_set', [$parameters['popularity_type'] => $topic['num_' . $parameters['popularity_type']]])), '</span>
			</dd>';
		}

		echo '
		</dl>';
	}
}
