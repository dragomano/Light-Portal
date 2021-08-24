<?php

/**
 * BoardStats
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\BoardStats;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class BoardStats extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'fas fa-chart-pie';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		$options['board_stats']['parameters'] = [
			'show_latest_member' => false,
			'show_basic_info'    => true,
			'show_whos_online'   => true,
			'use_fa_icons'       => true,
			'update_interval'    => 600,
		];
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'board_stats')
			return;

		$parameters['show_latest_member'] = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_basic_info']    = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_whos_online']   = FILTER_VALIDATE_BOOLEAN;
		$parameters['use_fa_icons']       = FILTER_VALIDATE_BOOLEAN;
		$parameters['update_interval']    = FILTER_VALIDATE_INT;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'board_stats')
			return;

		$context['posting_fields']['show_latest_member']['label']['text'] = $txt['lp_board_stats']['show_latest_member'];
		$context['posting_fields']['show_latest_member']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_latest_member',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_latest_member'])
			),
			'tab' => 'content'
		);

		$context['posting_fields']['show_basic_info']['label']['text'] = $txt['lp_board_stats']['show_basic_info'];
		$context['posting_fields']['show_basic_info']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_basic_info',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_basic_info'])
			),
			'tab' => 'content'
		);

		$context['posting_fields']['show_whos_online']['label']['text'] = $txt['lp_board_stats']['show_whos_online'];
		$context['posting_fields']['show_whos_online']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_whos_online',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_whos_online'])
			),
			'tab' => 'content'
		);

		$context['posting_fields']['use_fa_icons']['label']['text'] = $txt['lp_board_stats']['use_fa_icons'];
		$context['posting_fields']['use_fa_icons']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'use_fa_icons',
				'checked' => !empty($context['lp_block']['options']['parameters']['use_fa_icons'])
			),
			'tab' => 'appearance'
		);

		$context['posting_fields']['update_interval']['label']['text'] = $txt['lp_board_stats']['update_interval'];
		$context['posting_fields']['update_interval']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id' => 'update_interval',
				'min' => 0,
				'value' => $context['lp_block']['options']['parameters']['update_interval']
			)
		);
	}

	/**
	 * Get the board stats data
	 *
	 * Получаем данные статистики форума
	 *
	 * @param array $parameters
	 * @return array
	 */
	public function getData(array $parameters): array
	{
		global $boarddir, $modSettings;

		if (empty($parameters['show_latest_member']) && empty($parameters['show_basic_info']) && empty($parameters['show_whos_online']))
			return [];

		require_once $boarddir . '/SSI.php';

		if (!empty($parameters['show_basic_info'])) {
			$basic_info = ssi_boardStats('array');
			$basic_info['max_online_today'] = comma_format($modSettings['mostOnlineToday']);
			$basic_info['max_online']       = comma_format($modSettings['mostOnline']);
		}

		return [
			'latest_member' => $modSettings['latestRealName'] ?? '',
			'basic_info'    => $basic_info ?? [],
			'whos_online'   => !empty($parameters['show_whos_online']) ? ssi_whosOnline('array') : []
		];
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
		global $user_info, $txt, $scripturl;

		if ($type !== 'board_stats')
			return;

		$board_stats = Helpers::cache('board_stats_addon_b' . $block_id . '_u' . $user_info['id'])
			->setLifeTime($parameters['update_interval'] ?? $cache_time)
			->setFallback(__CLASS__, 'getData', $parameters);

		if (empty($board_stats))
			return;

		$fa = !empty($parameters['use_fa_icons']);

		echo '
			<div class="board_stats_areas">';

		if (!empty($parameters['show_latest_member']) && !empty($board_stats['latest_member'])) {
			echo '
				<div>
					<h4>
						', $fa ? '<i class="fas fa-user"></i> ' : '<span class="main_icons members"></span> ', $txt['lp_board_stats']['newbie'], '
					</h4>
					<ul class="bbc_list">
						<li>', $board_stats['latest_member'], '</li>
					</ul>
				</div>';
		}

		if (!empty($parameters['show_basic_info']) && !empty($board_stats['basic_info'])) {
			$stats_title = allowedTo('view_stats') ? '<a href="' . $scripturl . '?action=stats">' . $txt['forum_stats'] . '</a>' : $txt['forum_stats'];

			echo '
				<div>
					<h4>
						', $fa ? '<i class="fas fa-chart-pie"></i> ' : '<span class="main_icons stats"></span> ', $stats_title, '
					</h4>';

			echo '
					<ul class="bbc_list">';

			if (allowedTo('view_stats')) {
				echo '
						<li>', $txt['members'], ': ', $board_stats['basic_info']['members'], '</li>
						<li>', $txt['posts'], ': ', $board_stats['basic_info']['posts'], '</li>
						<li>', $txt['topics'], ': ', $board_stats['basic_info']['topics'], '</li>';
			}

			echo '
						<li>', $txt['lp_board_stats']['online_today'] , ': ', $board_stats['basic_info']['max_online_today'], '</li>
						<li>', $txt['lp_board_stats']['max_online'], ': ', $board_stats['basic_info']['max_online'], '</li>
					</ul>
				</div>';
		}

		if (!empty($parameters['show_whos_online']) && !empty($board_stats['whos_online'])) {
			$online_title = allowedTo('who_view') ? '<a href="' . $scripturl . '?action=who">' . $txt['online_users'] . '</a>' : $txt['online_users'];

			echo '
				<div>
					<h4>
						', $fa ? '<i class="fas fa-users"></i> ' : '<span class="main_icons people"></span> ', $online_title, '
					</h4>
					<ul class="bbc_list">
						<li>', $txt['members'], ': ', comma_format($board_stats['whos_online']['num_users_online']), '</li>
						<li>', $txt['lp_board_stats']['guests'], ': ', comma_format($board_stats['whos_online']['num_guests']), '</li>
						<li>', $txt['lp_board_stats']['spiders'], ': ', comma_format($board_stats['whos_online']['num_spiders']), '</li>
						<li>', $txt['total'], ': ', comma_format($board_stats['whos_online']['total_users']), '</li>
					</ul>
				</div>';
		}

		echo '
			</div>';
	}
}
