<?php

/**
 * BoardStats.php
 *
 * @package BoardStats (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.05.24
 */

namespace Bugo\LightPortal\Addons\BoardStats;

use Bugo\Compat\{Config, Lang, User, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, NumberField};
use Bugo\LightPortal\Enums\Tab;

if (! defined('LP_NAME'))
	die('No direct access...');

class BoardStats extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-chart-pie';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'board_stats')
			return;

		$params = [
			'link_in_title'      => Config::$scripturl . '?action=stats',
			'show_latest_member' => false,
			'show_basic_info'    => true,
			'show_whos_online'   => true,
			'use_fa_icons'       => true,
			'update_interval'    => 600,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'board_stats')
			return;

		$params = [
			'show_latest_member' => FILTER_VALIDATE_BOOLEAN,
			'show_basic_info'    => FILTER_VALIDATE_BOOLEAN,
			'show_whos_online'   => FILTER_VALIDATE_BOOLEAN,
			'use_fa_icons'       => FILTER_VALIDATE_BOOLEAN,
			'update_interval'    => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'board_stats')
			return;

		CheckboxField::make('show_latest_member', Lang::$txt['lp_board_stats']['show_latest_member'])
			->setTab(Tab::CONTENT)
			->setValue(Utils::$context['lp_block']['options']['show_latest_member']);

		CheckboxField::make('show_basic_info', Lang::$txt['lp_board_stats']['show_basic_info'])
			->setTab(Tab::CONTENT)
			->setValue(Utils::$context['lp_block']['options']['show_basic_info']);

		CheckboxField::make('show_whos_online', Lang::$txt['lp_board_stats']['show_whos_online'])
			->setTab(Tab::CONTENT)
			->setValue(Utils::$context['lp_block']['options']['show_whos_online']);

		CheckboxField::make('use_fa_icons', Lang::$txt['lp_board_stats']['use_fa_icons'])
			->setTab(Tab::APPEARANCE)
			->setValue(Utils::$context['lp_block']['options']['use_fa_icons']);

		NumberField::make('update_interval', Lang::$txt['lp_board_stats']['update_interval'])
			->setAttribute('min', 0)
			->setValue(Utils::$context['lp_block']['options']['update_interval']);
	}

	public function getData(array $parameters): array
	{
		if (empty($parameters['show_latest_member']) && empty($parameters['show_basic_info']) && empty($parameters['show_whos_online']))
			return [];

		if ($parameters['show_basic_info']) {
			$info = $this->getFromSsi('boardStats', 'array');
			$info['max_online_today'] = comma_format(Config::$modSettings['mostOnlineToday']);
			$info['max_online'] = comma_format(Config::$modSettings['mostOnline']);
		}

		return [
			'latest_member' => Config::$modSettings['latestRealName'] ?? '',
			'basic_info'    => $info ?? [],
			'whos_online'   => empty($parameters['show_whos_online']) ? [] : $this->getFromSsi('whosOnline', 'array')
		];
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'board_stats')
			return;

		if ($this->request()->has('preview'))
			$parameters['update_interval'] = 0;

		$parameters['show_latest_member'] ??= false;

		$boardStats = $this->cache('board_stats_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($parameters['update_interval'] ?? $data->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($boardStats))
			return;

		echo '
			<div class="board_stats_areas">';

		if ($parameters['show_latest_member'] && $boardStats['latest_member']) {
			echo '
				<div>
					<h4>
						', $parameters['use_fa_icons'] ? '<i class="fas fa-user"></i> ' : '<span class="main_icons members"></span> ', Lang::$txt['lp_board_stats']['newbie'], '
					</h4>
					<ul class="bbc_list">
						<li>', $boardStats['latest_member'], '</li>
					</ul>
				</div>';
		}

		if ($parameters['show_basic_info'] && $boardStats['basic_info']) {
			$statsTitle = User::hasPermission('view_stats')
				? '<a href="' . Config::$scripturl . '?action=stats">' . Lang::$txt['forum_stats'] . '</a>'
				: Lang::$txt['forum_stats'];

			echo '
				<div>
					<h4>
						', $parameters['use_fa_icons'] ? '<i class="fas fa-chart-pie"></i> ' : '<span class="main_icons stats"></span> ', $statsTitle, '
					</h4>';

			echo '
					<ul class="bbc_list">';

			if (User::hasPermission('view_stats')) {
				echo '
						<li>', Lang::$txt['members'], ': ', $boardStats['basic_info']['members'], '</li>
						<li>', Lang::$txt['posts'], ': ', $boardStats['basic_info']['posts'], '</li>
						<li>', Lang::$txt['topics'], ': ', $boardStats['basic_info']['topics'], '</li>';
			}

			echo '
						<li>', Lang::$txt['lp_board_stats']['online_today'] , ': ', $boardStats['basic_info']['max_online_today'], '</li>
						<li>', Lang::$txt['lp_board_stats']['max_online'], ': ', $boardStats['basic_info']['max_online'], '</li>
					</ul>
				</div>';
		}

		if ($parameters['show_whos_online'] && $boardStats['whos_online']) {
			$onlineTitle = User::hasPermission('who_view')
				? '<a href="' . Config::$scripturl . '?action=who">' . Lang::$txt['online_users'] . '</a>'
				: Lang::$txt['online_users'];

			echo '
				<div>
					<h4>
						', $parameters['use_fa_icons'] ? '<i class="fas fa-users"></i> ' : '<span class="main_icons people"></span> ', $onlineTitle, '
					</h4>
					<ul class="bbc_list">
						<li>', Lang::$txt['members'], ': ', comma_format($boardStats['whos_online']['num_users_online']), '</li>
						<li>', Lang::$txt['lp_board_stats']['guests'], ': ', comma_format($boardStats['whos_online']['num_guests']), '</li>
						<li>', Lang::$txt['lp_board_stats']['spiders'], ': ', comma_format($boardStats['whos_online']['num_spiders']), '</li>
						<li>', Lang::$txt['total'], ': ', comma_format($boardStats['whos_online']['total_users']), '</li>
					</ul>
				</div>';
		}

		echo '
			</div>';
	}
}
