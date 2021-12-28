<?php

/**
 * TopBoards.php
 *
 * @package TopBoards (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 23.12.21
 */

namespace Bugo\LightPortal\Addons\TopBoards;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class TopBoards extends Plugin
{
	public string $icon = 'fas fa-balance-scale-left';

	public function blockOptions(array &$options)
	{
		$options['top_boards']['parameters'] = [
			'num_boards'        => 10,
			'entity_type'       => 'num_topics',
			'show_numbers_only' => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'top_boards')
			return;

		$parameters['num_boards']        = FILTER_VALIDATE_INT;
		$parameters['entity_type']       = FILTER_SANITIZE_STRING;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'top_boards')
			return;

		$context['posting_fields']['num_boards']['label']['text'] = $txt['lp_top_boards']['num_boards'];
		$context['posting_fields']['num_boards']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_boards',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_boards']
			)
		);

		$context['posting_fields']['entity_type']['label']['text'] = $txt['lp_top_boards']['entity_type'];
		$context['posting_fields']['entity_type']['input'] = array(
			'type' => 'radio_select',
			'attributes' => array(
				'id' => 'entity_type'
			),
			'options' => array()
		);

		$entity_types = array_combine(array('num_topics', 'num_posts'), $txt['lp_top_boards']['entity_type_set']);

		foreach ($entity_types as $key => $value) {
			$context['posting_fields']['entity_type']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['entity_type']
			);
		}

		$context['posting_fields']['show_numbers_only']['label']['text'] = $txt['lp_top_boards']['show_numbers_only'];
		$context['posting_fields']['show_numbers_only']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_numbers_only',
				'checked' => ! empty($context['lp_block']['options']['parameters']['show_numbers_only'])
			)
		);
	}

	public function getData(int $num_boards): array
	{
		$this->loadSsi();

		return ssi_topBoards($num_boards, 'array');
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $user_info, $txt;

		if ($type !== 'top_boards')
			return;

		$top_boards = Helper::cache('top_boards_addon_b' . $block_id . '_u' . $user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData', $parameters['num_boards']);

		if (empty($top_boards))
			return;

		echo '
		<dl class="stats">';

		$type = $parameters['entity_type'] === 'num_posts' ? 'posts' : 'topics';

		$max = $top_boards[0]['num_' . $type];

		foreach ($top_boards as $board) {
			if ($board['num_' . $type] < 1)
				continue;

			$width = $board['num_' . $type] * 100 / $max;

			echo '
			<dt>', $board['link'], '</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($board['num_' . $type]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $board['num_' . $type] : Helper::getSmartContext($txt['lp_top_boards'][$type], [$type => $board['num_' . $type]])), '</span>
			</dd>';
		}

		echo '
		</dl>';
	}
}
