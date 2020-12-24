<?php

namespace Bugo\LightPortal\Addons\BoardStats;

use Bugo\LightPortal\Helpers;

/**
 * BoardStats
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class BoardStats
{
	/**
	 * @var string
	 */
	public $addon_icon = 'fas fa-chart-pie';

	/**
	 * @var bool
	 */
	private $show_latest_member = false;

	/**
	 * @var bool
	 */
	private $show_basic_info = true;

	/**
	 * @var bool
	 */
	private $show_whos_online = true;

	/**
	 * @var bool
	 */
	private $use_fa_icons = true;

	/**
	 * @var int
	 */
	private $update_interval = 600;

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['board_stats']['parameters']['show_latest_member'] = $this->show_latest_member;
		$options['board_stats']['parameters']['show_basic_info']    = $this->show_basic_info;
		$options['board_stats']['parameters']['show_whos_online']   = $this->show_whos_online;
		$options['board_stats']['parameters']['use_fa_icons']       = $this->use_fa_icons;
		$options['board_stats']['parameters']['update_interval']    = $this->update_interval;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
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

		$context['posting_fields']['show_latest_member']['label']['text'] = $txt['lp_board_stats_addon_show_latest_member'];
		$context['posting_fields']['show_latest_member']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_latest_member',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_latest_member'])
			),
			'tab' => 'content'
		);

		$context['posting_fields']['show_basic_info']['label']['text'] = $txt['lp_board_stats_addon_show_basic_info'];
		$context['posting_fields']['show_basic_info']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_basic_info',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_basic_info'])
			),
			'tab' => 'content'
		);

		$context['posting_fields']['show_whos_online']['label']['text'] = $txt['lp_board_stats_addon_show_whos_online'];
		$context['posting_fields']['show_whos_online']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_whos_online',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_whos_online'])
			),
			'tab' => 'content'
		);

		$context['posting_fields']['use_fa_icons']['label']['text'] = $txt['lp_board_stats_addon_use_fa_icons'];
		$context['posting_fields']['use_fa_icons']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'use_fa_icons',
				'checked' => !empty($context['lp_block']['options']['parameters']['use_fa_icons'])
			),
			'tab' => 'appearance'
		);

		$context['posting_fields']['update_interval']['label']['text'] = $txt['lp_board_stats_addon_update_interval'];
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
	public function getData(array $parameters)
	{
		global $boarddir, $modSettings;

		if (empty($parameters['show_basic_info']) && empty($parameters['show_whos_online']))
			return [];

		require_once($boarddir . '/SSI.php');

		if (!empty($parameters['show_basic_info'])) {
			$basic_info = ssi_boardStats('array');
			$basic_info['total_pages']      = Helpers::getNumActivePages(true);
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
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $user_info, $txt, $scripturl;

		if ($type !== 'board_stats')
			return;

		$board_stats = Helpers::cache(
			'board_stats_addon_b' . $block_id . '_u' . $user_info['id'],
			'getData',
			__CLASS__,
			$parameters['update_interval'] ?? $cache_time,
			$parameters
		);

		if (!empty($board_stats)) {
			ob_start();

			$fa = !empty($parameters['use_fa_icons']);

			echo '
			<div class="board_stats_areas">';

			if (!empty($parameters['show_latest_member']) && !empty($board_stats['latest_member'])) {
				echo '
				<div>
					<h4>
						', $fa ? '<i class="fas fa-user"></i> ' : '<span class="main_icons members"></span> ', $txt['lp_board_stats_addon_newbies'], '
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
						<li>', $txt['lp_board_stats_addon_pages'], ': ', $board_stats['basic_info']['total_pages'], '</li>
						<li>', $txt['lp_board_stats_addon_online_today'] , ': ', $board_stats['basic_info']['max_online_today'], '</li>
						<li>', $txt['lp_board_stats_addon_max_online'], ': ', $board_stats['basic_info']['max_online'], '</li>
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
						<li>', $txt['lp_board_stats_addon_guests'], ': ', comma_format($board_stats['whos_online']['num_guests']), '</li>
						<li>', $txt['lp_board_stats_addon_spiders'], ': ', comma_format($board_stats['whos_online']['num_spiders']), '</li>
						<li>', $txt['total'], ': ', comma_format($board_stats['whos_online']['total_users']), '</li>
					</ul>
				</div>';
			}

			echo '
			</div>';

			$content = ob_get_clean();
		}
	}
}
