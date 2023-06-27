<?php

/**
 * BoardStats.php
 *
 * @package BoardStats (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 03.06.23
 */

namespace Bugo\LightPortal\Addons\BoardStats;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

class BoardStats extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-chart-pie';

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

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'board_stats')
			return;

		$this->context['posting_fields']['show_latest_member']['label']['text'] = $this->txt['lp_board_stats']['show_latest_member'];
		$this->context['posting_fields']['show_latest_member']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_latest_member',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_latest_member']
			],
			'tab' => 'content'
		];

		$this->context['posting_fields']['show_basic_info']['label']['text'] = $this->txt['lp_board_stats']['show_basic_info'];
		$this->context['posting_fields']['show_basic_info']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_basic_info',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_basic_info']
			],
			'tab' => 'content'
		];

		$this->context['posting_fields']['show_whos_online']['label']['text'] = $this->txt['lp_board_stats']['show_whos_online'];
		$this->context['posting_fields']['show_whos_online']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_whos_online',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_whos_online']
			],
			'tab' => 'content'
		];

		$this->context['posting_fields']['use_fa_icons']['label']['text'] = $this->txt['lp_board_stats']['use_fa_icons'];
		$this->context['posting_fields']['use_fa_icons']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'use_fa_icons',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['use_fa_icons']
			],
			'tab' => 'appearance'
		];

		$this->context['posting_fields']['update_interval']['label']['text'] = $this->txt['lp_board_stats']['update_interval'];
		$this->context['posting_fields']['update_interval']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id' => 'update_interval',
				'min' => 0,
				'value' => $this->context['lp_block']['options']['parameters']['update_interval']
			]
		];
	}

	public function getData(array $parameters): array
	{
		if (empty($parameters['show_latest_member']) && empty($parameters['show_basic_info']) && empty($parameters['show_whos_online']))
			return [];

		if ($parameters['show_basic_info']) {
			$basic_info = $this->getFromSsi('boardStats', 'array');
			$basic_info['max_online_today'] = comma_format($this->modSettings['mostOnlineToday']);
			$basic_info['max_online']       = comma_format($this->modSettings['mostOnline']);
		}

		return [
			'latest_member' => $this->modSettings['latestRealName'] ?? '',
			'basic_info'    => $basic_info ?? [],
			'whos_online'   => empty($parameters['show_whos_online']) ? [] : $this->getFromSsi('whosOnline', 'array')
		];
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'board_stats')
			return;

		if ($this->request()->has('preview'))
			$parameters['update_interval'] = 0;

		$parameters['show_latest_member'] ??= false;

		$board_stats = $this->cache('board_stats_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($parameters['update_interval'] ?? $cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($board_stats))
			return;

		echo '
			<div class="board_stats_areas">';

		if ($parameters['show_latest_member'] && $board_stats['latest_member']) {
			echo '
				<div>
					<h4>
						', $parameters['use_fa_icons'] ? '<i class="fas fa-user"></i> ' : '<span class="main_icons members"></span> ', $this->txt['lp_board_stats']['newbie'], '
					</h4>
					<ul class="bbc_list">
						<li>', $board_stats['latest_member'], '</li>
					</ul>
				</div>';
		}

		if ($parameters['show_basic_info'] && $board_stats['basic_info']) {
			$stats_title = $this->allowedTo('view_stats') ? '<a href="' . $this->scripturl . '?action=stats">' . $this->txt['forum_stats'] . '</a>' : $this->txt['forum_stats'];

			echo '
				<div>
					<h4>
						', $parameters['use_fa_icons'] ? '<i class="fas fa-chart-pie"></i> ' : '<span class="main_icons stats"></span> ', $stats_title, '
					</h4>';

			echo '
					<ul class="bbc_list">';

			if ($this->allowedTo('view_stats')) {
				echo '
						<li>', $this->txt['members'], ': ', $board_stats['basic_info']['members'], '</li>
						<li>', $this->txt['posts'], ': ', $board_stats['basic_info']['posts'], '</li>
						<li>', $this->txt['topics'], ': ', $board_stats['basic_info']['topics'], '</li>';
			}

			echo '
						<li>', $this->txt['lp_board_stats']['online_today'] , ': ', $board_stats['basic_info']['max_online_today'], '</li>
						<li>', $this->txt['lp_board_stats']['max_online'], ': ', $board_stats['basic_info']['max_online'], '</li>
					</ul>
				</div>';
		}

		if ($parameters['show_whos_online'] && $board_stats['whos_online']) {
			$online_title = $this->allowedTo('who_view') ? '<a href="' . $this->scripturl . '?action=who">' . $this->txt['online_users'] . '</a>' : $this->txt['online_users'];

			echo '
				<div>
					<h4>
						', $parameters['use_fa_icons'] ? '<i class="fas fa-users"></i> ' : '<span class="main_icons people"></span> ', $online_title, '
					</h4>
					<ul class="bbc_list">
						<li>', $this->txt['members'], ': ', comma_format($board_stats['whos_online']['num_users_online']), '</li>
						<li>', $this->txt['lp_board_stats']['guests'], ': ', comma_format($board_stats['whos_online']['num_guests']), '</li>
						<li>', $this->txt['lp_board_stats']['spiders'], ': ', comma_format($board_stats['whos_online']['num_spiders']), '</li>
						<li>', $this->txt['total'], ': ', comma_format($board_stats['whos_online']['total_users']), '</li>
					</ul>
				</div>';
		}

		echo '
			</div>';
	}
}
