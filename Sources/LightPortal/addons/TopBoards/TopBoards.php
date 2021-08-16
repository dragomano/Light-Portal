<?php

/**
 * TopBoards
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\TopBoards;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class TopBoards extends Plugin
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
		$options['top_boards']['parameters'] = [
			'num_boards'        => 10,
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
		if ($type !== 'top_boards')
			return;

		$parameters['num_boards']        = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	/**
	 * @return void
	 */
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

		$context['posting_fields']['show_numbers_only']['label']['text'] = $txt['lp_top_boards']['show_numbers_only'];
		$context['posting_fields']['show_numbers_only']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_numbers_only',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_numbers_only'])
			)
		);
	}

	/**
	 * Get the list of popular boards
	 *
	 * Получаем список популярных разделов
	 *
	 * @param int $num_boards
	 * @return array
	 */
	public function getData(int $num_boards): array
	{
		global $boarddir;

		require_once $boarddir . '/SSI.php';

		return ssi_topBoards($num_boards, 'array');
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

		if ($type !== 'top_boards')
			return;

		$top_boards = Helpers::cache('top_boards_addon_b' . $block_id . '_u' . $user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData', $parameters['num_boards']);

		if (empty($top_boards))
			return;

		echo '
		<dl class="stats">';

		$max = $top_boards[0]['num_topics'];

		foreach ($top_boards as $board) {
			if ($board['num_topics'] < 1)
				continue;

			$width = $board['num_topics'] * 100 / $max;

			echo '
			<dt>', $board['link'], '</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($board['num_topics']) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $board['num_topics'] : Helpers::getText($board['num_topics'], $txt['lp_top_boards']['topics'])), '</span>
			</dd>';
		}

		echo '
		</dl>';
	}
}
