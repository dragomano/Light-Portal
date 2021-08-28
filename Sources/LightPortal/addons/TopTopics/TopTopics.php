<?php

/**
 * TopTopics
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

namespace Bugo\LightPortal\Addons\TopTopics;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class TopTopics extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'fas fa-balance-scale-left';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		$options['top_topics']['parameters'] = [
			'popularity_type'   => 'replies',
			'num_topics'        => 10,
			'show_numbers_only' => false,
		];
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'top_topics')
			return;

		$parameters['popularity_type']   = FILTER_SANITIZE_STRING;
		$parameters['num_topics']        = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'top_topics')
			return;

		$context['posting_fields']['popularity_type']['label']['text'] = $txt['lp_top_topics']['type'];
		$context['posting_fields']['popularity_type']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id' => 'popularity_type'
			),
			'options' => array()
		);

		$types = array_combine(array('replies', 'views'), $txt['lp_top_topics']['type_set']);

		foreach ($types as $key => $value) {
			$context['posting_fields']['popularity_type']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['popularity_type']
			);
		}

		$context['posting_fields']['num_topics']['label']['text'] = $txt['lp_top_topics']['num_topics'];
		$context['posting_fields']['num_topics']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_topics',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_topics']
			)
		);

		$context['posting_fields']['show_numbers_only']['label']['text'] = $txt['lp_top_topics']['show_numbers_only'];
		$context['posting_fields']['show_numbers_only']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_numbers_only',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_numbers_only'])
			)
		);
	}

	/**
	 * Get the list of popular topics
	 *
	 * Получаем список популярных тем
	 *
	 * @param array $parameters
	 * @return array
	 */
	public function getData(array $parameters): array
	{
		global $boarddir;

		require_once $boarddir . '/SSI.php';

		return ssi_topTopics($parameters['popularity_type'], $parameters['num_topics'], 'array');
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

		if ($type !== 'top_topics')
			return;

		$top_topics = Helpers::cache('top_topics_addon_b' . $block_id . '_u' . $user_info['id'])
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
				<span>', ($parameters['show_numbers_only'] ? $topic['num_' . $parameters['popularity_type']] : Helpers::getText($topic['num_' . $parameters['popularity_type']], $txt['lp_' . $parameters['popularity_type'] . '_set'])), '</span>
			</dd>';
		}

		echo '
		</dl>';
	}
}
